import { ref, computed, onMounted } from 'vue'

export function usePrefs() {
  const prefs = computed(() => Preferences.query().first())

  async function getPrefs() {
    if (Preferences.query().count() === 0) {
      const result = await idbs.dbGetAll('preferences')
      if (result.length === 1) {
        Preferences.insertOrUpdate({ data: result })
      } else {
        Preferences.insertOrUpdate({ data: { id: 1 } })
        idbs.dbPut('preferences', Preferences.query().first())
      }
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
    scrollTop
  }
}
