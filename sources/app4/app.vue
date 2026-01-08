<script setup lang="ts">
// KPI Admin - Root component
const { locale } = useI18n()

// Head configuration
useHead({
  htmlAttrs: {
    lang: locale
  },
  meta: [
    { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes' }
  ]
})
</script>

<template>
  <UApp>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <UNotifications />
  </UApp>
</template>

<style>
html, body {
  margin: 0;
  padding: 0;
  min-height: 100vh;
}
</style>
