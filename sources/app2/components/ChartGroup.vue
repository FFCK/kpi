<template>
  <div class="p-1">
    <!-- <h6 class="font-semibold text-gray-700 mb-3 text-center">{{ chartGroup }}</h6> -->

    <!-- Group Ranking for Phase Type C - Only Rankings -->
    <div v-if="phaseType === 'C'">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-1 py-1 text-left text-xs">#</th>
              <th class="px-1 py-1 text-left text-xs">{{ t('Charts.Team') }}</th>
              <th class="px-1 py-1 text-center text-xs">{{ t('Charts.Pts') }}</th>
              <th class="px-1 py-1 text-center text-xs">{{ t('Charts.Pld') }}</th>
              <th class="px-1 py-1 text-center text-xs">+/-</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(team, index) in displayTeams" :key="index" class="border-t">
              <template v-if="!team._empty">
                <td class="px-1 py-1 text-xs">{{ hasMatchesPlayed ? (team.t_clt || (index + 1)) : (index + 1) }}</td>
                <td class="px-1 py-1 text-xs">
                  <div class="flex items-center">
                    <TeamName
                      v-if="team.t_label"
                      :team-label="team.t_label"
                      :team-id="team.t_id"
                      :is-winner="false"
                      :is-highlighted="team.t_highlighted"
                    />
                    <span v-else class="text-gray-400 italic">—</span>
                  </div>
                </td>
                <td class="px-1 py-1 text-center text-xs font-bold">{{ hasMatchesPlayed ? Math.floor((team.t_pts || 0) / 100) : '' }}</td>
                <td class="px-1 py-1 text-center text-xs">{{ hasMatchesPlayed ? (team.t_pld || 0) : '' }}</td>
                <td class="px-1 py-1 text-center text-xs">{{ hasMatchesPlayed ? (team.t_diff || 0) : '' }}</td>
              </template>
              <template v-else>
                <td class="px-1 py-1 text-xs text-gray-400">{{ index + 1 }}</td>
                <td class="px-1 py-1 text-xs text-gray-400 italic" colspan="4">—</td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Note: Games for phase type E are handled by ChartGame component -->
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TeamName from '~/components/TeamName.vue'

const { t } = useI18n()

const props = defineProps({
  chartRound: {
    type: Number,
    default: 0
  },
  chartTeamList: {
    type: Array,
    default: () => []
  },
  chartTeams: {
    type: Array,
    default: () => []
  },
  chartTeamCount: {
    type: Number,
    default: 0
  },
  chartGames: {
    type: Array,
    default: () => []
  },
  chartGroup: {
    type: String,
    default: ''
  },
  competitionType: {
    type: String,
    default: ''
  },
  phaseType: {
    type: String,
    default: ''
  }
})

const hasMatchesPlayed = computed(() => {
  if (!props.chartTeams || props.chartTeams.length === 0) return false
  return props.chartTeams.some(t => parseInt(t.t_pld) > 0)
})

const sortedTeams = computed(() => {
  if (!props.chartTeams || props.chartTeams.length === 0) return []

  return [...props.chartTeams].sort((a, b) => {
    if (props.phaseType === 'C') {
      if (hasMatchesPlayed.value) {
        const rankA = parseInt(a.t_clt) || 0
        const rankB = parseInt(b.t_clt) || 0
        if (rankA !== rankB) return rankA - rankB

        const ptsA = Math.floor((parseInt(a.t_pts) || 0) / 100)
        const ptsB = Math.floor((parseInt(b.t_pts) || 0) / 100)
        if (ptsA !== ptsB) return ptsB - ptsA

        const gdA = parseInt(a.t_diff) || 0
        const gdB = parseInt(b.t_diff) || 0
        if (gdA !== gdB) return gdB - gdA
      } else {
        // No matches played yet: sort by draw/tirage order (t_number)
        const numA = parseInt(a.t_number) || 0
        const numB = parseInt(b.t_number) || 0
        if (numA !== numB) return numA - numB
      }

      const nameA = a.t_label || ''
      const nameB = b.t_label || ''
      return nameA.localeCompare(nameB)
    }

    const ptsA = parseInt(a.pts) || 0
    const ptsB = parseInt(b.pts) || 0
    if (ptsA !== ptsB) return ptsB - ptsA

    const gdA = parseInt(a.gd || a.diff) || 0
    const gdB = parseInt(b.gd || b.diff) || 0
    if (gdA !== gdB) return gdB - gdA

    const nameA = a.t_label || a.label || ''
    const nameB = b.t_label || b.label || ''
    return nameA.localeCompare(nameB)
  })
})

// Pad with empty rows to always show the expected team count
const displayTeams = computed(() => {
  const teams = sortedTeams.value
  const count = props.chartTeamCount || 0
  if (count <= teams.length) return teams
  const padded = [...teams]
  for (let i = teams.length; i < count; i++) {
    padded.push({ _empty: true })
  }
  return padded
})
</script>