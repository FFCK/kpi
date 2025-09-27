<template>
  <div>
    <div class="mt-1">
      <table class="table-auto w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <caption class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800">
          {{ filteredGamesCount }}/{{ gamesCount }} {{ t('Games.games') }}
        </caption>
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3 hidden lg:table-cell">#</th>
            <th scope="col" class="px-6 py-3 hidden lg:table-cell">{{ t('Games.Cat') }} | {{ t('Games.Group') }}</th>
            <th scope="col" class="px-6 py-3 hidden lg:table-cell text-center">{{ t('Games.Time') }} / {{ t('Games.Pitch') }}</th>
            <th scope="col" class="px-6 py-3 text-right">{{ t('Games.Team') }} A</th>
            <th scope="col" class="px-6 py-3 text-center">{{ t('Games.Score') }}</th>
            <th scope="col" class="px-6 py-3">{{ t('Games.Team') }} B</th>
            <th v-if="showRefs" scope="col" class="px-6 py-3 hidden lg:table-cell">{{ t('Games.Referee') }}</th>
          </tr>
        </thead>
        <tbody v-for="game_group in games" :key="game_group.goupDate">
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <th colspan="100%" scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
              {{ d(new Date(game_group.goupDate), 'short') }}
            </th>
          </tr>
          <tr v-for="game in game_group.filtered" :key="game.g_id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td class="px-6 py-4 hidden lg:table-cell"><i>#{{ game.g_number }}</i></td>
            <td class="px-6 py-4 hidden lg:table-cell">{{ game.c_code }}<span v-if="game.d_phase"> | {{ game.d_phase }}</span></td>
            <td class="px-6 py-4 hidden lg:table-cell text-center">{{ game.g_time }} / {{ game.g_pitch }}</td>
            <td class="px-6 py-4 text-right">
              <div class="lg:hidden">{{ game.c_code }}<span v-if="game.d_phase"> | {{ game.d_phase }}</span></div>
              <div class="text-nowrap">
                <span :class="teamClass(game, 'A')" v-html="teamNameResize(showCode(game.t_a_label))" />
                <img v-if="showFlags" :src="`${baseUrl}/img/${game.t_a_logo}`" class="h-4 inline-block ml-1" alt="" />
              </div>
              <div v-if="showRefs" class="lg:hidden text-xs" v-html="showCode(game.r_1)" />
            </td>
            <td class="px-6 py-4 text-center">
              <div class="lg:hidden text-xs"><i>#{{ game.g_number }}</i></div>
              <div class="text-nowrap">
                <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'A')">{{ game.g_score_a }}</span>
                <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'B')">{{ game.g_score_b }}</span>
              </div>
              <div :class="statusClass(game)">{{ game.g_status !== 'ON' ? t('Games.Status.' + game.g_status) : t('Games.Period.' + game.g_period) }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="lg:hidden text-right">{{ game.g_time }} / {{ game.g_pitch }}</div>
              <div class="text-nowrap">
                <img v-if="showFlags" :src="`${baseUrl}/img/${game.t_b_logo}`" class="h-4 inline-block mr-1" alt="" />
                <span :class="teamClass(game, 'B')" v-html="teamNameResize(showCode(game.t_b_label))" />
              </div>
              <div v-if="showRefs" class="lg:hidden text-right text-xs" v-html="showCode(game.r_2)" />
            </td>
            <td v-if="showRefs" class="px-6 py-4 hidden lg:table-cell">
              <div v-html="showCode(game.r_1)" />
              <div v-html="showCode(game.r_2)" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { useGameDisplay } from '~/composables/useGameDisplay'
const { showCode, teamNameResize } = useGameDisplay()
const { t, d } = useI18n()

const props = defineProps({
  games: { type: Array, default: () => [] },
  showRefs: { type: Boolean, default: true },
  showFlags: { type: Boolean, default: true },
  filteredGamesCount: { type: Number, default: 0 },
  gamesCount: { type: Number, default: 0 }
})

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.apiBaseUrl.replace('/api', '')

const teamClass = (game, team) => {
  const isWinner = team === 'A'
    ? (game.g_score_b === 'F' || parseInt(game.g_score_a) > parseInt(game.g_score_b))
    : (game.g_score_a === 'F' || parseInt(game.g_score_b) > parseInt(game.g_score_a))

  return {
    'font-bold': game.g_status === 'END' && game.g_validation === 'O' && isWinner,
  }
}

const scoreClass = (game, team) => {
  const isWinner = team === 'A'
    ? (game.g_score_b === 'F' || parseInt(game.g_score_a) > parseInt(game.g_score_b))
    : (game.g_score_a === 'F' || parseInt(game.g_score_b) > parseInt(game.g_score_a))

  return {
    'px-2 py-1 rounded': true,
    'bg-gray-200 dark:bg-gray-700': game.g_status !== 'END' || game.g_validation !== 'O' || !isWinner,
    'bg-green-500 text-white': game.g_status === 'END' && game.g_validation === 'O' && isWinner,
    'text-red-500': game.g_validation !== 'O'
  }
}

const statusClass = (game) => {
  return {
    'text-xs px-2 py-0.5 rounded-full': true,
    'bg-gray-200 text-gray-800': game.g_status === 'ATT',
    'bg-blue-200 text-blue-800': game.g_status === 'ON',
    'bg-green-200 text-green-800': game.g_status === 'END'
  }
}
</script>
