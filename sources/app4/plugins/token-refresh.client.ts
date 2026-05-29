// Silently refresh the JWT token every 45 minutes for low-privilege users (profile < 7).
// This keeps the session alive for a full workday without re-authentication.
const REFRESH_INTERVAL_MS = 45 * 60 * 1000

export default defineNuxtPlugin(() => {
  const authStore = useAuthStore()
  const { refreshToken } = useAuth()

  let intervalId: ReturnType<typeof setInterval> | null = null

  const startRefreshLoop = () => {
    if (intervalId) return
    intervalId = setInterval(async () => {
      if (!authStore.isAuthenticated || authStore.profile >= 7) return
      if (!authStore.isTokenExpiringSoon) return
      await refreshToken()
    }, REFRESH_INTERVAL_MS)
  }

  const stopRefreshLoop = () => {
    if (intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  watch(
    () => authStore.isAuthenticated,
    (authenticated) => {
      if (authenticated && authStore.profile < 7) {
        startRefreshLoop()
      } else {
        stopRefreshLoop()
      }
    },
    { immediate: true }
  )
})
