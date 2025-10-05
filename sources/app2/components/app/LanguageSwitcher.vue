<script setup lang="ts">
import { computed } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore'

const { locale, locales, setLocale } = useI18n()
const preferenceStore = usePreferenceStore()

const availableLocales = computed(() => {
  return locales.value
})

const flagEmojis: Record<string, string> = {
  'en': '🇬🇧',
  'fr': '🇫🇷'
}

const changeLanguage = async (code: string) => {
  setLocale(code as 'en' | 'fr')
  await preferenceStore.putItem('lang', code)
}
</script>

<template>
  <div>
    <div class="flex space-x-2">
      <button
        v-for="loc in availableLocales"
        :key="loc.code"
        @click="changeLanguage(loc.code)"
        :class="[
          'text-2xl transition-all duration-200 cursor-pointer',
          locale === loc.code
            ? 'opacity-100 scale-110 drop-shadow-lg'
            : 'opacity-50 hover:opacity-75 hover:scale-105'
        ]"
        :title="loc.name"
      >
        {{ flagEmojis[loc.code] }}
      </button>
    </div>
  </div>
</template>