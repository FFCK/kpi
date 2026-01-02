import type { ApiError } from '~/types'

export const useApi = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const baseUrl = config.public.api2BaseUrl

  // Get auth headers
  const getHeaders = (): HeadersInit => {
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    }

    if (authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`
    }

    return headers
  }

  // Generic fetch wrapper
  const apiFetch = async <T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> => {
    const url = `${baseUrl}${endpoint}`

    const response = await fetch(url, {
      ...options,
      headers: {
        ...getHeaders(),
        ...options.headers
      }
    })

    if (!response.ok) {
      // Handle 401 - unauthorized
      if (response.status === 401) {
        authStore.clearAuth()
        navigateTo('/login')
        throw new Error('Session expired')
      }

      // Parse error response
      let error: ApiError
      try {
        error = await response.json()
      } catch {
        error = { message: `HTTP ${response.status}: ${response.statusText}` }
      }

      throw error
    }

    // Handle 204 No Content
    if (response.status === 204) {
      return {} as T
    }

    return response.json()
  }

  // GET request
  const get = <T>(endpoint: string, params?: Record<string, string | number>): Promise<T> => {
    let url = endpoint
    if (params) {
      const searchParams = new URLSearchParams()
      Object.entries(params).forEach(([key, value]) => {
        searchParams.append(key, String(value))
      })
      url = `${endpoint}?${searchParams.toString()}`
    }
    return apiFetch<T>(url, { method: 'GET' })
  }

  // POST request
  const post = <T>(endpoint: string, data?: unknown): Promise<T> => {
    return apiFetch<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined
    })
  }

  // PUT request
  const put = <T>(endpoint: string, data?: unknown): Promise<T> => {
    return apiFetch<T>(endpoint, {
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined
    })
  }

  // PATCH request
  const patch = <T>(endpoint: string, data?: unknown): Promise<T> => {
    return apiFetch<T>(endpoint, {
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined
    })
  }

  // DELETE request
  const del = <T>(endpoint: string): Promise<T> => {
    return apiFetch<T>(endpoint, { method: 'DELETE' })
  }

  return {
    get,
    post,
    put,
    patch,
    del
  }
}
