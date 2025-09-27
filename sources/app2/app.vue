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
