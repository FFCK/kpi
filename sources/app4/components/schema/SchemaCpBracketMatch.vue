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

// Winner shown first, loser second
const winnerSide = computed(() => {
  if (scoreA.value === null || scoreB.value === null) return null
  if (scoreA.value > scoreB.value) return 'A'
  if (scoreB.value > scoreA.value) return 'B'
  return null
})

const firstTeam = computed(() => {
  if (winnerSide.value === 'B') return 'B'
  return 'A'
})

const secondTeam = computed(() => firstTeam.value === 'A' ? 'B' : 'A')

const getLabel = (side: 'A' | 'B') => side === 'A' ? props.match.equipeA : props.match.equipeB
const getScore = (side: 'A' | 'B') => side === 'A' ? props.match.scoreA : props.match.scoreB

const isWinner = (side: 'A' | 'B') => hasScore.value && winnerSide.value === side

const isHighlighted = (side: 'A' | 'B') => {
  const name = getLabel(side)
  return props.hoveredTeam !== null && name === props.hoveredTeam
}

const scoreBlockClass = (side: 'A' | 'B') => {
  if (isHighlighted(side)) return 'bg-warning-400 text-black font-bold'
  if (isWinner(side)) return 'bg-header-800 text-white'
  return 'bg-header-200 text-black'
}

const teamNameClass = (side: 'A' | 'B') => {
  if (isHighlighted(side)) return 'bg-warning-100'
  if (isWinner(side)) return 'bg-header-800 text-white font-semibold'
  return 'bg-header-200 text-black'
}
</script>

<template>
  <div class="flex items-center space-x-2">
    <!-- Match number -->
    <div v-if="match.numeroOrdre" class="text-xs text-header-400 italic font-medium shrink-0 w-8 text-right">
      #{{ match.numeroOrdre }}
    </div>
    <div v-else class="w-8 shrink-0" />

    <!-- Teams and scores -->
    <div class="flex-1 space-y-1">
      <!-- First team (winner if decided) -->
      <div class="flex items-center gap-1">
        <span
          class="text-xs flex-1 truncate px-1 py-0.5 rounded transition-colors duration-100 text-end"
          :class="teamNameClass(firstTeam)"
          :title="getLabel(firstTeam)"
          @mouseenter="emit('hoverTeam', getLabel(firstTeam))"
          @mouseleave="emit('hoverTeam', null)"
        >
          {{ getLabel(firstTeam) }}
        </span>
        <span
          v-if="hasScore"
          class="text-xs px-2 py-0.5 rounded text-center min-w-7 tabular-nums"
          :class="scoreBlockClass(firstTeam)"
        >
          {{ getScore(firstTeam) }}
        </span>
      </div>

      <!-- Second team (loser if decided) -->
      <div class="flex items-center gap-1">
        <span
          class="text-xs flex-1 truncate px-1 py-0.5 rounded transition-colors duration-100 text-end"
          :class="teamNameClass(secondTeam)"
          :title="getLabel(secondTeam)"
          @mouseenter="emit('hoverTeam', getLabel(secondTeam))"
          @mouseleave="emit('hoverTeam', null)"
        >
          {{ getLabel(secondTeam) }}
        </span>
        <span
          v-if="hasScore"
          class="text-xs px-2 py-0.5 rounded text-center min-w-7 tabular-nums"
          :class="scoreBlockClass(secondTeam)"
        >
          {{ getScore(secondTeam) }}
        </span>
      </div>
    </div>
  </div>
</template>
