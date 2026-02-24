<script setup lang="ts">
import type { SchemaPhase } from '~/types/schema'

defineProps<{
  phases: SchemaPhase[]
  hoveredTeam: string | null
}>()

const emit = defineEmits<{
  hoverTeam: [team: string | null]
}>()
</script>

<template>
  <div class="space-y-4">
    <SchemaChptGameday
      v-for="phase in phases"
      :key="phase.idJournee"
      :phase="phase"
      :hovered-team="hoveredTeam"
      @hover-team="emit('hoverTeam', $event)"
    />
  </div>
</template>
