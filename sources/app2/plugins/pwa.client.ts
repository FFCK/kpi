export default defineNuxtPlugin(() => {
  // Only register service worker in production
  if (import.meta.env.MODE === 'development' || import.meta.dev) {
    console.log('Service Worker disabled in development mode')
    return
  }

  if ('serviceWorker' in navigator) {
    const config = useRuntimeConfig()
    const baseURL = config.app.baseURL || ''
    // Normalize paths
    const swPath = baseURL ? `${baseURL}/sw.js` : '/sw.js'
    const scope = baseURL ? `${baseURL}/` : '/'

    window.addEventListener('load', () => {
      navigator.serviceWorker
        .register(swPath, { scope })
        .then((registration) => {
          console.log('SW registered:', registration)

          // Check for updates every hour
          setInterval(() => {
            registration.update()
          }, 60 * 60 * 1000)
        })
        .catch((error) => {
          console.error('SW registration failed:', error)
        })
    })
  }
})
