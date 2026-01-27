import type { AuthResponse } from '~/types'

export const useAuth = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const baseUrl = config.public.api2BaseUrl

  // Login with username and password
  const login = async (username: string, password: string): Promise<boolean> => {
    try {
      const response = await fetch(`${baseUrl}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
      })

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Invalid credentials')
        }
        throw new Error('Login failed')
      }

      const data: AuthResponse = await response.json()

      authStore.setAuth(data.user, data.token)
      return true
    } catch (error) {
      authStore.clearAuth()
      throw error
    }
  }

  // Logout
  const logout = async (): Promise<void> => {
    // Optionally call API to invalidate token
    // For now, just clear local state
    authStore.clearAuth()
  }

  // Check if token is still valid
  const checkAuth = async (): Promise<boolean> => {
    if (!authStore.token) return false

    try {
      const response = await fetch(`${baseUrl}/auth/me`, {
        headers: {
          'Authorization': `Bearer ${authStore.token}`
        }
      })

      if (!response.ok) {
        authStore.clearAuth()
        return false
      }

      return true
    } catch {
      authStore.clearAuth()
      return false
    }
  }

  return {
    login,
    logout,
    checkAuth
  }
}
