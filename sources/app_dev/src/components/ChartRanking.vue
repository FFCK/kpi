<template>
  <div class="my-1">
    <div v-if="chartStatus === 'END'" class="badge bg-success text-light">
      {{ $t("Charts.FinalRanking") }}
    </div>
    <div v-else class="badge bg-warning text-dark">
      {{ $t("Charts.ProvisionalRanking") }}
    </div>
    <table class="table table-sm table-responsive table-striped">
      <thead>
        <tr>
          <th class="text-center">{{ $t("Charts.Ranking") }}</th>
          <th>{{ $t("Charts.Team") }}</th>
          <th class="text-center">{{ $t("Charts.Pts") }}</th>
          <th class="text-center">{{ $t("Charts.Pld") }}</th>
          <th class="text-center">{{ $t("Charts.W") }}</th>
          <th class="text-center">{{ $t("Charts.D") }}</th>
          <th class="text-center">{{ $t("Charts.L") }}</th>
          <th class="text-center">{{ $t("Charts.F") }}</th>
          <th class="text-center d-none d-md-table-cell">
            {{ $t("Charts.GF") }}
          </th>
          <th class="text-center d-none d-md-table-cell">
            {{ $t("Charts.GA") }}
          </th>
          <th class="text-center">{{ $t("Charts.GD") }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rank in chartRanking" :key="rank.t_id">
          <th class="text-center">{{ rank.t_clt }}</th>
          <td class="text-nowrap">
            <img
              class="team_logo d-none d-sm-inline me-1 img-fluid"
              v-if="showFlags"
              :src="`${baseUrl}/img/${rank.t_logo}`"
              alt=""
            />
            {{ rank.t_label }}
          </td>
          <td class="text-center">{{ rank.t_pts }}</td>
          <td class="text-center">{{ rank.t_pld }}</td>
          <td class="text-center">{{ rank.t_won }}</td>
          <td class="text-center">{{ rank.t_draw }}</td>
          <td class="text-center">{{ rank.t_lost }}</td>
          <td class="text-center">{{ rank.t_f }}</td>
          <td class="text-center d-none d-md-table-cell">
            {{ rank.t_plus }}
          </td>
          <td class="text-center d-none d-md-table-cell">
            {{ rank.t_minus }}
          </td>
          <td class="text-center">
            <span v-if="parseInt(rank.t_diff) > 0">+</span>{{ rank.t_diff }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { gamesDisplayMixin } from '@/mixins/mixins'
export default {
  name: 'ChartRanking',
  mixins: [gamesDisplayMixin],
  props: {
    chartRanking: {
      type: Object,
      default: null
    },
    chartStatus: {
      type: String,
      default: null
    },
    showFlags: {
      type: Boolean,
      default: true
    }
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL
    }
  }
}
</script>

<style scoped>
.content-table {
  margin-top: 52px;
}
.table-sm {
  font-size: 13px;
}
</style>
