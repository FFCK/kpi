<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useI18n } from 'vue-i18n'

const preferenceStore = usePreferenceStore()
const { locale } = useI18n()
const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.baseUrl || ''
const backendBaseUrl = runtimeConfig.public.backendBaseUrl

// SEO Meta Tags
useSeoMeta({
  title: 'KPI Application - Kayak Polo Information',
  description: 'Live scores, rankings, and statistics for kayak polo competitions. Follow your favorite teams and matches in real-time.',
  ogTitle: 'KPI Application - Kayak Polo Information',
  ogDescription: 'Live scores, rankings, and statistics for kayak polo competitions',
  ogImage: `${baseUrl}/img/logo_kp.png`,
  ogUrl: backendBaseUrl,
  ogType: 'website',
  twitterCard: 'summary',
  twitterTitle: 'KPI Application - Kayak Polo Information',
  twitterDescription: 'Live scores, rankings, and statistics for kayak polo competitions',
  twitterImage: `${baseUrl}/img/logo_kp.png`,
  themeColor: '#1f2937',
  colorScheme: 'light',
  robots: 'index, follow'
})

// Head configuration
useHead({
  htmlAttrs: {
    lang: locale
  },
  link: [
    { rel: 'icon', type: 'image/x-icon', href: `${baseUrl}/favicon.ico` },
    { rel: 'apple-touch-icon', href: `${baseUrl}/apple-touch-icon.png` }
    // Note: manifest is automatically added by @vite-pwa/nuxt
  ],
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes' },
    { name: 'format-detection', content: 'telephone=no' },
    { name: 'mobile-web-app-capable', content: 'yes' },
    { name: 'apple-mobile-web-app-capable', content: 'yes' },
    { name: 'apple-mobile-web-app-status-bar-style', content: 'black-translucent' },
    { name: 'apple-mobile-web-app-title', content: 'KPI App' }
  ]
})

onMounted(async () => {
  await preferenceStore.fetchItems()
  if (preferenceStore.preferences.lang) {
    locale.value = preferenceStore.preferences.lang
  }
})
</script>

<style>
@import url("~/assets/css/app.css");
@import "tailwindcss";

</style>
