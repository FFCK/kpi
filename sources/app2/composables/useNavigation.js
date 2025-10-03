import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

export const useNavigation = () => {
  const preferenceStore = usePreferenceStore()
  const route = useRoute()

  const isAuthenticated = computed(() => {
    return preferenceStore.preferences.user !== undefined && preferenceStore.preferences.user !== null
  })

  // Define navigation order
  const navigationOrder = computed(() => {
    if (isAuthenticated.value) {
      return ['/', '/games', '/charts', '/scrutineering', '/about']
    } else {
      return ['/', '/games', '/charts', '/about']
    }
  })

  const currentIndex = computed(() => {
    return navigationOrder.value.indexOf(route.path)
  })

  const previousPage = computed(() => {
    if (currentIndex.value <= 0) {
      return navigationOrder.value[navigationOrder.value.length - 1]
    }
    return navigationOrder.value[currentIndex.value - 1]
  })

  const nextPage = computed(() => {
    if (currentIndex.value >= navigationOrder.value.length - 1) {
      return navigationOrder.value[0]
    }
    return navigationOrder.value[currentIndex.value + 1]
  })

  return {
    isAuthenticated,
    previousPage,
    nextPage,
    navigationOrder
  }
}
