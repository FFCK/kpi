export default defineNuxtPlugin(() => {
  if (typeof window !== 'undefined' && 'serviceWorker' in navigator) {
    // Service worker is registered by @vite-pwa/nuxt module
    console.log('PWA plugin loaded')
  }
})
