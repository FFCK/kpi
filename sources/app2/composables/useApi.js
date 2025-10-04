export const useApi = () => {
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl

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

  const getApi = (url) => {
    return fetch(`${apiBaseUrl}${url}`, {
      headers: {
        'Cache-Control': 'no-cache',
        Pragma: 'no-cache',
        Expires: '0',
        ...getAuthHeaders()
      }
    })
  }

  const postApi = (url, data, method = 'POST') => {
    return fetch(`${apiBaseUrl}${url}`, {
      method,
      headers: {
        'Content-Type': 'application/json',
        ...getAuthHeaders()
      },
      body: JSON.stringify(data)
    })
  }

  const getToken = (authToken) => {
    return fetch(`${apiBaseUrl}/login`, {
      method: 'POST',
      headers: {
        Authorization: `Basic ${authToken}`
      }
    })
  }

  return { getApi, postApi, getToken, getCookie, getAuthHeaders }
}
