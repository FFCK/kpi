// https://nuxt.com/docs/api/configuration/nuxt-config

const baseUrl = process.env.BASE_URL ?? ''
const apiBaseUrl = process.env.API_BASE_URL ?? 'https://kpi.localhost/api'
const backendBaseUrl = process.env.BACKEND_BASE_URL ?? 'https://kpi.localhost'

// Helper pour construire les chemins PWA
const pwaPath = (path: string) => baseUrl ? `${baseUrl}/${path}` : `/${path}`
const pwaScope = baseUrl ? `${baseUrl}/` : '/'

export default defineNuxtConfig({
  app: {
    baseURL: baseUrl,
    head: {
      meta: [
        { name: 'theme-color', content: '#1f2937' },
        { name: 'mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' }
      ],
      link: [
        { rel: 'sitemap', type: 'application/xml', href: pwaPath('sitemap.xml') },
        { rel: 'manifest', href: pwaPath('manifest.webmanifest') },
        { rel: 'icon', type: 'image/png', sizes: '192x192', href: pwaPath('pwa-192x192.png') },
        { rel: 'icon', type: 'image/png', sizes: '512x512', href: pwaPath('pwa-512x512.png') },
        { rel: 'apple-touch-icon', href: pwaPath('pwa-512x512.png') }
      ]
    }
  },
  runtimeConfig: {
    public: {
      baseUrl,
      apiBaseUrl,
      backendBaseUrl
    }
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  devServer: {
    port: 3000
  },
  modules: ['@nuxt/eslint', '@pinia/nuxt', '@nuxtjs/i18n', '@nuxt/ui', '@vite-pwa/nuxt'],
  pwa: {
    registerType: 'autoUpdate',
    base: pwaScope,
    scope: pwaScope,
    injectRegister: null,
    manifestFilename: 'manifest.webmanifest',
    strategies: 'generateSW',
    manifest: {
      name: 'KPI Application',
      short_name: 'KPI App',
      description: 'Kayak Polo Information - Live scores and statistics',
      theme_color: '#1f2937',
      background_color: '#ffffff',
      display: 'standalone',
      orientation: 'portrait',
      scope: pwaScope,
      start_url: pwaScope,
      icons: [
        {
          src: pwaPath('pwa-192x192.png'),
          sizes: '192x192',
          type: 'image/png',
          purpose: 'any'
        },
        {
          src: pwaPath('pwa-512x512.png'),
          sizes: '512x512',
          type: 'image/png',
          purpose: 'any'
        },
        {
          src: pwaPath('pwa-512x512.png'),
          sizes: '512x512',
          type: 'image/png',
          purpose: 'maskable'
        }
      ]
    },
    workbox: {
      navigateFallback: baseUrl ? `${baseUrl}/index.html` : '/index.html',
      cleanupOutdatedCaches: true,
      globPatterns: ['**/*.{js,css,html,png,svg,ico,woff2}'],
      globDirectory: '.output/public',
      navigateFallbackDenylist: [/^\/api\//],
      manifestTransforms: [
        (manifestEntries) => {
          // Filter out empty URLs and fix baseUrl
          const manifest = manifestEntries.map(entry => {
            if (entry.url === '' || entry.url === '/') {
              return { ...entry, url: '/index.html' }
            }
            return entry
          }).filter(entry => entry.url !== '')
          return { manifest }
        }
      ],
      runtimeCaching: [
        {
          urlPattern: new RegExp('^' + apiBaseUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '/.*', 'i'),
          handler: 'NetworkOnly'
        }
      ]
    },
    devOptions: {
      enabled: false, // Disabled in dev to avoid caching issues
      type: 'module'
    }
  },
  i18n: {
    strategy: 'no_prefix',
    defaultLocale: 'fr',
    langDir: 'locales',
    locales: [
      { code: 'en', file: 'en.json', name: 'English' },
      { code: 'fr', file: 'fr.json', name: 'Français' },
    ],
  },
  css: ['@/assets/css/app.css'],
  vite: {
    plugins: [
    ],
  },
})