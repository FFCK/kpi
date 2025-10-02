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
          <!-- First team (winner if there's a winner, otherwise Team A) -->
          <div class="flex items-center gap-1">
            <span
              :class="teamBlockClass(game, getFirstTeam(game))"
              class="px-2 py-1 rounded text-xs flex-1"
              v-html="teamNameResize(getFirstTeamLabel(game))"
            />
            <div
              v-if="getFirstTeamScore(game) !== undefined && getFirstTeamScore(game) !== ''"
              :class="[teamBlockClass(game, getFirstTeam(game)), 'lcd text-xs px-2 py-1 rounded text-center border-0 min-w-8']"
            >
              {{ getFirstTeamScore(game) }}
            </div>
          </div>

          <!-- Second team (loser if there's a winner, otherwise Team B) -->
          <div class="flex items-center gap-1">
            <span
              :class="teamBlockClass(game, getSecondTeam(game))"
              class="px-2 py-1 rounded text-xs flex-1"
              v-html="teamNameResize(getSecondTeamLabel(game))"
            />
            <div
              v-if="getSecondTeamScore(game) !== undefined && getSecondTeamScore(game) !== ''"
              :class="[teamBlockClass(game, getSecondTeam(game)), 'lcd text-xs px-2 py-1 rounded text-center border-0 min-w-8']"
            >
              {{ getSecondTeamScore(game) }}
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

const getFirstTeam = (game) => {
  // If Team A is winner, show A first, otherwise show B first (if B is winner) or A by default
  if (isWinner(game, 'A')) return 'A'
  if (isWinner(game, 'B')) return 'B'
  return 'A'
}

const getSecondTeam = (game) => {
  return getFirstTeam(game) === 'A' ? 'B' : 'A'
}

const getFirstTeamLabel = (game) => {
  return getFirstTeam(game) === 'A' ? (game.t_a_label || 'Team A') : (game.t_b_label || 'Team B')
}

const getSecondTeamLabel = (game) => {
  return getSecondTeam(game) === 'A' ? (game.t_a_label || 'Team A') : (game.t_b_label || 'Team B')
}

const getFirstTeamScore = (game) => {
  return getFirstTeam(game) === 'A' ? game.g_score_a : game.g_score_b
}

const getSecondTeamScore = (game) => {
  return getSecondTeam(game) === 'A' ? game.g_score_a : game.g_score_b
}

const teamBlockClass = (game, team) => {
  const winner = isWinner(game, team)
  const highlighted = team === 'A' ? game.t_a_highlighted : game.t_b_highlighted

  return {
    // Background colors
    'bg-yellow-400': highlighted, // Yellow for all highlighted teams
    'bg-gray-800': winner && !highlighted, // Dark gray for winners not highlighted
    'bg-gray-200': !winner && !highlighted, // Light gray for others
    // Text colors
    'text-black': highlighted, // Black text for all highlighted teams
    'text-white': winner && !highlighted, // White text for winners not highlighted
    'font-bold': highlighted // Bold for highlighted teams
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