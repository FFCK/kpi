<script setup lang="ts">
import type { SchemaPhase } from '~/types/schema'

defineProps<{
  phase: SchemaPhase
  hoveredTeam: string | null
}>()

const emit = defineEmits<{
  hoverTeam: [team: string | null]
}>()

const formatDateRange = (start: string | null, end: string | null) => {
  if (!start) return ''
  const fmt = (d: string) => {
    const [y, m, dd] = d.split('-')
    return `${dd}/${m}/${y}`
  }
  if (!end || start === end) return fmt(start)
  return `${fmt(start)} - ${fmt(end)}`
}

</script>

<template>
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Gameday header -->
    <div class="px-4 py-2 bg-header-50 border-b border-header-200">
      <div class="flex items-center gap-2">
        <span class="font-semibold text-header-800">{{ phase.phase }}</span>
        <template v-if="phase.lieu || phase.departement">
          <span class="text-header-400">—</span>
          <span class="text-sm text-header-600">
            {{ phase.lieu }}<span v-if="phase.departement"> ({{ phase.departement }})</span>
          </span>
        </template>
        <span v-if="phase.dateDebut" class="text-sm text-header-500 ml-auto">
          {{ formatDateRange(phase.dateDebut, phase.dateFin) }}
        </span>
      </div>
    </div>

    <!-- Match grid: 3 cols desktop, 2 tablet, 1 mobile -->
    <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
      <SchemaMatchResult
        v-for="match in phase.matches"
        :key="match.id"
        :match="match"
        :hovered-team="hoveredTeam"
        @hover-team="emit('hoverTeam', $event)"
      />
    </div>
  </div>
</template>
