import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

export const useAuth = () => {
  // Note: avoid calling composables inside event handlers — call necessary
  // composition APIs at composable init (which runs in component setup).
  const preferenceStore = usePreferenceStore()

  // read-only computed user reference
  const user = computed(() => preferenceStore.preferences.user)

  // capture runtime config at init so we can use it later inside handlers
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public?.apiBaseUrl || ''

  const login = async (login, password) => {
    const authToken = btoa(`${login}:${password}`)

    // Perform a direct fetch to the login endpoint instead of calling useApi()
    // which itself uses composition functions and must be called during setup.
    const response = await fetch(`${apiBaseUrl}/login`, {
      method: 'POST',
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })

    if (response.ok) {
      const data = await response.json()
      await preferenceStore.putItem('user', data.user)
      // Set cookie
      const date = new Date()
      date.setTime(date.getTime() + 10 * 24 * 60 * 60 * 1000)
      const expires = 'expires=' + date.toUTCString()
      document.cookie = `kpi_app=${data.user.token}; ${expires}; path=/; SameSite=Lax;`
      return true
    } else {
      return false
    }
  }

  const logout = async () => {
    await preferenceStore.removeItem('user')
    document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax;'
  }

  return { user, login, logout }
}
