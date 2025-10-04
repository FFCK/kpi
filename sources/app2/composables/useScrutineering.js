import { computed } from 'vue'
import { useScrutineeringStore } from '~/stores/scrutineeringStore'
import { usePrefs } from '~/composables/usePrefs'

export const useScrutineering = () => {
  const store = useScrutineeringStore()
  const { prefs } = usePrefs()
  const { getApi, postApi } = useApi()

  const loadPlayers = async () => {
    if (!prefs.value?.lastEvent?.id || !prefs.value?.scr_team_id) {
      console.error('Missing event or team ID')
      return
    }

    store.loading = true
    store.error = null

    try {
      const response = await getApi(
        `/staff/${prefs.value.lastEvent.id}/players/${prefs.value.scr_team_id}/force`
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

    if (!prefs.value?.lastEvent?.id || !prefs.value?.scr_team_id) {
      console.error('Missing event or team ID')
      return
    }

    try {
      const response = await postApi(
        `/staff/${prefs.value.lastEvent.id}/player/${playerId}/team/${prefs.value.scr_team_id}/${equipment}/${newValue}`,
        {},
        'PUT'
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

  const updateComment = async (playerId, comment) => {
    if (!prefs.value?.lastEvent?.id || !prefs.value?.scr_team_id) {
      console.error('Missing event or team ID')
      return
    }

    try {
      const response = await postApi(
        `/staff/${prefs.value.lastEvent.id}/player/${playerId}/team/${prefs.value.scr_team_id}/comment`,
        { comment },
        'PUT'
      )

      if (!response.ok) {
        throw new Error('Failed to update comment')
      }

      store.updatePlayerComment(playerId, comment)
    } catch (error) {
      console.error('Error updating comment:', error)
    }
  }

  return {
    players: computed(() => store.players),
    loading: computed(() => store.loading),
    error: computed(() => store.error),
    loadPlayers,
    updatePlayer,
    updateComment
  }
}