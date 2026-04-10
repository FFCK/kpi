/**
 * Composable for redirecting to legacy PHP admin pages
 * Used as temporary solution for pages not yet migrated to app4
 */
export const useLegacyRedirect = () => {
  const config = useRuntimeConfig()

  // Base URL for legacy admin (PHP backend)
  const legacyBaseUrl = `${config.public.legacyBaseUrl || ''}/admin`

  /**
   * Redirect to a legacy admin page
   * @param phpPage - The PHP page name (without .php extension)
   */
  const redirectToLegacy = (phpPage: string) => {
    const url = `${legacyBaseUrl}/${phpPage}.php`
    if (import.meta.client) {
      window.location.href = url
    }
    return url
  }

  /**
   * Get the legacy URL for a page
   * @param phpPage - The PHP page name (without .php extension)
   */
  const getLegacyUrl = (phpPage: string) => {
    return `${legacyBaseUrl}/${phpPage}.php`
  }

  return {
    redirectToLegacy,
    getLegacyUrl,
    legacyBaseUrl
  }
}
