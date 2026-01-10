import { ref } from 'vue'

// Global state to ensure single initialization
const isOnline = ref(true)
const isInitialized = ref(false)
const wasOffline = ref(false)

// Store references to toast and t functions
let toastRef: ReturnType<typeof useToast> | null = null
let tRef: ReturnType<typeof useI18n>['t'] | null = null

const showOfflineToast = () => {
  if (toastRef && tRef) {
    toastRef.add({
      id: 'offline-status',
      title: tRef('status.offline'),
      description: tRef('status.offline_description'),
      icon: 'i-heroicons-signal-slash',
      color: 'warning',
      duration: 5000
    })
  }
}

const showOnlineToast = () => {
  if (toastRef && tRef) {
    toastRef.add({
      id: 'online-status',
      title: tRef('status.back_online'),
      icon: 'i-heroicons-wifi',
      color: 'success',
      duration: 3000
    })
  }
}

const handleOnline = () => {
  isOnline.value = true

  // Show toast only if we were previously offline
  if (wasOffline.value) {
    showOnlineToast()
    wasOffline.value = false
  }
}

const handleOffline = () => {
  console.log('[OnlineStatus] handleOffline triggered')
  isOnline.value = false
  wasOffline.value = true
  showOfflineToast()
}

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
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

export const useOnlineStatus = () => {
  // Update references each time the composable is called
  toastRef = useToast()
  const { t } = useI18n()
  tRef = t

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
