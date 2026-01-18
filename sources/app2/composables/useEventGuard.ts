import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

/**
 * Composable to check if an event or group is selected
 * Returns a computed property that checks if lastEvent or lastGroup exists in preferences
 */
export const useEventGuard = () => {
  const preferenceStore = usePreferenceStore()

  const hasEventSelected = computed(() => {
    const hasEvent = preferenceStore.preferences?.lastEvent !== undefined &&
                     preferenceStore.preferences?.lastEvent !== null
    const hasGroup = preferenceStore.preferences?.lastGroup !== undefined &&
                     preferenceStore.preferences?.lastGroup !== null
    return hasEvent || hasGroup
  })

  return {
    hasEventSelected
  }
}
