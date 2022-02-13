<template>
  <div class="my-1 bg-light">
    <div v-if="chartStatus === 'END'" class="badge bg-success text-light ms-1">
      {{ $t("Charts.FinalRanking") }}
    </div>
    <div v-else class="badge bg-warning text-dark ms-1">
      {{ $t("Charts.ProvisionalRanking") }}
    </div>
    <table class="table table-sm table-responsive table-striped">
      <thead>
        <tr>
          <th class="text-center">{{ $t("Charts.Ranking") }}</th>
          <th>{{ $t("Charts.Team") }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rank in ranking" :key="rank.t_id">
          <th class="text-center">{{ rank.t_clt_cp }}</th>
          <td class="text-nowrap">
            <img
              class="team_logo d-none d-sm-inline me-1 img-fluid"
              v-if="showFlags"
              :src="`${baseUrl}/img/${rank.t_logo}`"
              alt=""
            />
            <span v-html="teamNameResize(rank.t_label)" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import gameDisplayMixin from '@/mixins/gameDisplayMixin'

export default {
  name: 'ChartCpRanking',
  mixins: [gameDisplayMixin],
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
      baseUrl: process.env.VUE_APP_BASE_URL,
      ranking: this.remastering(this.chartRanking)
    }
  },
  methods: {
    remastering (inputRanking) {
      return inputRanking.filter(value => parseInt(value.t_clt_cp) > 0)
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
