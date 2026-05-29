import { onMounted, onUnmounted } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

const FIVE_MINUTES = 5 * 60 * 1000

/**
 * Auto-refresh composable: triggers `refreshFn` every 5 minutes while the page
 * is visible, and immediately on page re-activation if inactive for > 5 minutes.
 *
 * @param {() => Promise<void>} refreshFn - async function to call for refresh (force=true)
 * @param {string} lastApiLoadKey - preferenceStore key tracking the last API load timestamp
 */
export const useAutoRefresh = (refreshFn, lastApiLoadKey) => {
  const preferenceStore = usePreferenceStore()

  let intervalId = null

  const shouldRefresh = () => {
    const lastLoad = preferenceStore.preferences?.[lastApiLoadKey] || 0
    return Date.now() - lastLoad > FIVE_MINUTES
  }

  const startInterval = () => {
    if (intervalId) return
    intervalId = setInterval(() => {
      if (document.visibilityState === 'visible') {
        refreshFn()
      }
    }, FIVE_MINUTES)
  }

  const stopInterval = () => {
    if (intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  const onVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
      if (shouldRefresh()) {
        refreshFn()
      }
      startInterval()
    } else {
      stopInterval()
    }
  }

  onMounted(() => {
    document.addEventListener('visibilitychange', onVisibilityChange)
    if (document.visibilityState === 'visible') {
      startInterval()
    }
  })

  onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange)
    stopInterval()
  })
}
