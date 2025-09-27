import { usePreferenceStore } from '~/stores/preferenceStore'

export const useAuth = () => {
  const { getToken } = useApi()
  const preferenceStore = usePreferenceStore()

  const user = computed(() => preferenceStore.preferences.user)

  const login = async (login, password) => {
    const authToken = btoa(`${login}:${password}`)
    const response = await getToken(authToken)
    if (response.ok) {
      const data = await response.json()
      await preferenceStore.putItem('user', data.user)
      // Set cookie
      const date = new Date()
      date.setTime(date.getTime() + 10 * 24 * 60 * 60 * 1000)
      const expires = 'expires=' + date.toUTCString()
      document.cookie = `kpi_app=${data.user.token}; ${expires}; path=/; SameSite=Strict;`
      return true
    } else {
      return false
    }
  }

  const logout = async () => {
    await preferenceStore.removeItem('user')
    document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Strict;'
  }

  return { user, login, logout }
}
