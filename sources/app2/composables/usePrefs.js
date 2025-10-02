import { computed, onMounted } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

export function usePrefs() {
  const preferenceStore = usePreferenceStore()

  const prefs = computed(() => preferenceStore.preferences)

  async function getPrefs() {
    await preferenceStore.fetchItems()
  }

  async function updatePref(updates) {
    for (const [key, value] of Object.entries(updates)) {
      await preferenceStore.putItem(key, value)
    }
  }

  function scrollTop() {
    document.body.scrollTop = 0 // For Safari
    document.documentElement.scrollTop = 0 // For Chrome, Firefox, IE and Opera
  }

  onMounted(getPrefs)

  return {
    prefs,
    getPrefs,
    updatePref,
    scrollTop
  }
}
