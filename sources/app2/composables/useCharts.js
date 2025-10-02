import { ref } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useApi } from '~/composables/useApi'
import db from '~/utils/db'

export const useCharts = () => {
  const preferenceStore = usePreferenceStore()
  const { getApi } = useApi()
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl

  const chartData = ref(null)
  const chartIndex = ref(0)
  const visibleButton = ref(true)
  const showFlags = ref(true)

  const loadCharts = async () => {
    if (!preferenceStore.preferences.lastEvent) return

    const eventId = preferenceStore.preferences.lastEvent.id
    visibleButton.value = false
    setTimeout(() => {
      visibleButton.value = true
    }, 3000)

    try {
      // Charger depuis Dexie d'abord
      const cachedCharts = await db.charts.where('eventId').equals(eventId).toArray()
      if (cachedCharts && cachedCharts.length > 0) {
        chartData.value = cachedCharts.map(item => item.data)
        chartIndex.value++
      }

      // Charger depuis l'API en arrière-plan
      try {
        const response = await getApi(`${apiBaseUrl}/charts/${eventId}`)
        const data = await response.json()

        // Sauvegarder dans Dexie
        await db.charts.where('eventId').equals(eventId).delete()
        await db.charts.bulkAdd(data.map(item => ({
          eventId: eventId,
          data: item,
          timestamp: Date.now()
        })))

        // Mettre à jour l'interface seulement si les données ont changé
        if (JSON.stringify(data) !== JSON.stringify(chartData.value)) {
          chartData.value = data
          chartIndex.value++
        }
      } catch (apiError) {
        console.error('Failed to load charts from API, using cached data:', apiError)
        if (!cachedCharts || cachedCharts.length === 0) {
          throw apiError
        }
      }

      // Nettoyer les anciennes données (plus de 7 jours)
      const cutoffTime = Date.now() - (7 * 24 * 60 * 60 * 1000)
      await db.charts.where('timestamp').below(cutoffTime).delete()
    } catch (error) {
      console.error('Failed to load charts:', error)
    }
  }

  const getFav = async () => {
    await preferenceStore.fetchItems()
    showFlags.value = preferenceStore.preferences.show_flags ?? true
  }

  return {
    chartData,
    chartIndex,
    visibleButton,
    showFlags,
    loadCharts,
    getFav
  }
}