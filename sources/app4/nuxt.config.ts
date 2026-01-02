// https://nuxt.com/docs/api/configuration/nuxt-config

const baseUrl = process.env.BASE_URL ?? '/admin2'
const api2BaseUrl = process.env.API2_BASE_URL ?? 'https://kpi.localhost/api2'

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
      api2BaseUrl
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

  icon: {
    provider: 'iconify',
    clientBundle: {
      scan: true,
      includeCustomCollections: true
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

  css: ['@/assets/css/admin.css']
})
