export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

  // Initialize auth from localStorage on first load
  if (!authStore.isAuthenticated) {
    authStore.initAuth()
  }

  // Public routes that don't require authentication
  const publicRoutes = ['/login', '/reset-password']

  if (publicRoutes.includes(to.path)) {
    // If already authenticated and on login, redirect to home
    if (authStore.isAuthenticated && to.path === '/login') {
      return navigateTo('/')
    }
    return
  }

  // Protected routes - require authentication
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  // select-mandate requires auth but no further checks
  if (to.path === '/select-mandate') {
    return
  }
})
