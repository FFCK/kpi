import { ref, computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useApi } from '~/composables/useApi'
import db from '~/utils/db'

export const useCharts = () => {
  const preferenceStore = usePreferenceStore()
  const { getApi } = useApi()
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl

  const allChartData = ref(null)
  const chartIndex = ref(0)
  const visibleButton = ref(true)
  const showFlags = ref(true)
  const fav_categories = ref([])
  const fav_teams = ref([])
  const categories = ref([])
  const teams = ref([])

  const filteredChartData = computed(() => {
    if (!allChartData.value) return null

    let filtered = [...allChartData.value]

    // Filter by categories
    if (fav_categories.value.length > 0) {
      filtered = filtered.filter(chart => {
        const categoryLabel = chart.libelle || chart.code
        return fav_categories.value.includes(categoryLabel)
      })
    }

    // Add highlighting to teams (but don't filter them out)
    filtered = filtered.map(chart => {
      const highlightedChart = JSON.parse(JSON.stringify(chart)) // Deep clone

      // Highlight teams in ranking
      if (highlightedChart.ranking) {
        highlightedChart.ranking = highlightedChart.ranking.map(team => ({
          ...team,
          t_highlighted: fav_teams.value.includes(team.t_label)
        }))
      }

      // Highlight teams in rounds/phases
      if (highlightedChart.rounds) {
        const highlightedRounds = {}
        for (const [roundKey, round] of Object.entries(highlightedChart.rounds)) {
          if (round.phases) {
            const highlightedPhases = {}
            for (const [phaseKey, phase] of Object.entries(round.phases)) {
              const highlightedPhase = { ...phase }

              // Highlight teams
              if (phase.teams) {
                highlightedPhase.teams = phase.teams.map(team => ({
                  ...team,
                  t_highlighted: fav_teams.value.includes(team.t_label)
                }))
              }

              // Highlight teams in games
              if (phase.games) {
                highlightedPhase.games = phase.games.map(game => ({
                  ...game,
                  t_a_highlighted: fav_teams.value.includes(game.t_a_label),
                  t_b_highlighted: fav_teams.value.includes(game.t_b_label)
                }))
              }

              highlightedPhases[phaseKey] = highlightedPhase
            }
            highlightedRounds[roundKey] = { ...round, phases: highlightedPhases }
          } else {
            highlightedRounds[roundKey] = round
          }
        }
        highlightedChart.rounds = highlightedRounds
      }

      return highlightedChart
    })

    return filtered
  })

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
        allChartData.value = cachedCharts.map(item => item.data)
        loadCategories()
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
        if (JSON.stringify(data) !== JSON.stringify(allChartData.value)) {
          allChartData.value = data
          loadCategories()
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

  const loadCategories = () => {
    if (!allChartData.value) return
    categories.value = allChartData.value.map(chart => chart.libelle || chart.code)

    // Extract teams from all charts
    const allTeams = new Set()
    allChartData.value.forEach(chart => {
      // Teams from ranking
      if (chart.ranking) {
        chart.ranking.forEach(team => {
          if (team.t_label) {
            allTeams.add(team.t_label)
          }
        })
      }

      // Teams from rounds/phases
      if (chart.rounds) {
        Object.values(chart.rounds).forEach(round => {
          if (round.phases) {
            Object.values(round.phases).forEach(phase => {
              // Teams from phase teams
              if (phase.teams) {
                phase.teams.forEach(team => {
                  if (team.t_label) {
                    allTeams.add(team.t_label)
                  }
                })
              }
              // Teams from phase games
              if (phase.games) {
                phase.games.forEach(game => {
                  if (game.t_a_label) allTeams.add(game.t_a_label)
                  if (game.t_b_label) allTeams.add(game.t_b_label)
                })
              }
            })
          }
        })
      }
    })
    teams.value = Array.from(allTeams).sort()
  }

  const getFav = async () => {
    await preferenceStore.fetchItems()
    showFlags.value = preferenceStore.preferences.show_flags ?? true
    fav_categories.value = JSON.parse(preferenceStore.preferences.fav_categories || '[]')
    fav_teams.value = JSON.parse(preferenceStore.preferences.fav_teams || '[]')
  }

  const changeFav = async () => {
    await preferenceStore.putItem('fav_categories', JSON.stringify(fav_categories.value))
    await preferenceStore.putItem('fav_teams', JSON.stringify(fav_teams.value))
  }

  return {
    chartData: filteredChartData,
    chartIndex,
    visibleButton,
    showFlags,
    categories,
    teams,
    fav_categories,
    fav_teams,
    loadCharts,
    getFav,
    changeFav
  }
}