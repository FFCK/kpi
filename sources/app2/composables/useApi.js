export const useApi = () => {
  const runtimeConfig = useRuntimeConfig()
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl

  const getApi = (url) => {
    return fetch(url, {
      headers: {
        'Cache-Control': 'no-cache',
        Pragma: 'no-cache',
        Expires: '0'
      }
    })
  }

  const postApi = (url, data) => {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
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

  return { getApi, postApi, getToken }
}
