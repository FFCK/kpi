// https://nuxt.com/docs/api/configuration/nuxt-config

const baseUrl = process.env.BASE_URL ?? ''
const apiBaseUrl = process.env.API_BASE_URL ?? 'https://kpi.localhost/api'
const backendBaseUrl = process.env.BACKEND_BASE_URL ?? 'https://kpi.localhost'

// Generate unique build ID based on timestamp to force cache invalidation
// This ensures browser cache is invalidated when deploying new builds
const buildId = `v${Date.now()}`

// Helper pour construire les chemins PWA
const pwaPath = (path: string) => baseUrl ? `${baseUrl}/${path}` : `/${path}`
const pwaScope = baseUrl ? `${baseUrl}/` : '/'

export default defineNuxtConfig({
  ssr: false,
  app: {
    baseURL: baseUrl,
    buildAssetsDir: `/_nuxt/${buildId}/`,
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
  icon: {
    // Use local icons from @iconify-json/heroicons instead of CDN
    provider: 'iconify',
    clientBundle: {
      // Include heroicons in client bundle (embedded, no CDN)
      scan: true,
      includeCustomCollections: true
    }
  },
  pwa: {
    registerType: 'autoUpdate',
    base: pwaScope,
    scope: pwaScope,
    injectRegister: false, // We'll handle registration manually
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
      // Disable precaching entirely - use runtime caching only
      globPatterns: [],
      globDirectory: '.output/public',
      navigateFallbackDenylist: [/^\/api\//],
      // Include buildId in cache name to force SW update on new builds
      cacheId: `kpi-app2-${buildId}`,
      // Immediately activate new Service Worker
      skipWaiting: true,
      clientsClaim: true,
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
          // Network-first strategy for navigation requests (HTML pages)
          urlPattern: ({ request }) => request.mode === 'navigate',
          handler: 'NetworkFirst',
          options: {
            cacheName: 'pages',
            networkTimeoutSeconds: 3,
            expiration: {
              maxEntries: 10,
              maxAgeSeconds: 300 // 5 minutes max cache
            }
          }
        },
        {
          // NetworkFirst for JS/CSS - always check network first
          urlPattern: /\/_nuxt\/.*\.(js|css)$/,
          handler: 'NetworkFirst',
          options: {
            cacheName: 'assets-js-css',
            networkTimeoutSeconds: 3,
            expiration: {
              maxEntries: 100,
              maxAgeSeconds: 3600 // 1 hour
            }
          }
        },
        {
          // CacheFirst for images and fonts (they don't change)
          urlPattern: /\.(png|jpg|jpeg|svg|gif|webp|ico|woff|woff2|ttf|eot)$/,
          handler: 'CacheFirst',
          options: {
            cacheName: 'assets-static',
            expiration: {
              maxEntries: 100,
              maxAgeSeconds: 86400 // 24 hours
            }
          }
        },
        {
          // API calls always from network
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