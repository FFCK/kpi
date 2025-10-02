import { useScrutineeringStore } from '~/stores/scrutineeringStore'
import { usePreferenceStore } from '~/stores/preferenceStore'

export const useScrutineering = () => {
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl
  const store = useScrutineeringStore()
  const prefStore = usePreferenceStore()
  const { getCookie } = useApi()

  const loadPlayers = async () => {
    if (!prefStore.preferences?.lastEvent?.id || !prefStore.preferences?.scr_team_id) {
      console.error('Missing event or team ID')
      return
    }

    const token = getCookie('kpi_app')
    if (!token) {
      console.error('No authentication token found in cookie')
      return
    }

    store.loading = true
    store.error = null

    try {
      const response = await fetch(
        `${apiBaseUrl}/staff/${prefStore.preferences.lastEvent.id}/players/${prefStore.preferences.scr_team_id}/force`,
        {
          headers: {
            'Cache-Control': 'no-cache',
            Pragma: 'no-cache',
            Expires: '0',
            'X-Auth-Token': token
          }
        }
      )

      if (!response.ok) {
        throw new Error('Failed to load players')
      }

      const data = await response.json()
      store.setPlayers(data)
    } catch (error) {
      console.error('Error loading players:', error)
      store.error = error.message
    } finally {
      store.loading = false
    }
  }

  const updatePlayer = async (playerId, equipment, currentValue) => {
    const max = equipment === 'paddle_count' ? 6 : 4
    const newValue = currentValue >= max ? 0 : currentValue + 1

    if (!prefStore.preferences?.lastEvent?.id || !prefStore.preferences?.scr_team_id) {
      console.error('Missing event or team ID')
      return
    }

    const token = getCookie('kpi_app')
    if (!token) {
      console.error('No authentication token found in cookie')
      return
    }

    try {
      const response = await fetch(
        `${apiBaseUrl}/staff/${prefStore.preferences.lastEvent.id}/player/${playerId}/team/${prefStore.preferences.scr_team_id}/${equipment}/${newValue}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-Auth-Token': token
          }
        }
      )

      if (!response.ok) {
        throw new Error('Failed to update player')
      }

      store.updatePlayerEquipment(playerId, equipment, newValue)
    } catch (error) {
      console.error('Error updating player:', error)
      if (error.message === 'Network Error') {
        console.log('Offline!')
      }
    }
  }

  return {
    players: computed(() => store.players),
    loading: computed(() => store.loading),
    error: computed(() => store.error),
    loadPlayers,
    updatePlayer
  }
}
