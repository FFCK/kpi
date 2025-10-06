<template>
  <div class="mt-1">
    <div class="hidden md:block">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <caption v-if="showCount" class="p-5 text-lg font-semibold text-left text-gray-900 bg-white dark:text-white dark:bg-gray-800">
          {{ filteredGamesCount }}/{{ gamesCount }} {{ t('Games.games') }}
        </caption>
        <thead class="text-xs text-gray-700 uppercase bg-gray-200 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-2 py-3">#</th>
            <th scope="col" class="px-2 py-3">{{ t('Games.Cat') }} | {{ t('Games.Group') }}</th>
            <th scope="col" class="px-1 py-3 text-center">{{ t('Games.Time') }} | {{ t('Games.Pitch') }}</th>
            <th scope="col" class="px-2 py-3 text-right">{{ t('Games.Team') }} A</th>
            <th scope="col" class="px-2 py-3 text-center">{{ t('Games.Score') }}</th>
            <th scope="col" class="px-2 py-3">{{ t('Games.Team') }} B</th>
            <th v-if="showRefs" scope="col" class="px-2 py-3">{{ t('Games.Referee') }}</th>
          </tr>
        </thead>
        <tbody v-for="(game_group, group_index) in games" :key="game_group.goupDate">
          <tr class="bg-gray-800 text-white">
            <th :colspan="showRefs ? 7 : 6" scope="row" class="px-2 py-2 font-medium whitespace-nowrap">
              <NuxtTime :datetime="game_group.goupDate" day="numeric" month="long" year="numeric" :locale="locale" />
            </th>
          </tr>
          <tr v-for="(game, game_index) in game_group.filtered" :key="game.g_id" :class="(group_index + game_index) % 2 === 0 ? 'bg-gray-100' : 'bg-white'">
            <td class="px-2 py-2 text-gray-900"><i>#{{ game.g_number }}</i></td>
            <td class="px-2 py-2 text-gray-900">{{ game.c_label }}<span v-if="game.d_phase"> | {{ game.d_phase }}</span></td>
            <td class="px-1 py-2 text-center text-gray-900">
              <span class="bg-gray-200 px-2 py-1 rounded">{{ game.g_time }}</span>
              <span class="bg-gray-500 text-white px-2 py-1 rounded ml-1">{{ game.g_pitch }}</span>
            </td>
            <td class="px-2 py-2 text-right">
              <div class="inline-block">
                <TeamName
                  :team-label="showCode(game.t_a_label)"
                  :team-id="game.t_a_id"
                  :is-winner="isWinner(game, 'A')"
                  :is-highlighted="game.t_a_highlighted"
                />
                <img v-if="showFlags" :src="`${baseUrl}/img/${game.t_a_logo}`" class="h-8 inline-block ml-1" alt="" />
              </div>
            </td>
            <td class="px-2 py-2 text-center">
              <div class="flex flex-col items-center">
                <div class="text-nowrap inline-block">
                  <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'A')">{{ game.g_score_a }}</span>
                  <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'B')">{{ game.g_score_b }}</span>
                </div>
                <div :class="statusClass(game)">{{ game.g_status !== 'ON' ? t('Games.Status.' + game.g_status) : t('Games.Period.' + game.g_period) }}</div>
              </div>
            </td>
            <td class="px-2 py-2">
              <div class="inline-block">
                <img v-if="showFlags" :src="`${baseUrl}/img/${game.t_b_logo}`" class="h-8 inline-block mr-1" alt="" />
                <TeamName
                  :team-label="showCode(game.t_b_label)"
                  :team-id="game.t_b_id"
                  :is-winner="isWinner(game, 'B')"
                  :is-highlighted="game.t_b_highlighted"
                />
              </div>
            </td>
            <td v-if="showRefs" class="px-2 py-2 text-xs text-gray-900">
              <div v-html="highlightReferee(game.r_1, game.r_1_highlighted)" />
              <div v-html="highlightReferee(game.r_2, game.r_2_highlighted)" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="md:hidden">
      <div v-for="game_group in games" :key="game_group.goupDate">
        <div class="bg-gray-800 text-white p-2"><NuxtTime :datetime="game_group.goupDate" day="numeric" month="long" year="numeric" :locale="locale" /></div>
        <div v-for="game in game_group.filtered" :key="game.g_id" class="p-2 border-b">
          <div class="grid grid-cols-[1fr_auto_1fr] gap-1 items-center">
            <div class="text-left text-xs text-gray-900 justify-self-start">
              {{ game.c_label }}<span v-if="game.d_phase"> | {{ game.d_phase }}</span>
            </div>
            <div class="text-center text-xs text-gray-900 justify-self-center">#{{ game.g_number }}</div>
            <div class="text-right text-xs text-gray-900 justify-self-end">
              <span class="bg-gray-200 px-2 py-1 rounded">{{ game.g_time }}</span>
              <span class="bg-gray-500 text-white px-2 py-1 rounded ml-1">{{ game.g_pitch }}</span>
            </div>
            <div class="text-right justify-self-end">
              <TeamName
                :team-label="showCode(game.t_a_label)"
                :team-id="game.t_a_id"
                :is-winner="isWinner(game, 'A')"
                :is-highlighted="game.t_a_highlighted"
              />
            </div>
            <div class="text-center justify-self-center">
              <div class="text-nowrap inline-block text-sm">
                <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'A')">{{ game.g_score_a }}</span>
                <span v-if="game.g_status !== 'ATT'" :class="scoreClass(game, 'B')">{{ game.g_score_b }}</span>
              </div>
            </div>
            <div class="text-left justify-self-start">
              <TeamName
                :team-label="showCode(game.t_b_label)"
                :team-id="game.t_b_id"
                :is-winner="isWinner(game, 'B')"
                :is-highlighted="game.t_b_highlighted"
              />
            </div>
            <div :class="['text-left text-xs text-gray-900 justify-self-start', { 'invisible': !showRefs }]" v-html="highlightReferee(game.r_1, game.r_1_highlighted)" />
            <div class="text-center justify-self-center">
                <div :class="statusClass(game)" class="text-xs">{{ game.g_status !== 'ON' ? t('Games.Status.' + game.g_status) : t('Games.Period.' + game.g_period) }}</div>
            </div>
            <div :class="['text-right text-xs text-gray-900 justify-self-end', { 'invisible': !showRefs }]" v-html="highlightReferee(game.r_2, game.r_2_highlighted)" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useGameDisplay } from '~/composables/useGameDisplay'
import TeamName from '~/components/TeamName.vue'

const { showCode } = useGameDisplay()
const { t, locale } = useI18n()

const props = defineProps({
  games: { type: Array, default: () => [] },
  showRefs: { type: Boolean, default: true },
  showFlags: { type: Boolean, default: true },
  filteredGamesCount: { type: Number, default: 0 },
  gamesCount: { type: Number, default: 0 },
  showCount: { type: Boolean, default: true }
})

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl

const isWinner = (game, team) => {
  if (game.g_status !== 'END' || game.g_validation !== 'O') return false
  if (team === 'A') {
    return game.g_score_b === 'F' || parseInt(game.g_score_a) > parseInt(game.g_score_b)
  } else {
    return game.g_score_a === 'F' || parseInt(game.g_score_b) > parseInt(game.g_score_a)
  }
}

const scoreClass = (game, team) => {
  return {
    'lcd text-lg px-2 py-1 border border-gray-400 rounded': true,
    'bg-gray-800 text-white': isWinner(game, team),
    'bg-gray-200 text-black': !isWinner(game, team),
  }
}

const statusClass = (game) => {
  return {
    'inline-block text-xs px-1 py-0 rounded-sm': true,
    'bg-green-500 text-white': game.g_status === 'END',
    'bg-blue-500 text-white': game.g_status === 'ON',
    'bg-gray-500 text-white': game.g_status === 'ATT',
  }
}

// Function to highlight referee text if needed
const highlightReferee = (refereeText, isHighlighted) => {
  if (!refereeText || !isHighlighted) {
    return showCode(refereeText)
  }
  // Apply yellow highlight to the referee text
  return `<span class="bg-yellow-200 text-black py-1">${showCode(refereeText)}</span>`
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