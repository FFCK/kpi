export function useMatomo() {
  const config = useRuntimeConfig()

  const isEnabled = () =>
    import.meta.client && config.public.matomoEnabled && !!window._paq

  const trackPageView = (title?: string) => {
    if (!isEnabled()) return
    if (title) window._paq.push(['setDocumentTitle', title])
    window._paq.push(['trackPageView'])
  }

  const trackEvent = (category: string, action: string, name?: string, value?: number) => {
    if (!isEnabled()) return
    window._paq.push(['trackEvent', category, action, ...(name ? [name] : []), ...(value !== undefined ? [value] : [])])
  }

  const setUserId = (userId: string) => {
    if (!isEnabled()) return
    window._paq.push(['setUserId', userId])
  }

  const resetUserId = () => {
    if (!isEnabled()) return
    window._paq.push(['resetUserId'])
    window._paq.push(['appendToTrackingUrl', 'new_visit=1'])
    window._paq.push(['trackPageView'])
    window._paq.push(['appendToTrackingUrl', ''])
  }

  return { trackPageView, trackEvent, setUserId, resetUserId }
}
