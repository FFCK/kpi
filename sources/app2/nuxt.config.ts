// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from "@tailwindcss/vite"

export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.API_BASE_URL || 'https://kpi.local/api'
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
  modules: ['@nuxt/eslint', '@pinia/nuxt'],
  css: ['@/assets/css/app.css'],
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
  plugins: [
    '@/plugins/dexie.js'
  ],
})