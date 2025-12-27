import { ref, onMounted, onUnmounted } from 'vue'

// Global state to ensure single initialization
const isOnline = ref(true)
const isInitialized = ref(false)
const wasOffline = ref(false)

export const useOnlineStatus = () => {
  const toast = useToast()
  const { t } = useI18n()

  const handleOnline = () => {
    console.log('[OnlineStatus] Network: Online')
    isOnline.value = true

    // Show toast only if we were previously offline
    if (wasOffline.value) {
      toast.add({
        id: 'online-status',
        title: t('status.BackOnline'),
        icon: 'i-heroicons-wifi',
        color: 'success',
        timeout: 3000
      })
      wasOffline.value = false
    }
  }

  const handleOffline = () => {
    console.log('[OnlineStatus] Network: Offline')
    isOnline.value = false
    wasOffline.value = true

    toast.add({
      id: 'offline-status',
      title: t('status.Offline'),
      description: t('status.OfflineDescription'),
      icon: 'i-heroicons-wifi',
      color: 'warning',
      timeout: 5000
    })
  }

  const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
      console.log('[OnlineStatus] App became visible')
      // Update online status when app becomes visible
      const currentOnlineStatus = navigator.onLine
      if (currentOnlineStatus !== isOnline.value) {
        if (currentOnlineStatus) {
          handleOnline()
        } else {
          handleOffline()
        }
      }
    }
  }

  const checkConnection = (): boolean => {
    if (import.meta.client) {
      const currentStatus = navigator.onLine
      if (currentStatus !== isOnline.value) {
        isOnline.value = currentStatus
      }
      return currentStatus
    }
    return true
  }

  // Initialize listeners only once
  if (import.meta.client && !isInitialized.value) {
    isOnline.value = navigator.onLine
    wasOffline.value = !navigator.onLine
    isInitialized.value = true

    window.addEventListener('online', handleOnline)
    window.addEventListener('offline', handleOffline)
    document.addEventListener('visibilitychange', handleVisibilityChange)
  }

  return {
    isOnline,
    checkConnection
  }
}
