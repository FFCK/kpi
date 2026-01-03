import { watch } from 'vue'

/**
 * Network monitoring composable
 * Watches for online/offline transitions and displays toast notifications
 * @returns {Object} - { isOnline }
 */
export const useNetworkMonitor = () => {
  const { isOnline } = usePwa()
  const toast = useToast()
  const { t } = useI18n()

  // Watch for network state changes
  watch(isOnline, (online, wasOnline) => {
    // Network lost
    if (!online && wasOnline) {
      toast.add({
        title: t('errors.network.offline.title'),
        description: t('errors.network.offline.message'),
        icon: 'i-heroicons-wifi',
        color: 'orange',
        timeout: 6000
      })
    }

    // Network restored
    if (online && !wasOnline) {
      toast.add({
        title: t('errors.network.online.title'),
        description: t('errors.network.online.message'),
        icon: 'i-heroicons-check-circle',
        color: 'green',
        timeout: 4000
      })
    }
  })

  return { isOnline }
}
