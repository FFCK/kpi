<template>
  <div v-if="hasSelection" class="event-qrcode-container text-center my-12">
    <p class="mb-1 text-gray-700 font-medium font-semibold text-lg py-2 flex items-center justify-center gap-2">
      <UIcon name="i-heroicons-share" class="h-6 w-6 flex-shrink-0" />
      <span class="align-middle">{{ t('Event.ShareEvent') }}</span>
    </p>
    <div class="qrcode-wrapper inline-block cursor-pointer" @click="copyLinkToClipboard">
      <canvas ref="qrcodeCanvas" class="mx-auto"></canvas>
      <p class="text-sm text-gray-600 mt-2">{{ t('Event.ClickToCopy') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, computed } from 'vue'
import QRCode from 'qrcode'

const { t } = useI18n()
const preferenceStore = usePreferenceStore()
const { hasEventSelected } = useEventGuard()
const runtimeConfig = useRuntimeConfig()
const toast = useToast()

const qrcodeCanvas = ref(null)

// Check if we have either an event or a group selected
const hasSelection = computed(() => {
  return hasEventSelected.value ||
         (preferenceStore.preferences?.lastGroup !== undefined && preferenceStore.preferences?.lastGroup !== null)
})

/**
 * Get the share URL based on current selection (event or group)
 */
const getShareUrl = () => {
  const baseUrl = runtimeConfig.public.baseUrl || ''
  const origin = window.location.origin

  // Check if we have an event selected
  const eventId = preferenceStore.preferences.lastEvent?.id
  if (eventId) {
    return `${origin}${baseUrl}/event/${eventId}`
  }

  // Check if we have a group selected
  const groupCode = preferenceStore.preferences.lastGroup?.code
  const season = preferenceStore.preferences.lastSeason
  if (groupCode && season) {
    return `${origin}${baseUrl}/group/${season}/${groupCode}`
  }

  return null
}

/**
 * Generate QR code with centered logo
 */
const generateQRCode = async () => {
  if (!hasSelection.value || !qrcodeCanvas.value) {
    return
  }

  const shareUrl = getShareUrl()
  if (!shareUrl) return

  try {
    const canvas = qrcodeCanvas.value
    const ctx = canvas.getContext('2d')

    // Generate QR code on canvas with high error correction (to allow logo overlay)
    await QRCode.toCanvas(canvas, shareUrl, {
      errorCorrectionLevel: 'H',
      margin: 2,
      width: 280,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    })

    // Load and draw logo in the center
    const logo = new Image()
    logo.crossOrigin = 'anonymous'
    logo.src = '/img/logo_kp.png'

    logo.onload = () => {
      const logoSize = 60 // Size of the logo in the center
      const x = (canvas.width - logoSize) / 2
      const y = (canvas.height - logoSize) / 2

      // Draw white background circle for logo
      ctx.fillStyle = '#FFFFFF'
      ctx.beginPath()
      ctx.arc(canvas.width / 2, canvas.height / 2, logoSize / 2 + 5, 0, 2 * Math.PI)
      ctx.fill()

      // Draw logo
      ctx.drawImage(logo, x, y, logoSize, logoSize)
    }

    logo.onerror = (error) => {
      console.warn('Failed to load logo for QR code:', error)
      // QR code still works without logo
    }
  } catch (error) {
    console.error('Failed to generate QR code:', error)
  }
}

/**
 * Copy link to clipboard
 */
const copyLinkToClipboard = async () => {
  if (!hasSelection.value) {
    return
  }

  const shareUrl = getShareUrl()
  if (!shareUrl) return

  try {
    await navigator.clipboard.writeText(shareUrl)

    // Show success toast notification
    toast.add({
      title: t('Event.LinkCopied'),
      description: shareUrl,
      icon: 'i-heroicons-check-circle',
      color: 'success',
      duration: 3000
    })
  } catch (error) {
    console.error('Failed to copy link to clipboard:', error)

    // Show error toast notification
    toast.add({
      title: t('Event.CopyFailed'),
      description: error.message,
      icon: 'i-heroicons-x-circle',
      color: 'error',
      duration: 3000
    })
  }
}

// Watch for event changes and regenerate QR code
watch(
  () => preferenceStore.preferences.lastEvent,
  async () => {
    await nextTick()
    generateQRCode()
  },
  { immediate: true }
)

// Watch for group changes and regenerate QR code
watch(
  () => preferenceStore.preferences.lastGroup,
  async () => {
    await nextTick()
    generateQRCode()
  },
  { immediate: true }
)

onMounted(() => {
  generateQRCode()
})
</script>

<style scoped>
.event-qrcode-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.qrcode-wrapper {
  padding: 1rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

canvas {
  display: block;
  border-radius: 0.25rem;
}
</style>
