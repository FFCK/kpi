<script setup lang="ts">
import type { SchemaPhase } from '~/types/schema'

const props = defineProps<{
  phase: SchemaPhase
  hoveredTeam: string | null
  qualifies: number
  elimines: number
}>()

const emit = defineEmits<{
  hoverTeam: [team: string | null]
}>()

const { t } = useI18n()

const hasRanking = computed(() => props.phase.ranking && props.phase.ranking.length > 0)
const hasPoolTeams = computed(() => props.phase.poolTeams && props.phase.poolTeams.length > 0)
const emptyRows = computed(() => {
  if (hasRanking.value || hasPoolTeams.value) return 0
  return props.phase.nbequipes
})

const isHighlighted = (team: string) => {
  return props.hoveredTeam !== null && team === props.hoveredTeam
}
</script>

<template>
  <div class="p-1">
    <!-- Ranking table -->
    <div v-if="hasRanking" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-1 py-1 text-left text-xs">{{ t('schema.table.rank') }}</th>
            <th class="px-1 py-1 text-left text-xs">{{ t('schema.table.teams') }}</th>
            <th class="px-1 py-1 text-center text-xs">{{ t('schema.table.pts') }}</th>
            <th class="px-1 py-1 text-center text-xs">{{ t('schema.table.played') }}</th>
            <th class="px-1 py-1 text-center text-xs">+/-</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(team, idx) in phase.ranking"
            :key="team.id"
            class="border-t transition-colors duration-100"
            :class="{
              'bg-yellow-100 text-black': isHighlighted(team.libelle),
              'bg-gray-200 text-black': !isHighlighted(team.libelle)
            }"
            @mouseenter="emit('hoverTeam', team.libelle)"
            @mouseleave="emit('hoverTeam', null)"
          >
            <td class="px-1 py-1 text-xs text-gray-500">{{ team.clt }}</td>
            <td class="px-1 py-1 text-xs font-medium truncate max-w-30" :title="team.libelle">{{ team.libelle }}</td>
            <td class="px-1 py-1 text-center text-xs font-bold">{{ team.pts }}</td>
            <td class="px-1 py-1 text-center text-xs">{{ team.j }}</td>
            <td class="px-1 py-1 text-center text-xs">{{ team.diff }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pool teams (no ranking yet) -->
    <div v-else-if="hasPoolTeams" class="space-y-0.5">
      <div
        v-for="team in phase.poolTeams"
        :key="team.id"
        class="py-0.5 px-1 rounded transition-colors duration-100 text-xs"
        :class="{ 'bg-yellow-100': isHighlighted(team.libelle) }"
        @mouseenter="emit('hoverTeam', team.libelle)"
        @mouseleave="emit('hoverTeam', null)"
      >
        <span class="font-medium">{{ team.libelle }}</span>
      </div>
    </div>

    <!-- Empty rows -->
    <div v-else>
      <div v-for="n in emptyRows" :key="n" class="py-0.5 px-1 text-gray-300 border-b border-gray-100 text-xs">
        —
      </div>
    </div>
  </div>
</template>
