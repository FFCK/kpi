import { computed, onMounted } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

export function useUser() {
  const preferenceStore = usePreferenceStore()

  const user = computed(() => preferenceStore.preferences.user)

  async function getUser() {
    await preferenceStore.fetchItems()
  }

  onMounted(getUser)

  return {
    user,
    getUser
  }
}
