<script setup lang="ts">
import type { SchemaMatch } from '~/types/schema'

const props = defineProps<{
  match: SchemaMatch
  hoveredTeam: string | null
}>()

const emit = defineEmits<{
  hoverTeam: [team: string | null]
}>()

const hasScore = computed(() => props.match.scoreA !== null && props.match.scoreB !== null)

const scoreA = computed(() => hasScore.value ? parseInt(props.match.scoreA!) : null)
const scoreB = computed(() => hasScore.value ? parseInt(props.match.scoreB!) : null)

const winnerSide = computed(() => {
  if (scoreA.value === null || scoreB.value === null) return null
  if (scoreA.value > scoreB.value) return 'A'
  if (scoreB.value > scoreA.value) return 'B'
  return null
})

const getTeamTextClass = (side: 'A' | 'B') => {
  const teamName = side === 'A' ? props.match.equipeA : props.match.equipeB
  if (props.hoveredTeam && teamName === props.hoveredTeam) return 'text-warning-800 font-bold'
  if (!hasScore.value) return 'text-header-700'
  if (winnerSide.value === side) return 'text-primary-700 font-bold'
  if (winnerSide.value !== null) return 'text-header-400'
  return 'text-header-600'
}

const getScoreClass = (side: 'A' | 'B') => {
  if (!hasScore.value) return ''
  if (winnerSide.value === side) return 'text-primary-700 font-bold'
  if (winnerSide.value !== null) return 'text-header-400'
  return 'text-header-600'
}
</script>

<template>
  <div
    class="flex items-center text-sm border border-header-200 rounded px-2 py-1 transition-colors duration-100"
    :class="{ 'bg-warning-50': hoveredTeam && (match.equipeA === hoveredTeam || match.equipeB === hoveredTeam) }"
  >
    <!-- Team A -->
    <span
      class="flex-1 truncate text-right transition-colors duration-100"
      :class="getTeamTextClass('A')"
      :title="match.equipeA"
      @mouseenter="emit('hoverTeam', match.equipeA)"
      @mouseleave="emit('hoverTeam', null)"
    >
      {{ match.equipeA }}
    </span>

    <!-- Scores -->
    <div class="flex items-center gap-1 mx-2 tabular-nums min-w-[40px] justify-center">
      <template v-if="hasScore">
        <span :class="getScoreClass('A')">{{ match.scoreA }}</span>
        <span class="text-header-300">-</span>
        <span :class="getScoreClass('B')">{{ match.scoreB }}</span>
      </template>
      <span v-else class="text-header-300">vs</span>
    </div>

    <!-- Team B -->
    <span
      class="flex-1 truncate transition-colors duration-100"
      :class="getTeamTextClass('B')"
      :title="match.equipeB"
      @mouseenter="emit('hoverTeam', match.equipeB)"
      @mouseleave="emit('hoverTeam', null)"
    >
      {{ match.equipeB }}
    </span>
  </div>
</template>
