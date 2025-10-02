import { ref } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

export const useStatus = () => {
  const authorized = ref(false)
  const preferenceStore = usePreferenceStore()

  const checkOnline = () => {
    return navigator.onLine
  }

  const checkAuthorized = async () => {
    await preferenceStore.fetchItems()
    const user = preferenceStore.preferences.user
    const prefs = preferenceStore.preferences

    if (user && prefs?.lastEvent?.id !== undefined) {
      const userEvents = user.events?.split('|').map(e => parseInt(e)) || []
      authorized.value = userEvents.includes(prefs.lastEvent.id)
    } else {
      authorized.value = false
    }
  }

  return {
    checkOnline,
    authorized,
    checkAuthorized
  }
}
