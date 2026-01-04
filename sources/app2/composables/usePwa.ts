import { ref, computed, watch } from 'vue'

// Singleton state - initialized once and shared across all usages
let initialized = false
const isOnline = ref(true)
const needRefresh = ref(false)
const offlineReady = ref(false)
let updateServiceWorker: (() => Promise<void>) | null = null

// Initialize PWA only once
const initializePwa = () => {
  if (initialized || !import.meta.client) return
  initialized = true
    
  // Update online status
  isOnline.value = navigator.onLine

  // Monitor online/offline status
  window.addEventListener('online', () => {
    isOnline.value = true
  })

  window.addEventListener('offline', () => {
    isOnline.value = false
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
    updateApp
  }
}
