import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

/**
 * Composable to check if an event is selected
 * Returns a computed property that checks if lastEvent exists in preferences
 */
export const useEventGuard = () => {
  const preferenceStore = usePreferenceStore()

  const hasEventSelected = computed(() => {
    return preferenceStore.preferences?.lastEvent !== undefined &&
           preferenceStore.preferences?.lastEvent !== null
  })

  return {
    hasEventSelected
  }
}
