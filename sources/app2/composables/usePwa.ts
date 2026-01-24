import { ref, computed, watch } from 'vue'

// Singleton state - initialized once and shared across all usages
let initialized = false
const isOnline = ref(true)
const needRefresh = ref(false)
const offlineReady = ref(false)
let updateServiceWorker: (() => Promise<void>) | null = null
let swRegistration: ServiceWorkerRegistration | null = null

// Initialize PWA only once
const initializePwa = () => {
  if (initialized || !import.meta.client) return
  initialized = true

  // Update online status
  isOnline.value = navigator.onLine

  // Monitor online/offline status
  window.addEventListener('online', () => {
    isOnline.value = true
    // Check for updates when coming back online
    checkForUpdates()
  })

  window.addEventListener('offline', () => {
    isOnline.value = false
  })

  // Check for updates when app becomes visible
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && navigator.onLine) {
      // console.log('[PWA] App became visible, checking for updates...')
      checkForUpdates()
    }
  })

  // Register service worker only in production
  if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    import('virtual:pwa-register/vue').then(({ useRegisterSW }) => {
      const {
        offlineReady: swOfflineReady,
        needRefresh: swNeedRefresh,
        updateServiceWorker: swUpdateServiceWorker
      } = useRegisterSW({
        immediate: true,
        onRegistered(registration) {
          console.log('[PWA] Service Worker registered')
          swRegistration = registration || null

          // Check for updates periodically (every 60 minutes)
          if (registration) {
            setInterval(() => {
              if (navigator.onLine) {
                console.log('[PWA] Periodic update check...')
                registration.update()
              }
            }, 60 * 60 * 1000)
          }
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

      // Sync reactive refs INSIDE the initialization, only once
      watch(swOfflineReady, (val) => { offlineReady.value = val })
      watch(swNeedRefresh, (val) => { needRefresh.value = val })
    }).catch((error) => {
      console.log('[PWA] Service Worker not available:', error)
    })

    // Listen to service worker lifecycle events
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      console.log('[PWA] Service Worker: Controller changed')
    })

    navigator.serviceWorker.ready.then((registration) => {
      console.log('[PWA] Service Worker: Ready')
      swRegistration = registration

      if (registration.waiting) {
        console.log('[PWA] Service Worker: Waiting')
        needRefresh.value = true
      }
    }).catch(() => {
      // Service worker not ready, ignore
    })
  }
}

const checkForUpdates = async () => {
  if (!import.meta.env.PROD || !swRegistration) {
    return
  }

  try {
    console.log('[PWA] Checking for updates...')
    await swRegistration.update()
    console.log('[PWA] Update check completed')
  } catch (error) {
    console.error('[PWA] Update check failed:', error)
  }
}

export const usePwa = () => {
  // Initialize on first call
  initializePwa()

  const updateApp = async () => {
    if (updateServiceWorker) {
      await updateServiceWorker()
    }
  }

  return {
    isOnline: computed(() => isOnline.value),
    needRefresh: computed(() => needRefresh.value),
    offlineReady: computed(() => offlineReady.value),
    updateApp,
    checkForUpdates
  }
}
