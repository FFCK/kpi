<script setup lang="ts">
import type { SchemaPhase } from '~/types/schema'

defineProps<{
  stages: { etape: number; phases: SchemaPhase[] }[]
  showMatchCount: boolean
  showTimeSlots: boolean
  hoveredTeam: string | null
  qualifies: number
  elimines: number
}>()

const emit = defineEmits<{
  hoverTeam: [team: string | null]
}>()

const { t } = useI18n()

const formatTimeRange = (start: string | null, end: string | null) => {
  if (!start) return null
  if (!end || start === end) return start
  return `${start}-${end}`
}
</script>

<template>
  <div class="overflow-x-auto pb-2">
    <div class="flex flex-wrap justify-center gap-4 py-2">
      <!-- One card per stage -->
      <article
        v-for="stage in stages"
        :key="stage.etape"
        class="min-w-80 max-w-110 flex-1 flex flex-col justify-center bg-gray-50 p-4 rounded-lg border"
      >
        <!-- Phases in this column -->
        <div v-for="phase in stage.phases" :key="phase.idJournee" class="mb-4 last:mb-0">
          <!-- Phase header: title + meta on same line -->
          <h6 class="font-semibold text-gray-700 mb-1 text-center">
            {{ phase.phase }}
            <span
              v-if="(showMatchCount && phase.nbMatchs) || (showTimeSlots && formatTimeRange(phase.startTime, phase.endTime))"
              class="font-normal text-gray-400 text-xs"
            >
              ·
              <template v-if="showMatchCount">{{ phase.nbMatchs }} {{ t('schema.phase_games', { count: phase.nbMatchs }, phase.nbMatchs) }}</template>
              <template v-if="showMatchCount && showTimeSlots && formatTimeRange(phase.startTime, phase.endTime)"> · </template>
              <template v-if="showTimeSlots && formatTimeRange(phase.startTime, phase.endTime)">{{ formatTimeRange(phase.startTime, phase.endTime) }}</template>
            </span>
          </h6>

          <!-- Pool ranking (type C) -->
          <SchemaCpPoolTable
            v-if="phase.type === 'C'"
            :phase="phase"
            :hovered-team="hoveredTeam"
            :qualifies="qualifies"
            :elimines="elimines"
            @hover-team="emit('hoverTeam', $event)"
          />

          <!-- Elimination brackets (type E) -->
          <div v-else class="p-1">
            <div class="grid grid-cols-1 gap-3">
              <SchemaCpBracketMatch
                v-for="match in phase.matches"
                :key="match.id"
                :match="match"
                :hovered-team="hoveredTeam"
                @hover-team="emit('hoverTeam', $event)"
              />
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>
