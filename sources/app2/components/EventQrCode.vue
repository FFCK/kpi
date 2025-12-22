<template>
  <div v-if="hasEventSelected" class="event-qrcode-container text-center my-8">
    <div class="qrcode-wrapper inline-block">
      <canvas ref="qrcodeCanvas" class="mx-auto"></canvas>
    </div>
    <p class="mt-3 text-gray-700 font-medium">
      {{ t('Event.ShareEvent') }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useEventGuard } from '~/composables/useEventGuard'
import QRCode from 'qrcode'

const { t } = useI18n()
const preferenceStore = usePreferenceStore()
const { hasEventSelected } = useEventGuard()
const runtimeConfig = useRuntimeConfig()

const qrcodeCanvas = ref(null)

/**
 * Generate QR code with centered logo
 */
const generateQRCode = async () => {
  if (!hasEventSelected.value || !qrcodeCanvas.value) {
    return
  }

  const eventId = preferenceStore.preferences.lastEvent?.id
  if (!eventId) return

  // Construct the event URL
  const backendBaseUrl = runtimeConfig.public.backendBaseUrl || window.location.origin
  const eventUrl = `${backendBaseUrl}/evt/${eventId}`

  try {
    const canvas = qrcodeCanvas.value
    const ctx = canvas.getContext('2d')

    // Generate QR code on canvas with high error correction (to allow logo overlay)
    await QRCode.toCanvas(canvas, eventUrl, {
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

// Watch for event changes and regenerate QR code
watch(
  () => preferenceStore.preferences.lastEvent,
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
