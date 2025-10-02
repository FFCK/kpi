<template>
  <div class="container-fluid mb-16">
    <div class="p-4 bg-white border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <button @click="navigateTo('/games')" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
          </button>
          <div class="ml-4 px-3 py-2 text-center text-base bg-gray-100 rounded-md min-w-32">
            {{ t('nav.Chart') }}
          </div>
        </div>
        <div class="flex items-center">
          <button :disabled="!visibleButton" @click="loadCharts" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
          </button>
          <button @click="navigateTo('/about')" class="ml-4 p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-right" class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

    <Charts v-if="chartData" :key="chartIndex" :chart-data="chartData" :show-flags="showFlags" />

    <button @click="scrollToTop" class="fixed bottom-4 right-4 bg-gray-800 hover:bg-gray-700 text-white font-bold p-3 rounded-full">
      <UIcon name="i-heroicons-arrow-up" class="h-6 w-6" />
    </button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCharts } from '~/composables/useCharts'
import Charts from '~/components/Charts.vue'
import { navigateTo } from '#app'

const { t } = useI18n()

const {
  chartData,
  chartIndex,
  visibleButton,
  showFlags,
  loadCharts,
  getFav
} = useCharts()

onMounted(async () => {
  await getFav()
  await loadCharts()
})

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>