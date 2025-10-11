import { usePreferenceStore } from '~/stores/preferenceStore'

/**
 * Middleware to protect routes that require an event to be selected
 * Redirects to home page if no event is selected
 */
export default defineNuxtRouteMiddleware((to) => {
  const preferenceStore = usePreferenceStore()

  // Allow access to home, about, and any other public pages
  const publicRoutes = ['/', '/about']

  if (publicRoutes.includes(to.path)) {
    return
  }

  // Check if an event is selected
  const hasEventSelected = preferenceStore.preferences?.lastEvent !== undefined &&
                           preferenceStore.preferences?.lastEvent !== null

  // If no event is selected, store the intended URL and redirect to home
  if (!hasEventSelected) {
    // Store path in query parameter so it survives the redirect
    return navigateTo({
      path: '/',
      query: { redirect: to.fullPath }
    })
  }
})
