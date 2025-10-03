<template>
  <div class="py-1 px-4 bg-white border-b border-gray-200">
    <div class="grid grid-cols-3 items-center">
      <div class="flex items-center justify-start">
        <button v-if="!hideLeft" @click="navigateTo(previousPage)" class="p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
        </button>
        <slot name="left" />
      </div>
      <div class="flex items-center justify-center">
        <slot name="center" />
      </div>
      <div class="flex items-center justify-end">
        <slot name="right" />
        <button v-if="!hideRight" @click="navigateTo(nextPage)" class="ml-4 p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-right" class="h-6 w-6" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useNavigation } from '~/composables/useNavigation'
import { navigateTo } from '#app'

const props = defineProps({
  hideLeft: {
    type: Boolean,
    default: false
  },
  hideRight: {
    type: Boolean,
    default: false
  }
})

const { previousPage, nextPage } = useNavigation()
</script>
