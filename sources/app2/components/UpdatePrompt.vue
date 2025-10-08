<template>
  <div v-if="needRefresh" class="fixed bottom-20 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-blue-600 text-white rounded-lg shadow-lg p-4 z-50 animate-slide-up">
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <h3 class="font-semibold text-lg mb-1">{{ t('Update.Message') }}</h3>
        <p class="text-sm text-blue-100 mb-3">{{ t('Update.Description') }}</p>
        <div class="flex space-x-2">
          <button
            @click="handleUpdate"
            class="px-4 py-2 bg-white text-blue-600 rounded-md font-semibold hover:bg-blue-50 transition-colors cursor-pointer"
          >
            {{ t('Update.Button') }}
          </button>
          <button
            @click="needRefresh = false"
            class="px-4 py-2 bg-blue-700 text-white rounded-md hover:bg-blue-800 transition-colors cursor-pointer"
          >
            {{ t('Update.Later') }}
          </button>
        </div>
      </div>
      <button
        @click="needRefresh = false"
        class="ml-2 text-blue-200 hover:text-white cursor-pointer"
      >
        <UIcon name="i-heroicons-x-mark" class="h-5 w-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { usePwa } from '~/composables/usePwa'

const { t } = useI18n()
const { needRefresh, updateApp } = usePwa()

const handleUpdate = async () => {
  await updateApp()
  needRefresh.value = false
  // Reload the page after update
  window.location.reload()
}
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>
