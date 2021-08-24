<template>
  <div class="table-responsive">
    <table class="table table-striped table-sm">
      <thead>
        <tr>
          <th class="text-center">
            #
          </th>
          <th>{{ $t("Games.Teams") }}</th>
          <th class="text-center">
            {{ $t("Charts.Pts") }}
          </th>
          <th class="text-center">
            {{ $t("Charts.Pld") }}
          </th>
          <th class="text-center">
            +/-
          </th>
        </tr>
      </thead>
      <tbody v-if="anonymousGroup">
        <tr v-for="(team, index) in anonymousTeams" :key="index">
          <td />
          <td>
            <span
              class="team_name btn btn-sm looser text-nowrap anonymous"
              @mouseenter="teamHover"
              @mouseleave="teamOut"
            >
              {{ showCode(team) || "" }}
            </span>
          </td>
          <td />
          <td />
          <td />
        </tr>
      </tbody>
      <tbody v-else>
        <tr v-for="(team, index) in chartTeams" :key="index">
          <td>{{ team.t_cltlv }}</td>
          <td>
            <span
              class="team_name btn btn-sm looser text-nowrap"
              @mouseenter="teamHover"
              @mouseleave="teamOut"
            >
              {{ team.t_label }}
            </span>
          </td>
          <td>{{ team.t_pts / 100 }}</td>
          <td>{{ team.t_pld }}</td>
          <td>{{ team.t_diff }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { gamesMixin, gamesDisplayMixin } from '@/services/mixins'
export default {
  name: 'ChartGroup',
  mixins: [gamesMixin, gamesDisplayMixin],
  props: {
    chartTeams: {
      type: Object,
      default: null
    },
    chartTeamCount: {
      type: Number,
      default: null
    },
    chartGames: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      anonymousTeams: {},
      anonymousGroup: false
    }
  },
  mounted () {
    this.checkRealTeams()
  },
  methods: {
    checkRealTeams () {
      if (this.chartTeams.length < this.chartTeamCount) {
        const teams = []
        this.chartGames.forEach(element => {
          teams.push(this.gameEncode(element, 1))
          teams.push(this.gameEncode(element, 2))
        })
        this.anonymousTeams = [...new Set(teams)].filter(value => value !== null).sort()
        this.anonymousGroup = true
      }
    }
  }
}
</script>

<style scoped>
.content-table {
  margin-top: 52px;
}
.table-sm {
  font-size: 12px;
}
</style>
