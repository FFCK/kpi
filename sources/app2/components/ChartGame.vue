<template>
  <div class="p-1">
    <!-- Games laid out side by side, max 2 per row -->
    <div
      v-if="chartGames && chartGames.length > 0"
      :class="chartGames.length === 1 ? 'flex justify-center' : 'grid grid-cols-1 md:grid-cols-2 gap-3'"
    >
      <div v-for="game in chartGames" :key="game.id" class="flex items-center space-x-2">
        <!-- Game number - grayed italic, vertically centered -->
        <div class="text-xs text-gray-500 italic font-medium flex-shrink-0">
          #{{ game.g_number }}
        </div>

        <!-- Teams and scores -->
        <div class="flex-1 space-y-2">
          <!-- Team A -->
          <div class="flex items-center gap-1">
            <span
              :class="teamBlockClass(game, 'A')"
              class="px-2 py-1 rounded text-xs flex-1"
              v-html="teamNameResize(game.t_a_label || 'Team A')"
            />
            <div
              v-if="game.g_score_a !== undefined && game.g_score_a !== ''"
              :class="[teamBlockClass(game, 'A'), 'lcd text-xs px-2 py-1 rounded text-center border-0 min-w-8']"
            >
              {{ game.g_score_a }}
            </div>
          </div>

          <!-- Team B -->
          <div class="flex items-center gap-1">
            <span
              :class="teamBlockClass(game, 'B')"
              class="px-2 py-1 rounded text-xs flex-1"
              v-html="teamNameResize(game.t_b_label || 'Team B')"
            />
            <div
              v-if="game.g_score_b !== undefined && game.g_score_b !== ''"
              :class="[teamBlockClass(game, 'B'), 'lcd text-xs px-2 py-1 rounded text-center border-0 min-w-8']"
            >
              {{ game.g_score_b }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else class="text-gray-500 text-sm">
      No games available
    </div>
  </div>
</template>

<script setup>
import { useGameDisplay } from '~/composables/useGameDisplay'

const { teamNameResize } = useGameDisplay()

const props = defineProps({
  chartGames: {
    type: Array,
    default: () => []
  }
})

const isWinner = (game, team) => {
  if (game.g_status !== 'END' || game.g_validation !== 'O') return false
  if (team === 'A') {
    return game.g_score_b === 'F' || parseInt(game.g_score_a) > parseInt(game.g_score_b)
  } else {
    return game.g_score_a === 'F' || parseInt(game.g_score_b) > parseInt(game.g_score_a)
  }
}

const teamBlockClass = (game, team) => {
  const winner = isWinner(game, team)

  return {
    // Background colors like in GameList.vue
    'bg-gray-800': winner, // Dark gray for winners
    'bg-gray-200': !winner, // Light gray for others
    // Text colors
    'text-white': winner, // White text for winners
    'text-black': !winner, // Black text for others
  }
}
</script>

<style scoped>
@font-face {
  font-family: "LCD";
  src: url('~/assets/fonts/7segments.ttf');
}

.lcd {
  font-family: "LCD", Helvetica, Arial;
}
</style>