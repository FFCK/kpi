<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #left>
        <div class="flex items-center gap-1 sm:gap-2">
          <label class="hidden sm:inline text-sm font-medium text-gray-700">{{ t('Teams.SelectTeam') }}:</label>
          <select
            v-model="selectedTeamModel"
            @change="onTeamChange"
            class="px-2 sm:px-3 py-1 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-40 sm:min-w-64 cursor-pointer"
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
          v-if="showRefreshButton && visibleButton"
          @click="handleRefresh"
          class="p-2 rounded-md hover:bg-gray-100 cursor-pointer"
        >
          <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
        </button>
      </template>
    </AppSecondaryNav>

    <div v-if="!selectedTeam || isLoading" class="p-4 text-center text-gray-500">
      <p v-if="isLoading">Chargement des données de l'équipe...</p>
      <p v-else>{{ t('Teams.PleaseSelectOne') }}</p>
    </div>

    <div v-else-if="!teamExists" class="p-4 text-center text-red-500">
      <p>L'équipe "{{ selectedTeam }}" n'existe pas dans cet événement.</p>
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

      <div class="py-2">

      <!-- Upcoming Matches -->
      <div v-if="formattedUpcomingMatches.length > 0" class="mb-6">
        <h3 class="px-4 text-xl font-semibold text-gray-700 mb-3">{{ t('Team.UpcomingMatches') }} ({{ upcomingMatches.length }})</h3>
        <GameList :games="formattedUpcomingMatches" :show-refs="showRefs" :show-flags="showFlags" :show-count="false" />
      </div>

      <!-- Finished Matches -->
      <div v-if="formattedFinishedMatches.length > 0" class="mb-6">
        <h3 class="px-4 text-xl font-semibold text-gray-700 mb-3">{{ t('Team.FinishedMatches') }} ({{ finishedMatches.length }})</h3>
        <GameList :games="formattedFinishedMatches" :show-refs="showRefs" :show-flags="showFlags" :show-count="false" />
      </div>

      <!-- Tournament Rounds (Rankings & Eliminations) -->
      <div v-if="tournamentRounds.length > 0" class="mb-6">
        <h3 class="px-4 text-xl font-semibold text-gray-700 mb-3">{{ t('Team.TournamentRounds') }}</h3>
        <div :class="containerClasses" class="px-2">
          <div v-for="round in tournamentRounds" :key="round.id" class="w-80 border rounded-lg shadow-sm flex flex-col">
            <div class="bg-gray-800 text-white px-3 py-2 rounded-t-lg text-sm">
              {{ round.category }}<span v-if="round.phase"> - {{ round.phase }}</span>
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
                        <TeamName
                          :team-label="team.t_label || `Team ${index + 1}`"
                          :team-id="team.t_id"
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
                      :team-id="team.id"
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
        <h3 class="px-4 text-xl font-semibold text-gray-700 mb-3">{{ t('Team.FinalRanking') }}</h3>
        <div v-for="ranking in finalRankings" :key="ranking.id" class="mb-4" :class="ranking.type === 'CP' ? 'flex justify-center' : ''">
          <div :class="ranking.type === 'CP' ? 'w-full max-w-2xl px-2' : 'px-2'">
            <div class="bg-gray-800 text-white rounded-t-lg px-3 py-2">
              {{ ranking.category }}
              <span v-if="ranking.phase" class="text-sm font-normal ml-2">- {{ ranking.phase }}</span>
            </div>
            <div class="bg-white rounded-b-lg p-4 shadow-sm border">
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-2 md:px-6 py-3 text-center w-20">{{ t('Charts.Ranking') }}</th>
                    <th class="px-2 md:px-6 py-3 text-left">{{ t('Charts.Team') }}</th>
                    <template v-if="ranking.type !== 'CP'">
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.Pts') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.Pld') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.W') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.D') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.L') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.GF') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.GA') }}</th>
                      <th class="px-2 md:px-3 py-2 text-center">{{ t('Charts.GD') }}</th>
                    </template>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(team, index) in ranking.teams" :key="index" class="border-t">
                    <td class="px-2 md:px-6 py-3 text-center font-bold text-lg">{{ ranking.type === 'CP' ? team.t_clt_cp || (index + 1) : team.t_clt || '' }}</td>
                    <td class="px-2 md:px-6 py-3">
                      <div class="flex items-center">
                        <img v-if="showFlags && team.t_logo" :src="getTeamLogo(team.t_logo)" class="h-6 w-6 mr-3" alt="" />
                        <TeamName
                          :team-label="team.t_label"
                          :team-id="team.t_id"
                          :is-winner="false"
                          :is-highlighted="team.t_label === selectedTeam"
                        />
                      </div>
                    </td>
                    <template v-if="ranking.type !== 'CP'">
                      <td class="px-2 md:px-3 py-2 text-center font-bold">{{ team.t_pts || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_pld || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_won || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_draw || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_lost || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_plus || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_minus || 0 }}</td>
                      <td class="px-2 md:px-3 py-2 text-center">{{ team.t_diff || 0 }}</td>
                    </template>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          </div>
        </div>
      </div>

      <!-- Team Statistics -->
      <div v-if="teamStats && teamStats.length > 0" class="mb-6 flex justify-center">
        <div class="w-full max-w-3xl px-2">
          <h3 class="px-2 text-xl font-semibold text-gray-700 mb-3">{{ t('Stats.Title') }}</h3>
          <div class="overflow-x-auto bg-white rounded-lg shadow-sm border">
            <table class="w-full">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="py-2 px-3 border-b text-left">{{ t('Stats.Player') }}</th>
                  <th class="py-2 px-2 border-b text-center">{{ t('Stats.Goals') }}</th>
                  <th class="py-2 px-2 border-b text-center">
                    <div class="inline-block bg-green-500 w-6 h-8 transform -rotate-12 rounded-sm"></div>
                  </th>
                  <th class="py-2 px-2 border-b text-center">
                    <div class="inline-block bg-yellow-400 w-6 h-8 transform -rotate-12 rounded-sm"></div>
                  </th>
                  <th class="py-2 px-2 border-b text-center">
                    <div class="inline-block bg-red-500 w-6 h-8 transform -rotate-12 rounded-sm"></div>
                  </th>
                  <th class="py-2 px-2 border-b text-center">
                    <div class="flex items-center justify-center bg-red-500 w-6 h-8 transform -rotate-12 rounded-sm text-white font-bold text-xs">E</div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="player in teamStats" :key="player.licence" class="hover:bg-gray-100 border-b">
                  <td class="py-2 px-3">
                    <div class="font-medium flex items-center">
                      <span v-if="player.captain !== 'E'" class="text-sm text-gray-500 mr-2">#{{ player.number }}</span>
                      <span class="text-xs md:text-sm lg:text-base ml-1">{{ player.firstname }} {{ player.name }}</span>
                      <span v-if="player.captain === 'C'" class="ml-2 bg-black text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-sm">C</span>
                      <span v-if="player.captain === 'E'" class="ml-2 text-xs text-gray-500">({{ t('Stats.Coach') }})</span>
                    </div>
                  </td>
                  <td class="py-2 px-2 text-center">{{ player.goals > 0 ? player.goals : '' }}</td>
                  <td class="py-2 px-2 text-center">{{ player.green_cards > 0 ? player.green_cards : '' }}</td>
                  <td class="py-2 px-2 text-center">{{ player.captain !== 'E' && player.yellow_cards > 0 ? player.yellow_cards : '' }}</td>
                  <td class="py-2 px-2 text-center">{{ player.red_cards > 0 ? player.red_cards : '' }}</td>
                  <td class="py-2 px-2 text-center">{{ player.exclusions > 0 ? player.exclusions : '' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No data message -->
      <div v-if="upcomingMatches.length === 0 && finishedMatches.length === 0 && tournamentRounds.length === 0 && finalRankings.length === 0" class="text-center text-gray-500 py-8 px-4">
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
import { useApi } from '~/composables/useApi'
import GameList from '~/components/GameList.vue'
import TeamName from '~/components/TeamName.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const preferenceStore = usePreferenceStore()

const {
  games,
  showRefs,
  showFlags,
  loadGames,
  getFav: getGamesFav
} = useGames()

const {
  chartData,
  loadCharts,
  getFav: getChartsFav
} = useCharts()

const selectedTeam = ref('')
const selectedTeamModel = ref('')
const showRefreshButton = ref(false)
const visibleButton = ref(true)
const isLoading = ref(false)
const stats = ref(null)

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl
const { getApi } = useApi()

// Page-specific SEO (will be updated when team is selected)
const pageTitle = computed(() =>
  selectedTeam.value ? `${selectedTeam.value} - Team Page - KPI Application` : 'Team Page - KPI Application'
)
const pageDescription = computed(() =>
  selectedTeam.value
    ? `View matches, rankings, and tournament progress for ${selectedTeam.value}`
    : 'Select a team to view their matches, rankings, and tournament progress'
)

useSeoMeta({
  title: pageTitle,
  description: pageDescription,
  ogTitle: pageTitle,
  ogDescription: pageDescription
})

// Scroll to top functionality
const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Filter and sort team statistics
const teamStats = computed(() => {
  if (!stats.value) return []

  return stats.value
    // Exclude non-players (A = referees, X = inactive)
    .filter(player => player.captain !== 'A' && player.captain !== 'X')
    // Sort: players first by number, then coaches by number
    .sort((a, b) => {
      const aIsCoach = a.captain === 'E'
      const bIsCoach = b.captain === 'E'

      // If one is coach and other is player, player comes first
      if (aIsCoach && !bIsCoach) return 1
      if (!aIsCoach && bIsCoach) return -1

      // Both same type, sort by number
      return (a.number || 0) - (b.number || 0)
    })
})

// Fetch team statistics
const fetchStats = async (teamId) => {
  if (!teamId) {
    stats.value = null
    return
  }

  try {
    const eventId = preferenceStore.preferences.lastEvent?.id
    if (!eventId) {
      stats.value = null
      return
    }

    const response = await getApi(`/team-stats/${teamId}/${eventId}`)
    if (response.ok) {
      stats.value = await response.json()
    } else {
      stats.value = null
    }
  } catch (e) {
    console.error('Error fetching stats:', e)
    stats.value = null
  }
}

// Create team ID to name mapping
const teamIdToName = computed(() => {
  const mapping = new Map()

  // Get teams from games
  if (games.value) {
    games.value.forEach(game => {
      if (game.t_a_id && game.t_a_label && game.t_a_label[0] !== '¤') {
        mapping.set(game.t_a_id.toString(), game.t_a_label)
      }
      if (game.t_b_id && game.t_b_label && game.t_b_label[0] !== '¤') {
        mapping.set(game.t_b_id.toString(), game.t_b_label)
      }
    })
  }

  // Get teams from charts
  if (chartData.value) {
    chartData.value.forEach(category => {
      if (category.ranking) {
        category.ranking.forEach(team => {
          if (team.t_id && team.t_label) {
            mapping.set(team.t_id.toString(), team.t_label)
          }
        })
      }
      if (category.rounds) {
        Object.values(category.rounds).forEach(round => {
          if (round.phases) {
            Object.values(round.phases).forEach(phase => {
              if (phase.teams) {
                phase.teams.forEach(team => {
                  if (team.t_id && team.t_label) {
                    mapping.set(team.t_id.toString(), team.t_label)
                  }
                })
              }
            })
          }
        })
      }
    })
  }

  return mapping
})

// Create team name to ID mapping
const teamNameToId = computed(() => {
  const mapping = new Map()
  teamIdToName.value.forEach((name, id) => {
    mapping.set(name, id)
  })
  return mapping
})

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

const teamExists = computed(() => {
  // We can only make a definitive check once loading is complete.
  if (isLoading.value) {
    return true // Assume it exists while loading to prevent premature errors
  }
  // After loading, if the list of available teams is not empty, check for inclusion.
  if (availableTeams.value.length > 0) {
    return availableTeams.value.includes(selectedTeam.value)
  }
  // If there are no teams after loading, it can't exist.
  return false
})

// Watch for team parameter in URL
watch(() => route.params.team, async (newTeam) => {
  if (newTeam) {
    const decodedParam = decodeURIComponent(newTeam)

    // Check if the parameter is a team ID (numeric) or team name
    let teamName = decodedParam
    let teamId = decodedParam
    if (/^\d+$/.test(decodedParam)) {
      // It's an ID, convert to name
      teamName = teamIdToName.value.get(decodedParam) || decodedParam
    } else {
      // It's a name, try to get the ID
      teamId = teamNameToId.value.get(teamName)
    }

    selectedTeam.value = teamName
    selectedTeamModel.value = teamName

    // Fetch stats for the team
    await fetchStats(teamId)

    // Save to preferences (client-side only)
    if (import.meta.client) {
      await preferenceStore.putItem('last_team', teamName)
    }
  }
}, { immediate: true })

// Handle team selection change
const onTeamChange = () => {
  if (selectedTeamModel.value) {
    const teamId = teamNameToId.value.get(selectedTeamModel.value)
    if (teamId) {
      router.push({ path: `/team/${teamId}` })
    } else {
      // Fallback to using team name if ID not found
      const encodedTeam = encodeURIComponent(selectedTeamModel.value)
      router.push({ path: `/team/${encodedTeam}` })
    }
  }
}

// Helper function to check if team is refereeing
const isTeamReferee = (refereeField, teamName) => {
  if (!refereeField || !teamName) return false
  // Check if team name is exactly the referee (alone)
  if (refereeField === teamName) return true
  // Check if team name is in parentheses (secondary referee)
  if (refereeField.includes(`(${teamName})`)) return true
  return false
}

// Computed properties for matches - formatted like in games page
const upcomingMatches = computed(() => {
  if (!selectedTeam.value || !games.value) return []

  return games.value
    .filter(game =>
      (game.g_status === 'ATT' || game.g_status === 'ON') &&
      (game.t_a_label === selectedTeam.value ||
       game.t_b_label === selectedTeam.value ||
       isTeamReferee(game.r_1, selectedTeam.value) ||
       isTeamReferee(game.r_2, selectedTeam.value))
    )
    .map(game => ({
      ...game,
      t_a_highlighted: game.t_a_label === selectedTeam.value,
      t_b_highlighted: game.t_b_label === selectedTeam.value,
      r_1_highlighted: isTeamReferee(game.r_1, selectedTeam.value),
      r_2_highlighted: isTeamReferee(game.r_2, selectedTeam.value)
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
      (game.t_a_label === selectedTeam.value ||
       game.t_b_label === selectedTeam.value ||
       isTeamReferee(game.r_1, selectedTeam.value) ||
       isTeamReferee(game.r_2, selectedTeam.value))
    )
    .map(game => ({
      ...game,
      t_a_highlighted: game.t_a_label === selectedTeam.value,
      t_b_highlighted: game.t_b_label === selectedTeam.value,
      r_1_highlighted: isTeamReferee(game.r_1, selectedTeam.value),
      r_2_highlighted: isTeamReferee(game.r_2, selectedTeam.value)
    }))
    .sort((a, b) => {
      const dateA = new Date(`${a.g_date} ${a.g_time}`)
      const dateB = new Date(`${b.g_date} ${b.g_time}`)
      return dateA - dateB
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

  return {
    'bg-gray-200': true // Default for non-winner, non-highlighted
  }
}

const getTeamLogo = (logo) => {
  return `${baseUrl}/img/${logo}`
}

const getOrderedTeams = (game) => {
  const teamA = { label: game.t_a_label, id: game.t_a_id, score: game.g_score_a, highlighted: game.t_a_highlighted, side: 'A' }
  const teamB = { label: game.t_b_label, id: game.t_b_id, score: game.g_score_b, highlighted: game.t_b_highlighted, side: 'B' }

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

const loadData = async (force = false) => {
  isLoading.value = true
  await Promise.all([
    loadGames(force),
    loadCharts(force)
  ])
  isLoading.value = false
}

const handleRefresh = () => {
  visibleButton.value = false
  loadData(true)
  setTimeout(() => {
    visibleButton.value = true
  }, 5000)
}

onMounted(async () => {
  await Promise.all([
    preferenceStore.fetchItems(),
    getGamesFav(),
    getChartsFav(),
    loadData()
  ])

  // If navigating to the base /team route without a parameter
  if (!route.params.team) {
    // Check for a last-viewed team and redirect if it exists
    if (preferenceStore.preferences.last_team) {
      const teamId = teamNameToId.value.get(preferenceStore.preferences.last_team)
      if (teamId) {
        router.push({ path: `/team/${teamId}` })
      } else {
        // Fallback to using team name if ID not found
        const encodedTeam = encodeURIComponent(preferenceStore.preferences.last_team)
        router.push({ path: `/team/${encodedTeam}` })
      }
    }
    // If no last_team, do nothing and let the page render its empty state
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
