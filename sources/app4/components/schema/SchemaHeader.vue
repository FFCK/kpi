<script setup lang="ts">
import type { SchemaCompetition } from '~/types/schema'

const props = defineProps<{
  competition: SchemaCompetition
  totalMatches: number
  showMatchCount: boolean
  showTimeSlots: boolean
  isCp: boolean
}>()

const emit = defineEmits<{
  toggleMatchCount: []
  toggleTimeSlots: []
}>()

const { t } = useI18n()

const title = computed(() => {
  let s = props.competition.libelle
  if (props.competition.soustitre2) {
    s += ' - ' + props.competition.soustitre2
  }
  return s
})
</script>

<template>
  <div class="mb-4 bg-white rounded-lg shadow p-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <!-- Title -->
      <h2 class="text-lg font-semibold text-gray-900">
        {{ title }}
        <!-- Season badge -->
        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-50 text-blue-700">
          {{ competition.season }}
        </span>
      </h2>

      <!-- Right side: badges + toggles -->
      <div class="flex items-center gap-3 flex-wrap">

        <!-- Game count badge -->
        <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
          {{ t('schema.games_count', { count: totalMatches }, totalMatches) }}
        </span>

        <!-- Toggles (CP only) -->
        <template v-if="isCp">
          <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showMatchCount"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @change="emit('toggleMatchCount')"
            >
            {{ t('schema.show_game_count') }}
          </label>
          <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showTimeSlots"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @change="emit('toggleTimeSlots')"
            >
            {{ t('schema.show_time_slots') }}
          </label>
        </template>
      </div>
    </div>
  </div>
</template>
