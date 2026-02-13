import type { ApiError } from '~/types'

// Error types for classification
enum ErrorType {
  NETWORK = 'network',
  HTTP_4XX = 'http_4xx',
  HTTP_5XX = 'http_5xx',
  HTTP_401 = 'http_401',
  HTTP_403 = 'http_403',
  TIMEOUT = 'timeout',
  OFFLINE = 'offline'
}

// Throttle 401 errors to prevent spam
let last401Time = 0
const THROTTLE_401_MS = 5000

// Request timeout in milliseconds
const REQUEST_TIMEOUT_MS = 10000

export const useApi = () => {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  const toast = useToast()
  const { t } = useI18n()

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

  /**
   * Detect error type based on error and response
   */
  const detectErrorType = (error: Error | null, response: Response | null): ErrorType | null => {
    // If no network connection
    if (typeof navigator !== 'undefined' && !navigator.onLine) {
      return ErrorType.OFFLINE
    }

    // If fetch threw (network error, CORS, etc.)
    if (error && !response) {
      if (error.name === 'AbortError' || error.message.includes('timeout')) {
        return ErrorType.TIMEOUT
      }
      return ErrorType.NETWORK
    }

    // HTTP status code errors
    if (response && !response.ok) {
      if (response.status === 401) return ErrorType.HTTP_401
      if (response.status === 403) return ErrorType.HTTP_403
      if (response.status >= 400 && response.status < 500) return ErrorType.HTTP_4XX
      if (response.status >= 500) return ErrorType.HTTP_5XX
    }

    return null
  }

  /**
   * Show error toast based on error type
   */
  const showErrorToast = (errorType: ErrorType, status?: number) => {
    let title: string
    let description: string
    let icon: string
    let color: 'error' | 'warning' = 'error'

    switch (errorType) {
      case ErrorType.OFFLINE:
        title = t('errors.network.offline.title')
        description = t('errors.network.offline.description')
        icon = 'i-heroicons-wifi'
        break
      case ErrorType.TIMEOUT:
        title = t('errors.network.timeout.title')
        description = t('errors.network.timeout.description')
        icon = 'i-heroicons-clock'
        break
      case ErrorType.NETWORK:
        title = t('errors.network.failed.title')
        description = t('errors.network.failed.description')
        icon = 'i-heroicons-signal-slash'
        break
      case ErrorType.HTTP_403:
        title = t('errors.http.403.title')
        description = t('errors.http.403.description')
        icon = 'i-heroicons-lock-closed'
        color = 'warning'
        break
      case ErrorType.HTTP_4XX:
        title = t('errors.http.4xx.title')
        description = t('errors.http.4xx.description', { status })
        icon = 'i-heroicons-exclamation-triangle'
        color = 'warning'
        break
      case ErrorType.HTTP_5XX:
        title = t('errors.http.5xx.title')
        description = t('errors.http.5xx.description')
        icon = 'i-heroicons-server'
        break
      default:
        title = t('common.error')
        description = t('errors.generic.description')
        icon = 'i-heroicons-x-circle'
    }

    toast.add({
      title,
      description,
      icon,
      color,
      duration: 3000
    })
  }

  // Generic fetch wrapper with timeout and error handling
  const apiFetch = async <T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> => {
    const url = `${baseUrl}${endpoint}`
    let response: Response | null = null
    let fetchError: Error | null = null

    try {
      // Add timeout wrapper
      response = await Promise.race([
        fetch(url, {
          ...options,
          headers: {
            ...getHeaders(),
            ...options.headers
          }
        }),
        new Promise<never>((_, reject) =>
          setTimeout(() => reject(new Error('Request timeout')), REQUEST_TIMEOUT_MS)
        )
      ])

      if (!response.ok) {
        const errorType = detectErrorType(null, response)

        // Handle 401 - unauthorized with throttling
        if (errorType === ErrorType.HTTP_401) {
          const now = Date.now()
          if (now - last401Time > THROTTLE_401_MS) {
            last401Time = now
            toast.add({
              title: t('errors.http.401.title'),
              description: t('errors.http.401.description'),
              icon: 'i-heroicons-shield-exclamation',
              color: 'error',
              duration: 3000
            })
          }
          authStore.clearAuth()
          navigateTo('/login')
          throw new Error('Session expired')
        }

        // Handle 403 - forbidden
        if (errorType === ErrorType.HTTP_403) {
          showErrorToast(ErrorType.HTTP_403)
          throw new Error('Access denied')
        }

        // Show toast for other errors
        if (errorType) {
          showErrorToast(errorType, response.status)
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
    } catch (err) {
      fetchError = err as Error

      // If not already handled, detect and show error
      if (!response || response.ok) {
        const errorType = detectErrorType(fetchError, null)
        if (errorType) {
          showErrorToast(errorType)
        }
      }

      throw fetchError
    }
  }

  // GET request
  const get = <T>(endpoint: string, params?: Record<string, string | number>): Promise<T> => {
    let url = endpoint
    if (params) {
      const searchParams = new URLSearchParams()
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          searchParams.append(key, String(value))
        }
      })
      const queryString = searchParams.toString()
      if (queryString) {
        url = `${endpoint}?${queryString}`
      }
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
  const del = <T>(endpoint: string, data?: unknown): Promise<T> => {
    return apiFetch<T>(endpoint, {
      method: 'DELETE',
      body: data ? JSON.stringify(data) : undefined
    })
  }

  // POST request with FormData (for file uploads)
  const upload = async <T>(endpoint: string, formData: FormData): Promise<T> => {
    const url = `${baseUrl}${endpoint}`
    let response: Response | null = null
    let fetchError: Error | null = null

    try {
      // Add timeout wrapper
      response = await Promise.race([
        fetch(url, {
          method: 'POST',
          headers: {
            // Don't set Content-Type for FormData - browser will set it with boundary
            'Authorization': authStore.token ? `Bearer ${authStore.token}` : ''
          },
          body: formData
        }),
        new Promise<never>((_, reject) =>
          setTimeout(() => reject(new Error('Request timeout')), REQUEST_TIMEOUT_MS * 3) // 30s for uploads
        )
      ])

      if (!response.ok) {
        const errorType = detectErrorType(null, response)

        if (errorType === ErrorType.HTTP_401) {
          const now = Date.now()
          if (now - last401Time > THROTTLE_401_MS) {
            last401Time = now
            toast.add({
              title: t('errors.http.401.title'),
              description: t('errors.http.401.description'),
              icon: 'i-heroicons-shield-exclamation',
              color: 'error',
              duration: 3000
            })
          }
          authStore.clearAuth()
          navigateTo('/login')
          throw new Error('Session expired')
        }

        if (errorType === ErrorType.HTTP_403) {
          showErrorToast(ErrorType.HTTP_403)
          throw new Error('Access denied')
        }

        if (errorType) {
          showErrorToast(errorType, response.status)
        }

        let error: ApiError
        try {
          error = await response.json()
        } catch {
          error = { message: `HTTP ${response.status}: ${response.statusText}` }
        }

        throw error
      }

      if (response.status === 204) {
        return {} as T
      }

      return response.json()
    } catch (err) {
      fetchError = err as Error

      if (!response || response.ok) {
        const errorType = detectErrorType(fetchError, null)
        if (errorType) {
          showErrorToast(errorType)
        }
      }

      throw fetchError
    }
  }

  // GET request returning Blob (for file downloads)
  const getBlob = async (endpoint: string): Promise<ArrayBuffer> => {
    const url = `${baseUrl}${endpoint}`
    let response: Response | null = null
    let fetchError: Error | null = null

    try {
      response = await Promise.race([
        fetch(url, {
          method: 'GET',
          headers: {
            'Authorization': authStore.token ? `Bearer ${authStore.token}` : ''
          }
        }),
        new Promise<never>((_, reject) =>
          setTimeout(() => reject(new Error('Request timeout')), REQUEST_TIMEOUT_MS)
        )
      ])

      if (!response.ok) {
        const errorType = detectErrorType(null, response)

        if (errorType === ErrorType.HTTP_401) {
          const now = Date.now()
          if (now - last401Time > THROTTLE_401_MS) {
            last401Time = now
            toast.add({
              title: t('errors.http.401.title'),
              description: t('errors.http.401.description'),
              icon: 'i-heroicons-shield-exclamation',
              color: 'error',
              duration: 3000
            })
          }
          authStore.clearAuth()
          navigateTo('/login')
          throw new Error('Session expired')
        }

        if (errorType) {
          showErrorToast(errorType, response.status)
        }

        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      return response.arrayBuffer()
    } catch (err) {
      fetchError = err as Error

      if (!response || response.ok) {
        const errorType = detectErrorType(fetchError, null)
        if (errorType) {
          showErrorToast(errorType)
        }
      }

      throw fetchError
    }
  }

  return {
    get,
    post,
    put,
    patch,
    del,
    upload,
    getBlob
  }
}
