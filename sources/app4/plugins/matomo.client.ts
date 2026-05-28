declare global {
  interface Window {
    _paq: unknown[][]
  }
}

export default defineNuxtPlugin(() => {
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
})
