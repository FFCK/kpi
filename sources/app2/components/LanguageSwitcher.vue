<script setup lang="ts">
const { t, locale, availableLocales } = useI18n()

function switchLocale(newLocale: string) {
  locale.value = newLocale
}
</script>

<template>
  <div>
    <div class="mt-4 space-x-2">
      <button
        v-for="loc in availableLocales"
        :key="loc"
        @click="switchLocale(loc)"
        class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
      >
        {{ loc.toUpperCase() }}
      </button>
    </div>
  </div>
</template>