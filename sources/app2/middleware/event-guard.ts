import { usePreferenceStore } from '~/stores/preferenceStore'

/**
 * Middleware to protect routes that require an event to be selected
 * Redirects to home page if no event is selected
 */
export default defineNuxtRouteMiddleware((to, from) => {
  const preferenceStore = usePreferenceStore()

  // Allow access to home, about, login and any other public pages
  const publicRoutes = ['/', '/about', '/login']

  // Always allow access to public routes to prevent redirect loops
  if (publicRoutes.includes(to.path)) {
    return
  }

  // Check if an event or group is selected
  const hasEvent = preferenceStore.preferences?.lastEvent !== undefined &&
                   preferenceStore.preferences?.lastEvent !== null
  const hasGroup = preferenceStore.preferences?.lastGroup !== undefined &&
                   preferenceStore.preferences?.lastGroup !== null
  const hasEventSelected = hasEvent || hasGroup

  // If no event/group is selected, store the intended URL and redirect to home
  if (!hasEventSelected) {
    // Store path in query parameter so it survives the redirect
    return navigateTo({
      path: '/',
      query: { redirect: to.fullPath }
    })
  }
})
