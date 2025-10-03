// https://nuxt.com/docs/api/configuration/nuxt-config

export default defineNuxtConfig({
  app: {
    baseURL: '/app2'
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL || 'https://kpi.local/api',
      backendBaseUrl: process.env.BACKEND_BASE_URL || 'https://kpi.local'
    }
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  devServer: {
    port: 3000
  },
  nitro: {
    port: parseInt(process.env.NITRO_PORT || '3000')
  },
  modules: ['@nuxt/eslint', '@pinia/nuxt', '@nuxtjs/i18n', '@nuxt/ui'],
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