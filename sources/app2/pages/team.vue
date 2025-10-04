<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #left>
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-700">{{ t('Teams.SelectTeam') }}:</label>
          <select
            v-model="selectedTeamModel"
            @change="onTeamChange"
            class="px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-64"
          >
            <option value="">{{ t('Teams.PleaseSelectOne') }}</option>
            <option v-for="team in availableTeams" :key="team" :value="team">
              {{ team }}
            </option>
          </select>
        </div>
      </template>
      <template #right>
        <button
          v-if="showRefreshButton"
          :disabled="!visibleButton"
          @click="loadData"
          class="p-2 rounded-md hover:bg-gray-100"
        >
          <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
        </button>
      </template>
    </AppSecondaryNav>

    <div v-if="!selectedTeam" class="p-4 text-center text-gray-500">
      {{ t('Teams.PleaseSelectOne') }}
    </div>

    <div v-else>
      <!-- Team Name and Logo -->
      <div class="px-4 py-1 bg-gray-50 border-b">
        <div class="flex items-center gap-3">
          <img
            v-if="selectedTeamLogo"
            class="h-12 w-12"
            :src="getTeamLogo(selectedTeamLogo)"
            alt="Logo"
          />
          <h2 class="text-xl font-bold text-gray-800">{{ selectedTeam }}</h2>
        </div>
      </div>

      <div class="px-4 py-2">

      <!-- Upcoming Matches -->
      <div v-if="formattedUpcomingMatches.length > 0" class="mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">{{ t('Team.UpcomingMatches') }}</h3>
        <GameList :games="formattedUpcomingMatches" :show-refs="showRefs" :show-flags="showFlags" :games-count="upcomingMatchesCount" :filtered-games-count="upcomingMatchesCount" />
      </div>

      <!-- Finished Matches -->
      <div v-if="formattedFinishedMatches.length > 0" class="mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">{{ t('Team.FinishedMatches') }}</h3>
        <GameList :games="formattedFinishedMatches" :show-refs="showRefs" :show-flags="showFlags" :games-count="finishedMatchesCount" :filtered-games-count="finishedMatchesCount" />
      </div>

      <!-- Tournament Rounds (Rankings & Eliminations) -->
      <div v-if="tournamentRounds.length > 0" class="mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">{{ t('Team.TournamentRounds') }}</h3>
        <div :class="containerClasses">
          <div v-for="round in tournamentRounds" :key="round.id" class="w-80 border rounded-lg shadow-sm flex flex-col">
            <div class="bg-gray-800 text-white px-3 py-2 rounded-t-lg">
              {{ round.category }}
              <span v-if="round.phase" class="text-sm font-normal ml-2">- {{ round.phase }}</span>
            </div>

            <!-- Ranking Table -->
            <div v-if="round.type === 'ranking'" class="p-1">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-2 py-1 text-left text-xs">#</th>
                    <th class="px-2 py-1 text-left text-xs">{{ t('Charts.Team') }}</th>
                    <th class="px-2 py-1 text-center text-xs">{{ t('Charts.Pts') }}</th>
                    <th class="px-2 py-1 text-center text-xs">{{ t('Charts.Pld') }}</th>
                    <th class="px-2 py-1 text-center text-xs">+/-</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(team, index) in round.data" :key="index" class="border-t">
                    <td class="px-2 py-1 text-xs">{{ team.t_cltlv || (index + 1) }}</td>
                    <td class="px-2 py-1 text-xs">
                      <div class="flex items-center">
                        <img v-if="showFlags && team.t_logo" :src="getTeamLogo(team.t_logo)" class="h-6 w-6 mr-2" alt="" />
                        <TeamName
                          :team-label="team.t_label || `Team ${index + 1}`"
                          :is-winner="false"
                          :is-highlighted="team.t_label === selectedTeam"
                        />
                      </div>
                    </td>
                    <td class="px-2 py-1 text-center text-xs font-bold">{{ Math.floor((team.t_pts || 0) / 100) }}</td>
                    <td class="px-2 py-1 text-center text-xs">{{ team.t_pld || 0 }}</td>
                    <td class="px-2 py-1 text-center text-xs">{{ team.t_diff || 0 }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Elimination Games -->
            <div v-if="round.type === 'elimination'" class="p-2 space-y-3">
              <div v-for="game in round.data" :key="game.g_id" class="flex items-center space-x-2">
                <div class="text-xs text-gray-500 italic font-medium flex-shrink-0">
                  #{{ game.g_number }}
                </div>
                <div class="flex-1 space-y-2">
                  <div v-for="team in getOrderedTeams(game)" :key="team.label" class="flex items-center gap-1">
                    <TeamName
                      :team-label="team.label"
                      :is-winner="isWinner(game, team.side)"
                      :is-highlighted="team.highlighted"
                      class="text-xs flex-1"
                    />
                    <div
                      v-if="team.score !== undefined && team.score !== ''"
                      :class="[getGameTeamClass(game, team.side), 'lcd text-xs px-2 py-1 rounded text-center border-0 min-w-8']"
                    >
                      {{ team.score }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Final Rankings -->
      <div v-if="finalRankings.length > 0" class="mb-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">{{ t('Team.FinalRanking') }}</h3>
        <div v-for="ranking in finalRankings" :key="ranking.id" class="mb-4" :class="ranking.type === 'CP' ? 'flex justify-center' : ''">
          <div :class="ranking.type === 'CP' ? 'w-full max-w-2xl' : ''">
            <div class="bg-gray-800 text-white px-3 py-2">
              {{ ranking.category }}
              <span v-if="ranking.phase" class="text-sm font-normal ml-2">- {{ ranking.phase }}</span>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm border">
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-center w-20">{{ t('Charts.Ranking') }}</th>
                    <th class="px-6 py-3 text-left">{{ t('Charts.Team') }}</th>
                    <template v-if="ranking.type !== 'CP'">
                      <th class="px-3 py-2 text-center">{{ t('Charts.Pts') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.Pld') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.W') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.D') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.L') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.GF') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.GA') }}</th>
                      <th class="px-3 py-2 text-center">{{ t('Charts.GD') }}</th>
                    </template>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(team, index) in ranking.teams" :key="index" class="border-t">
                    <td class="px-6 py-3 text-center font-bold text-lg">{{ index + 1 }}</td>
                    <td class="px-6 py-3">
                      <div class="flex items-center">
                        <img v-if="showFlags && team.t_logo" :src="getTeamLogo(team.t_logo)" class="h-6 w-6 mr-3" alt="" />
                        <TeamName
                          :team-label="team.t_label"
                          :is-winner="false"
                          :is-highlighted="team.t_label === selectedTeam"
                        />
                      </div>
                    </td>
                    <template v-if="ranking.type !== 'CP'">
                      <td class="px-3 py-2 text-center font-bold">{{ team.pts || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.pld || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.w || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.d || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.l || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.gf || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.ga || 0 }}</td>
                      <td class="px-3 py-2 text-center">{{ team.gd || 0 }}</td>
                    </template>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          </div>
        </div>
      </div>

      <!-- No data message -->
      <div v-if="upcomingMatchesCount === 0 && finishedMatchesCount === 0 && tournamentRounds.length === 0 && finalRankings.length === 0" class="text-center text-gray-500 py-8">
        {{ t('Team.NoData') }}
      </div>
      </div>
    </div>

    <button @click="scrollToTop" class="fixed bottom-8 right-4 bg-gray-800 hover:bg-gray-700 text-white font-bold p-3 rounded-full">
      <UIcon name="i-heroicons-arrow-up" class="h-6 w-6" />
    </button>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGames } from '~/composables/useGames'
import { useCharts } from '~/composables/useCharts'
import { usePreferenceStore } from '~/stores/preferenceStore'
import GameList from '~/components/GameList.vue'
import TeamName from '~/components/TeamName.vue'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const preferenceStore = usePreferenceStore()

const {
  games,
  showRefs,
  showFlags,
  visibleButton: gamesVisibleButton,
  loadGames,
  getFav: getGamesFav
} = useGames()

const {
  chartData,
  visibleButton: chartsVisibleButton,
  loadCharts,
  getFav: getChartsFav
} = useCharts()

const selectedTeam = ref('')
const selectedTeamModel = ref('')
const showRefreshButton = ref(false)

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl

const visibleButton = computed(() => gamesVisibleButton.value && chartsVisibleButton.value)

// Scroll to top functionality
const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Get available teams from games and charts
const availableTeams = computed(() => {
  const teamsSet = new Set()

  // Get teams from games
  if (games.value) {
    games.value.forEach(game => {
      if (game.t_a_label && game.t_a_label[0] !== '¤') {
        teamsSet.add(game.t_a_label)
      }
      if (game.t_b_label && game.t_b_label[0] !== '¤') {
        teamsSet.add(game.t_b_label)
      }
    })
  }

  // Get teams from charts
  if (chartData.value) {
    chartData.value.forEach(category => {
      if (category.ranking) {
        category.ranking.forEach(team => {
          if (team.t_label) {
            teamsSet.add(team.t_label)
          }
        })
      }
      if (category.rounds) {
        Object.values(category.rounds).forEach(round => {
          if (round.phases) {
            Object.values(round.phases).forEach(phase => {
              if (phase.teams) {
                phase.teams.forEach(team => {
                  if (team.t_label) {
                    teamsSet.add(team.t_label)
                  }
                })
              }
            })
          }
        })
      }
    })
  }

  return Array.from(teamsSet).sort()
})

// Watch for team parameter in URL
watch(() => route.query.team, async (newTeam) => {
  if (newTeam) {
    selectedTeam.value = newTeam
    selectedTeamModel.value = newTeam
    // Save to preferences
    await preferenceStore.putItem('last_team', newTeam)
  }
}, { immediate: true })

// Handle team selection change
const onTeamChange = () => {
  if (selectedTeamModel.value) {
    router.push({ path: '/team', query: { team: selectedTeamModel.value } })
  }
}

// Computed properties for matches - formatted like in games page
const upcomingMatches = computed(() => {
  if (!selectedTeam.value || !games.value) return []

  return games.value
    .filter(game =>
      game.g_status === 'ATT' &&
      (game.t_a_label === selectedTeam.value || game.t_b_label === selectedTeam.value)
    )
    .map(game => ({
      ...game,
      t_a_highlighted: game.t_a_label === selectedTeam.value,
      t_b_highlighted: game.t_b_label === selectedTeam.value
    }))
    .sort((a, b) => {
      const dateA = new Date(`${a.g_date} ${a.g_time}`)
      const dateB = new Date(`${b.g_date} ${b.g_time}`)
      return dateA - dateB
    })
})

const finishedMatches = computed(() => {
  if (!selectedTeam.value || !games.value) return []

  return games.value
    .filter(game =>
      game.g_status === 'END' &&
      (game.t_a_label === selectedTeam.value || game.t_b_label === selectedTeam.value)
    )
    .map(game => ({
      ...game,
      t_a_highlighted: game.t_a_label === selectedTeam.value,
      t_b_highlighted: game.t_b_label === selectedTeam.value
    }))
    .sort((a, b) => {
      const dateA = new Date(`${a.g_date} ${a.g_time}`)
      const dateB = new Date(`${b.g_date} ${b.g_time}`)
      return dateB - dateA
    })
})

// Format matches like in games page (grouped by date)
const formattedUpcomingMatches = computed(() => {
  const dates = [...new Set(upcomingMatches.value.map(x => x.g_date))]
  return dates.map(date => ({
    goupDate: date,
    filtered: upcomingMatches.value.filter(game => game.g_date === date)
  }))
})

const formattedFinishedMatches = computed(() => {
  const dates = [...new Set(finishedMatches.value.map(x => x.g_date))]
  return dates.map(date => ({
    goupDate: date,
    filtered: finishedMatches.value.filter(game => game.g_date === date)
  }))
})

const upcomingMatchesCount = computed(() => upcomingMatches.value.length)
const finishedMatchesCount = computed(() => finishedMatches.value.length)

// Get logo for selected team
const selectedTeamLogo = computed(() => {
  if (!selectedTeam.value || !chartData.value) return null

  // Search in all rankings and teams for the logo
  for (const category of chartData.value) {
    // Check in final ranking
    if (category.ranking && Array.isArray(category.ranking)) {
      const team = category.ranking.find(t => t.t_label === selectedTeam.value)
      if (team && team.t_logo) return team.t_logo
    }

    // Check in rounds/phases/teams
    if (category.rounds) {
      for (const round of Object.values(category.rounds)) {
        if (round.phases) {
          for (const phase of Object.values(round.phases)) {
            if (phase.teams && Array.isArray(phase.teams)) {
              const team = phase.teams.find(t => t.t_label === selectedTeam.value)
              if (team && team.t_logo) return team.t_logo
            }
          }
        }
      }
    }
  }

  return null
})

// Final rankings
const finalRankings = computed(() => {
  if (!selectedTeam.value || !chartData.value) return []

  const rankings = []

  chartData.value.forEach(category => {
    // Check final ranking for championships
    if (category.ranking && Array.isArray(category.ranking)) {
      const teamInRanking = category.ranking.find(team => team.t_label === selectedTeam.value)
      if (teamInRanking) {
        rankings.push({
          id: `final-${category.code}`,
          category: category.libelle || category.code,
          phase: category.status === 'END' ? t('Charts.FinalRanking') : t('Charts.ProvisionalRanking'),
          teams: category.ranking,
          type: category.type,
          isFinal: category.status === 'END'
        })
      }
    }
  })

  return rankings
})

// Group rankings and elimination games by round for columnar display
const tournamentRounds = computed(() => {
  if (!selectedTeam.value || !chartData.value) return []

  const rounds = []

  chartData.value.forEach(category => {
    if (category.rounds) {
      Object.values(category.rounds).forEach(round => {
        if (round.phases) {
          Object.values(round.phases).forEach(phase => {
            // Type C for group rankings
            if (phase.type === 'C' && phase.teams && Array.isArray(phase.teams)) {
              const teamInPhase = phase.teams.find(team => team.t_label === selectedTeam.value)
              if (teamInPhase) {
                rounds.push({
                  id: `${category.code}-${phase.libelle}`,
                  type: 'ranking',
                  category: category.libelle || category.code,
                  phase: phase.libelle,
                  data: phase.teams
                })
              }
            }

            // Type E for elimination games
            if (phase.type === 'E' && phase.games && Array.isArray(phase.games)) {
              const teamGames = phase.games.filter(game =>
                game.t_a_label === selectedTeam.value || game.t_b_label === selectedTeam.value
              ).map(game => ({
                ...game,
                t_a_highlighted: game.t_a_label === selectedTeam.value,
                t_b_highlighted: game.t_b_label === selectedTeam.value
              }))

              if (teamGames.length > 0) {
                rounds.push({
                  id: `${category.code}-${phase.libelle}`,
                  type: 'elimination',
                  category: category.libelle || category.code,
                  phase: phase.libelle,
                  data: teamGames
                })
              }
            }
          })
        }
      })
    }
  })

  return rounds
})

const containerClasses = 'flex flex-wrap justify-center gap-4'

// Helper functions
const isWinner = (game, team) => {
  if (game.g_status !== 'END' || game.g_validation !== 'O') return false
  if (team === 'A') {
    return game.g_score_b === 'F' || parseInt(game.g_score_a) > parseInt(game.g_score_b)
  } else {
    return game.g_score_a === 'F' || parseInt(game.g_score_b) > parseInt(game.g_score_a)
  }
}

const getGameTeamClass = (game, team) => {
  const winner = isWinner(game, team)
  const highlighted = team === 'A' ? game.t_a_highlighted : game.t_b_highlighted

  if (winner) {
    return {
      'bg-gray-800': true,
      'text-white': true,
      'font-bold': highlighted // Keep font-bold for the selected team even if it wins
    }
  }

  if (highlighted) {
    return {
      'bg-yellow-400': true,
      'text-black': true,
      'font-bold': true
    }
  }

  return {
    'bg-gray-200': true // Default for non-winner, non-highlighted
  }
}

const getTeamLogo = (logo) => {
  return `${baseUrl}/img/${logo}`
}

const getOrderedTeams = (game) => {
  const teamA = { label: game.t_a_label, score: game.g_score_a, highlighted: game.t_a_highlighted, side: 'A' }
  const teamB = { label: game.t_b_label, score: game.g_score_b, highlighted: game.t_b_highlighted, side: 'B' }

  if (game.g_status !== 'END' || game.g_validation !== 'O') {
    return [teamA, teamB]
  }

  if (isWinner(game, 'A')) {
    return [teamA, teamB]
  } else if (isWinner(game, 'B')) {
    return [teamB, teamA]
  } else { // Handle draws or other cases
    return [teamA, teamB]
  }
}

const loadData = async () => {
  await Promise.all([
    loadGames(),
    loadCharts()
  ])
}

onMounted(async () => {
  await preferenceStore.fetchItems()
  await getGamesFav()
  await getChartsFav()
  await loadData()

  // Load last viewed team if no team in URL
  if (!route.query.team && preferenceStore.preferences.last_team) {
    router.push({ path: '/team', query: { team: preferenceStore.preferences.last_team } })
  }

  showRefreshButton.value = true
})
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
