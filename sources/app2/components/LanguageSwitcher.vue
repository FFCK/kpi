<script setup lang="ts">
import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

const { locale, locales, setLocale } = useI18n()
const preferenceStore = usePreferenceStore()

const availableLocales = computed(() => {
  return locales.value.filter(i => i.code !== locale.value)
})

const changeLanguage = async (code: string) => {
  setLocale(code)
  await preferenceStore.putItem('lang', code)
}
</script>

<template>
  <div>
    <div class="mt-4 space-x-2">
      <button
        v-for="loc in availableLocales"
        :key="loc.code"
        @click="changeLanguage(loc.code)"
        class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-black"
      >
        {{ loc.name }}
      </button>
    </div>
  </div>
</template>