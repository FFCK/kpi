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
        .register(swPath, {
          scope,
          updateViaCache: 'none' // Force check for updates, bypass HTTP cache
        })
        .then((registration) => {
          console.log('SW registered:', registration)

          // Force immediate update check on page load
          registration.update()

          // Check for updates when page becomes visible
          document.addEventListener('visibilitychange', () => {
            if (!document.hidden && registration) {
              console.log('Page visible, checking for SW updates...')
              registration.update()
            }
          })

          // Listen for new service worker installation
          registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing
            console.log('New Service Worker installing...')

            if (newWorker) {
              newWorker.addEventListener('statechange', () => {
                console.log('SW state changed to:', newWorker.state)
                if (newWorker.state === 'activated' && !navigator.serviceWorker.controller) {
                  // First time activation - reload to start using SW
                  console.log('First SW activation, reloading...')
                  window.location.reload()
                }
              })
            }
          })

          // Handle SW controller change (when new SW takes control)
          navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('New Service Worker took control, reloading...')
            window.location.reload()
          })
        })
        .catch((error) => {
          console.error('SW registration failed:', error)
        })
    })
  }
})
