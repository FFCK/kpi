export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

  // Initialize auth from localStorage on first load
  if (!authStore.isAuthenticated) {
    authStore.initAuth()
  }

  // Public routes that don't require authentication
  const publicRoutes = ['/login']

  if (publicRoutes.includes(to.path)) {
    // If already authenticated, redirect to home
    if (authStore.isAuthenticated) {
      return navigateTo('/')
    }
    return
  }

  // Protected routes - require authentication
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  // Check profile 1 restriction for beta
  if (authStore.user?.profile !== 1) {
    authStore.clearAuth()
    return navigateTo('/login')
  }
})
