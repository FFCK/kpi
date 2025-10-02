<template>
  <div class="p-1">
    <!-- <h6 class="font-semibold text-gray-700 mb-3 text-center">{{ chartGroup }}</h6> -->

    <!-- Group Ranking for Phase Type C - Only Rankings -->
    <div v-if="phaseType === 'C'">
      <div class="overflow-x-auto">
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
            <tr v-for="(team, index) in sortedTeams" :key="index" class="border-t">
              <td class="px-2 py-1 text-xs">{{ team.t_cltlv || (index + 1) }}</td>
              <td class="px-2 py-1 text-xs">
                <div class="flex items-center">
                  <span class="bg-gray-200 text-black px-2 py-1 rounded truncate" v-html="teamNameResize(team.t_label || `Team ${index + 1}`)"></span>
                </div>
              </td>
              <td class="px-2 py-1 text-center text-xs font-bold">{{ Math.floor((team.t_pts || 0) / 100) }}</td>
              <td class="px-2 py-1 text-center text-xs">{{ team.t_pld || 0 }}</td>
              <td class="px-2 py-1 text-center text-xs">{{ team.t_diff || 0 }}</td>
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
import { useGameDisplay } from '~/composables/useGameDisplay'

const { t } = useI18n()
const { teamNameResize } = useGameDisplay()

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

// Sort teams by points (desc), then by goal difference (desc), then by name
const sortedTeams = computed(() => {
  if (!props.chartTeams || props.chartTeams.length === 0) {
    return []
  }

  return [...props.chartTeams].sort((a, b) => {
    // For phase type C, use specific team properties
    if (props.phaseType === 'C') {
      // Sort by points (descending) - t_pts divided by 100
      const ptsA = Math.floor((parseInt(a.t_pts) || 0) / 100)
      const ptsB = Math.floor((parseInt(b.t_pts) || 0) / 100)
      if (ptsA !== ptsB) return ptsB - ptsA

      // Then by goal difference (descending)
      const gdA = parseInt(a.t_diff) || 0
      const gdB = parseInt(b.t_diff) || 0
      if (gdA !== gdB) return gdB - gdA

      // Finally by team name (ascending)
      const nameA = a.t_label || ''
      const nameB = b.t_label || ''
      return nameA.localeCompare(nameB)
    }

    // For other phase types, use generic properties
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
</script>