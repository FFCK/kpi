// Error types for classification
const ErrorType = {
  NETWORK: 'network',
  HTTP_4XX: 'http_4xx',
  HTTP_5XX: 'http_5xx',
  HTTP_401: 'http_401',
  HTTP_404: 'http_404',
  TIMEOUT: 'timeout',
  OFFLINE: 'offline'
}

// Throttle 401 errors to prevent spam
let last401Time = 0
const THROTTLE_401_MS = 5000

// Throttle cache toast to prevent duplicates
let lastCacheToastTime = 0
const THROTTLE_CACHE_TOAST_MS = 3000

export const useApi = () => {
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl
  const toast = useToast()
  const { t } = useI18n()
  const router = useRouter()
  const { logout } = useAuth()

  /**
   * Show cache toast with deduplication
   */
  const showCacheToast = () => {
    const now = Date.now()
    if (now - lastCacheToastTime < THROTTLE_CACHE_TOAST_MS) {
      return // Skip if toast was shown recently
    }
    lastCacheToastTime = now

    toast.add({
      title: t('errors.cache.usingOfflineData.title'),
      description: t('errors.cache.usingOfflineData.description'),
      icon: 'i-heroicons-archive-box',
      color: 'blue',
      timeout: 3000
    })
  }

  const getCookie = (name) => {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
    return null
  }

  const getAuthHeaders = () => {
    const token = getCookie('kpi_app')
    return token ? { 'X-Auth-Token': token } : {}
  }

  /**
   * Detect error type based on error and response
   */
  const detectErrorType = (error, response) => {
    // If no network connection
    if (!navigator.onLine) {
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
      if (response.status === 404) return ErrorType.HTTP_404
      if (response.status >= 400 && response.status < 500) return ErrorType.HTTP_4XX
      if (response.status >= 500) return ErrorType.HTTP_5XX
    }

    return null
  }

  /**
   * Show HTTP error toast
   */
  const showHttpErrorToast = (errorType, status) => {
    let titleKey, descKey, icon, color

    switch (errorType) {
      case ErrorType.HTTP_404:
        titleKey = 'errors.http.404.title'
        descKey = 'errors.http.404.description'
        icon = 'i-heroicons-question-mark-circle'
        color = 'orange'
        break
      case ErrorType.HTTP_4XX:
        titleKey = 'errors.http.4xx.title'
        descKey = 'errors.http.4xx.description'
        icon = 'i-heroicons-exclamation-triangle'
        color = 'orange'
        break
      case ErrorType.HTTP_5XX:
        titleKey = 'errors.http.5xx.title'
        descKey = 'errors.http.5xx.description'
        icon = 'i-heroicons-server'
        color = 'red'
        break
      default:
        titleKey = 'errors.generic.title'
        descKey = 'errors.generic.description'
        icon = 'i-heroicons-x-circle'
        color = 'red'
    }

    toast.add({
      title: t(titleKey),
      description: t(descKey, { status }),
      icon,
      color,
      timeout: 3000
    })
  }

  /**
   * Show network error toast
   */
  const showNetworkErrorToast = (errorType) => {
    let titleKey, descKey, icon

    switch (errorType) {
      case ErrorType.OFFLINE:
        titleKey = 'errors.network.offline.title'
        descKey = 'errors.network.offline.description'
        icon = 'i-heroicons-wifi'
        break
      case ErrorType.TIMEOUT:
        titleKey = 'errors.network.timeout.title'
        descKey = 'errors.network.timeout.description'
        icon = 'i-heroicons-clock'
        break
      case ErrorType.NETWORK:
        titleKey = 'errors.network.failed.title'
        descKey = 'errors.network.failed.description'
        icon = 'i-heroicons-signal-slash'
        break
      default:
        titleKey = 'errors.generic.title'
        descKey = 'errors.generic.description'
        icon = 'i-heroicons-x-circle'
    }

    toast.add({
      title: t(titleKey),
      description: t(descKey),
      icon,
      color: 'red',
      timeout: 3000
    })
  }

  /**
   * Handle API response with automatic error detection and toast notifications
   * @param {Promise<Response>} responsePromise - The fetch promise
   * @param {Object} options - Configuration options
   * @param {boolean} options.silentErrors - If true, don't show toast notifications
   * @returns {Promise<Response>}
   */
  const handleApiResponse = async (responsePromise, options = {}) => {
    const {
      silentErrors = false
    } = options

    let response = null
    let error = null

    try {
      // Add timeout wrapper (10 seconds)
      response = await Promise.race([
        responsePromise,
        new Promise((_, reject) =>
          setTimeout(() => reject(new Error('Request timeout')), 10000)
        )
      ])

      // Check if response is OK
      if (!response.ok) {
        const errorType = detectErrorType(null, response)

        // Special handling for 401 - logout and auto-redirect to login
        if (errorType === ErrorType.HTTP_401) {
          const now = Date.now()

          // Only show toast if enough time has passed (throttling)
          if (!silentErrors && (now - last401Time > THROTTLE_401_MS)) {
            last401Time = now

            toast.add({
              title: t('errors.http.401.title'),
              description: t('errors.http.401.description'),
              icon: 'i-heroicons-shield-exclamation',
              color: 'red',
              timeout: 4000
            })

            // Logout user and redirect to login after short delay
            setTimeout(async () => {
              await logout()
              router.push('/login')
            }, 1500)
          }

          throw new Error('Unauthorized')
        }

        // Handle other HTTP errors
        if (!silentErrors) {
          showHttpErrorToast(errorType, response.status)
        }

        throw new Error(`HTTP ${response.status}`)
      }

      return response
    } catch (err) {
      error = err
      const errorType = detectErrorType(error, response)

      // Show appropriate error toast (if not HTTP error already handled)
      if (!silentErrors && errorType !== ErrorType.HTTP_401) {
        if (errorType === ErrorType.NETWORK || errorType === ErrorType.OFFLINE || errorType === ErrorType.TIMEOUT) {
          showNetworkErrorToast(errorType)
        }
      }

      throw error
    }
  }

  /**
   * GET request with automatic error handling
   */
  const getApi = (url, options = {}) => {
    const responsePromise = fetch(`${apiBaseUrl}${url}`, {
      headers: {
        'Cache-Control': 'no-cache',
        Pragma: 'no-cache',
        Expires: '0',
        ...getAuthHeaders()
      }
    })

    return handleApiResponse(responsePromise, options)
  }

  /**
   * POST/PUT/DELETE request with automatic error handling
   */
  const postApi = (url, data, method = 'POST', options = {}) => {
    const responsePromise = fetch(`${apiBaseUrl}${url}`, {
      method,
      headers: {
        'Content-Type': 'application/json',
        ...getAuthHeaders()
      },
      body: JSON.stringify(data)
    })

    return handleApiResponse(responsePromise, options)
  }

  /**
   * Get authentication token
   */
  const getToken = (authToken) => {
    return fetch(`${apiBaseUrl}/login`, {
      method: 'POST',
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })
  }

  return { getApi, postApi, getToken, getCookie, getAuthHeaders, showCacheToast }
}
