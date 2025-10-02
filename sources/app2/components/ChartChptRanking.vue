<template>
  <div class="bg-white rounded-lg p-4 shadow-sm border">
    <h6 class="font-semibold text-gray-700 mb-3">Championship Ranking</h6>
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
              <td class="px-3 py-2">{{ index + 1 }}</td>
              <td class="px-3 py-2">
                <div class="flex items-center">
                  <img v-if="showFlags && team.t_logo" :src="getTeamLogo(team.t_logo)" class="h-6 w-6 mr-2" alt="" />
                  {{ team.t_label }}
                </div>
              </td>
              <td class="px-3 py-2 text-center font-bold">{{ team.pts || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.pld || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.w || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.d || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.l || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.gf || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.ga || 0 }}</td>
              <td class="px-3 py-2 text-center">{{ team.gd || 0 }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else class="text-gray-500 text-sm">
      No ranking data available
    </div>
  </div>
</template>

<script setup>
const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.apiBaseUrl.replace('/api', '')

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

const getTeamLogo = (logo) => {
  return `${baseUrl}/img/${logo}`
}
</script>