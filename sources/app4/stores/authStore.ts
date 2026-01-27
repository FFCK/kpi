import { defineStore } from 'pinia'
import type { User } from '~/types'

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),

  getters: {
    // Get user profile level (returns 99 if not authenticated)
    profile: (state): number => {
      return state.user?.profile ?? 99
    },

    // Check if user has required profile level
    hasProfile: (state) => (requiredProfile: number): boolean => {
      if (!state.user) return false
      return state.user.profile <= requiredProfile
    },

    // Check if user is super admin (profile 1)
    isSuperAdmin: (state): boolean => {
      return state.user?.profile === 1
    }
  },

  actions: {
    setAuth(user: User, token: string) {
      this.user = user
      this.token = token
      this.isAuthenticated = true

      // Store in localStorage for persistence
      if (import.meta.client) {
        localStorage.setItem('kpi_admin_token', token)
        localStorage.setItem('kpi_admin_user', JSON.stringify(user))
      }
    },

    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false

      if (import.meta.client) {
        localStorage.removeItem('kpi_admin_token')
        localStorage.removeItem('kpi_admin_user')
      }
    },

    // Initialize auth from localStorage
    initAuth() {
      if (!import.meta.client) return

      const token = localStorage.getItem('kpi_admin_token')
      const userJson = localStorage.getItem('kpi_admin_user')

      if (token && userJson) {
        try {
          const user = JSON.parse(userJson) as User
          this.user = user
          this.token = token
          this.isAuthenticated = true
        } catch {
          this.clearAuth()
        }
      }
    }
  }
})
