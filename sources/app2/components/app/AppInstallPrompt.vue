<template>
  <div
    v-if="showPrompt"
    class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-50 animate-slide-up"
  >
    <div class="p-4">
      <div class="flex items-start gap-3">
        <img src="/img/logo_kp.png" alt="KPI Logo" class="h-12 w-12 flex-shrink-0" />
        <div class="flex-1 min-w-0">
          <h3 class="font-semibold text-gray-900 mb-1">{{ t('AddToHomeScreen.message') }}</h3>
          <p class="text-sm text-gray-600 mb-3">
            {{ appDescription }}
          </p>
          <div class="flex gap-2">
            <button
              @click="install"
              class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors"
            >
              {{ t('AddToHomeScreen.Install') }}
            </button>
            <button
              @click="dismiss"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md font-medium transition-colors"
            >
              {{ t('AddToHomeScreen.Dismiss') }}
            </button>
          </div>
        </div>
        <button
          @click="dismiss"
          class="text-gray-400 hover:text-gray-600 flex-shrink-0"
          aria-label="Close"
        >
          <UIcon name="i-heroicons-x-mark" class="h-5 w-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

const { t } = useI18n()
const preferenceStore = usePreferenceStore()

const showPrompt = ref(false)
const deferredPrompt = ref(null)
const appDescription = ref('Installez l\'application pour un accès rapide et une utilisation hors ligne.')

const handleBeforeInstallPrompt = (e) => {
  // Prevent the mini-infobar from appearing on mobile
  e.preventDefault()
  // Stash the event so it can be triggered later
  deferredPrompt.value = e

  // Check if user has already dismissed or installed
  if (!preferenceStore.preferences.pwa_dismissed && !isInstalled()) {
    showPrompt.value = true
  }
}

const isInstalled = () => {
  // Check if app is running in standalone mode
  if (import.meta.client) {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true
  }
  return false
}

const install = async () => {
  if (!deferredPrompt.value) {
    return
  }

  // Show the install prompt
  deferredPrompt.value.prompt()

  // Wait for the user to respond to the prompt
  const { outcome } = await deferredPrompt.value.userChoice

  if (outcome === 'accepted') {
    console.log('User accepted the install prompt')
  } else {
    console.log('User dismissed the install prompt')
  }

  // Clear the deferredPrompt for garbage collection
  deferredPrompt.value = null
  showPrompt.value = false
}

const dismiss = async () => {
  showPrompt.value = false
  // Remember that user dismissed the prompt
  await preferenceStore.putItem('pwa_dismissed', true)
}

onMounted(async () => {
  await preferenceStore.fetchItems()

  if (import.meta.client) {
    // Check if already installed
    if (isInstalled()) {
      return
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
      console.log('PWA was installed')
      showPrompt.value = false
    })
  }
})

onBeforeUnmount(() => {
  if (import.meta.client) {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  }
})
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>
