declare global {
  interface Window {
    _paq: unknown[][]
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const { matomoUrl, matomoSiteId, matomoEnabled } = config.public

  if (!matomoEnabled || !matomoUrl || !matomoSiteId) return

  window._paq = window._paq ?? []
  window._paq.push(['trackPageView'])
  window._paq.push(['enableLinkTracking'])
  window._paq.push(['setTrackerUrl', `${matomoUrl}/matomo.php`])
  window._paq.push(['setSiteId', String(matomoSiteId)])

  const script = document.createElement('script')
  script.async = true
  script.src = `${matomoUrl}/matomo.js`
  document.head.appendChild(script)

  const router = useRouter()
  router.afterEach((to) => {
    window._paq.push(['setCustomUrl', to.fullPath])
    window._paq.push(['setDocumentTitle', document.title])
    window._paq.push(['trackPageView'])
  })
})
