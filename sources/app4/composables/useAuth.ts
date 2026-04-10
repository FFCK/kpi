import type { MandateSummary, MandateFilters } from '~/types/users'

interface LoginResponse {
  token: string
  user: {
    id: string
    name: string
    firstname: string
    profile: number
    filters: MandateFilters
    mandates: MandateSummary[]
    activeMandate: { id: number; libelle: string } | null
    effectiveProfile: number
    effectiveFilters: MandateFilters
  }
  hasMandates: boolean
}

interface SwitchMandateResponse {
  token: string
  user: {
    activeMandate: { id: number; libelle: string } | null
    effectiveProfile: number
    effectiveFilters: MandateFilters | null
  }
}

export const useAuth = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const baseUrl = config.public.api2BaseUrl

  // Login with username and password
  const login = async (username: string, password: string): Promise<{ hasMandates: boolean }> => {
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

      const data: LoginResponse = await response.json()

      authStore.setAuth(
        data.user,
        data.token,
        data.user.mandates,
        data.user.activeMandate,
        data.user.effectiveProfile,
        data.user.effectiveFilters
      )

      return { hasMandates: data.hasMandates }
    } catch (error) {
      authStore.clearAuth()
      throw error
    }
  }

  // Logout
  const logout = async (): Promise<void> => {
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

  // Switch active mandate (or revert to base profile with mandateId = null)
  const switchMandate = async (mandateId: number | null): Promise<void> => {
    const response = await fetch(`${baseUrl}/auth/switch-mandate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({ mandateId })
    })

    if (!response.ok) {
      throw new Error('Failed to switch mandate')
    }

    const data: SwitchMandateResponse = await response.json()

    authStore.setMandate(
      data.token,
      data.user.activeMandate,
      data.user.effectiveProfile,
      data.user.effectiveFilters
    )
  }

  return {
    login,
    logout,
    checkAuth,
    switchMandate
  }
}
