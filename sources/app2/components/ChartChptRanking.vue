<template>
  <div class="bg-white rounded-lg p-4 shadow-sm border">
    <h6 class="font-semibold text-gray-700 mb-3">{{ t('Charts.ChampionshipRanking') }}</h6>
    <div v-if="chartRanking && chartRanking.length > 0">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2 text-left">#</th>
              <th class="px-3 py-2 text-left">{{ t('Charts.Team') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.Pts') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.Pld') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.W') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.D') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.L') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.GF') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.GA') }}</th>
              <th class="px-3 py-2 text-center">{{ t('Charts.GD') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(team, index) in chartRanking" :key="index" class="border-t">
              <td class="px-3 py-2">{{ team.t_clt || 0 }}</td>
              <td class="px-3 py-2">
                <div class="flex items-center">
                  <img v-if="showFlags && team.t_logo" :src="getTeamLogo(team.t_logo)" class="h-8 w-8 mr-2" alt="" />
                  <TeamName
                    :team-label="team.t_label"
                    :team-id="team.t_id"
                    :is-winner="false"
                    :is-highlighted="team.t_highlighted"
                  />
                </div>
              </td>
              <td class="px-3 py-2 text-center font-bold">{{ team.t_pts || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_pld || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_won || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_draw || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_lost || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_plus || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_minus || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.t_diff || 0 }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else class="text-gray-500 text-sm">
      {{ t('Charts.NoData') }}
    </div>
  </div>
</template>

<script setup>
import TeamName from '~/components/TeamName.vue'

const { t } = useI18n()

const props = defineProps({
  chartRanking: {
    type: Array,
    default: () => []
  },
  chartStatus: {
    type: String,
    default: ''
  },
  showFlags: {
    type: Boolean,
    default: true
  }
})

const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl

const getTeamLogo = (logo) => {
  return `${baseUrl}/img/${logo}`
}
</script>