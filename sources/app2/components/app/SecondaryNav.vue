<template>
  <div class="py-1 px-1 bg-white border-b border-gray-200">
    <div class="grid grid-cols-3 items-center">
      <div class="flex items-center justify-start">
        <button v-if="!hideLeft" @click="navigateTo(previousPage)" class="p-2 rounded-md hover:bg-gray-100 cursor-pointer">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
        </button>
        <slot name="left" />
      </div>
      <div class="flex items-center justify-center">
        <UIcon v-if="isLoading" name="i-heroicons-arrow-path" class="h-6 w-6 animate-spin text-blue-600" />
        <template v-else>
          <span v-if="eventInfo" class="hidden md:inline text-sm font-medium text-gray-700 text-center truncate max-w-xs lg:max-w-md">
            {{ eventInfo }}
          </span>
          <slot name="center" />
        </template>
      </div>
      <div class="flex items-center justify-end">
        <slot name="right" />
        <button v-if="!hideRight" @click="navigateTo(nextPage)" class="p-2 rounded-md hover:bg-gray-100 cursor-pointer">
          <UIcon name="i-heroicons-arrow-right" class="h-6 w-6" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useNavigation } from '~/composables/useNavigation'
import { useLoadingState } from '~/composables/useLoadingState'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { navigateTo } from '#app'

const { getGroupLabel } = useGroupLabel()

const props = defineProps({
  hideLeft: {
    type: Boolean,
    default: false
  },
  hideRight: {
    type: Boolean,
    default: false
  },
  showEventInfo: {
    type: Boolean,
    default: false
  }
})

const { previousPage, nextPage } = useNavigation()
const { isLoading } = useLoadingState()
const preferenceStore = usePreferenceStore()

const eventInfo = computed(() => {
  if (!props.showEventInfo) return null

  const prefs = preferenceStore.preferences
  if (prefs?.lastEvent) {
    return `${prefs.lastEvent.libelle} (${prefs.lastEvent.year})`
  } else if (prefs?.lastGroup && prefs?.lastSeason) {
    return `${getGroupLabel(prefs.lastGroup)} (${prefs.lastSeason})`
  }
  return null
})
</script>
