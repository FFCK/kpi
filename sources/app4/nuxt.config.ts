// https://nuxt.com/docs/api/configuration/nuxt-config

const baseUrl = process.env.BASE_URL ?? '/admin2'
const api2BaseUrl = process.env.API2_BASE_URL ?? 'https://kpi.localhost/api2'
const legacyBaseUrl = process.env.LEGACY_BASE_URL ?? 'https://kpi.localhost'

export default defineNuxtConfig({
  ssr: false,

  app: {
    baseURL: baseUrl,
    head: {
      title: 'KPI Admin',
      meta: [
        { name: 'theme-color', content: '#1e40af' },
        { name: 'description', content: 'KPI Administration Panel' }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: `${baseUrl}/favicon.png` }
      ]
    }
  },

  runtimeConfig: {
    public: {
      baseUrl,
      api2BaseUrl,
      legacyBaseUrl
    }
  },

  compatibilityDate: '2025-07-15',

  devtools: { enabled: true },

  devServer: {
    port: 3004
  },

  modules: [
    '@nuxt/eslint',
    '@pinia/nuxt',
    '@nuxtjs/i18n',
    '@nuxt/ui'
  ],

  // Force light mode for admin interface
  colorMode: {
    preference: 'light',
    fallback: 'light',
    classSuffix: ''
  },

  icon: {
    provider: 'iconify',
    clientBundle: {
      scan: true,
      includeCustomCollections: true,
      icons: [
        'heroicons:bars-3',
        'heroicons:x-mark',
        'heroicons:calendar-days',
        'heroicons:document-text',
        'heroicons:chart-bar',
        'heroicons:cog-6-tooth',
        'heroicons:arrow-right-on-rectangle',
        'heroicons:chevron-down',
        'heroicons:magnifying-glass',
        'heroicons:plus',
        'heroicons:pencil',
        'heroicons:trash',
        'heroicons:check-circle',
        'heroicons:x-circle',
        'heroicons:device-phone-mobile',
        'heroicons:arrows-up-down',
        'heroicons:arrow-up',
        'heroicons:arrow-down',
        'heroicons:chevron-left',
        'heroicons:chevron-right',
        'heroicons:arrow-path',
        'heroicons:exclamation-triangle',
        'heroicons:wifi',
        'heroicons:signal-slash',
        'heroicons:check-circle-solid',
        'heroicons:x-circle-solid',
        'heroicons:device-phone-mobile-solid',
        'heroicons:pencil-solid',
        'heroicons:trash-solid',
        'heroicons:user-group',
        'heroicons:trophy',
        'heroicons:chart-bar-square',
        'heroicons:calendar',
        'heroicons:shield-check',
        'heroicons:document-duplicate',
        'heroicons:table-cells',
        'heroicons:arrow-top-right-on-square',
        'heroicons:qr-code',
        'heroicons:eye-solid',
        'heroicons:map-pin-solid',
        'heroicons:calendar-solid',
        'heroicons:lock-closed-solid',
        'heroicons:lock-open-solid'
      ]
    }
  },

  i18n: {
    strategy: 'no_prefix',
    defaultLocale: 'fr',
    langDir: 'locales',
    locales: [
      { code: 'en', file: 'en.json', name: 'English' },
      { code: 'fr', file: 'fr.json', name: 'Français' }
    ]
  },

  tailwindcss: {
    configPath: 'tailwind.config.ts'
  },

  css: ['@/assets/css/admin.css']
})
