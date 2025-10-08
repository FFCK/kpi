import { ref } from 'vue'

export const usePwa = () => {
  // Initialize to true to avoid hydration mismatch
  // Will be updated to real value onMounted client-side
  const isOnline = ref(true)
  const needRefresh = ref(false)
  const offlineReady = ref(false)

  let updateServiceWorker: (() => Promise<void>) | null = null

  // Only run on client-side to avoid hydration issues
  if (import.meta.client) {
    // Update online status immediately on client
    isOnline.value = navigator.onLine
  }

  // Only register service worker in production
  if (import.meta.client && import.meta.env.PROD) {
    import('virtual:pwa-register/vue').then(({ useRegisterSW }) => {
      const {
        offlineReady: swOfflineReady,
        needRefresh: swNeedRefresh,
        updateServiceWorker: swUpdateServiceWorker
      } = useRegisterSW({
        immediate: true,
        onRegistered(registration) {
          console.log('[PWA] Service Worker registered:', registration)
        },
        onRegisterError(error) {
          console.error('[PWA] Service Worker registration error:', error)
        },
        onOfflineReady() {
          console.log('[PWA] App ready to work offline')
          offlineReady.value = true
        },
        onNeedRefresh() {
          console.log('[PWA] New content available, please refresh')
          needRefresh.value = true
        }
      })

      updateServiceWorker = swUpdateServiceWorker
    }).catch(() => {
      console.log('[PWA] Service Worker not available in development mode')
    })
  } else if (import.meta.client) {
    console.log('[PWA] Service Worker disabled in development mode')
  }

  // Monitor online/offline status changes
  if (import.meta.client) {
    window.addEventListener('online', () => {
      console.log('[PWA] Network: Online')
      isOnline.value = true
    })

    window.addEventListener('offline', () => {
      console.log('[PWA] Network: Offline')
      isOnline.value = false
    })

    // Listen to service worker lifecycle events (only in production)
    if (import.meta.env.PROD && 'serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('[PWA] Service Worker: Controller changed')
      })

      navigator.serviceWorker.ready.then((registration) => {
        console.log('[PWA] Service Worker: Ready')

        // Listen to state changes
        if (registration.installing) {
          console.log('[PWA] Service Worker: Installing')
          registration.installing.addEventListener('statechange', (e: Event) => {
            const sw = e.target as ServiceWorker
            console.log('[PWA] Service Worker state:', sw.state)
          })
        }

        if (registration.waiting) {
          console.log('[PWA] Service Worker: Waiting')
        }

        if (registration.active) {
          console.log('[PWA] Service Worker: Active')
        }
      }).catch(() => {
        // Service worker not ready, ignore
      })
    }
  }

  const updateApp = async () => {
    console.log('[PWA] Updating app...')
    if (updateServiceWorker) {
      await updateServiceWorker()
    }
  }

  return {
    isOnline,
    needRefresh,
    offlineReady,
    updateApp
  }
}
