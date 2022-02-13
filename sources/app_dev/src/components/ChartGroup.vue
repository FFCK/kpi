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
          <td class="text-center">{{ team.t_cltlv }}</td>
          <td>
            <span
              class="team_name btn btn-sm looser text-nowrap"
              @mouseenter="teamHover"
              @mouseleave="teamOut"
            >
              <span v-html="teamNameResize(team.t_label)" />
            </span>
          </td>
          <td class="text-center">{{ team.t_pts / 100 }}</td>
          <td class="text-center">{{ team.t_pld }}</td>
          <td class="text-center">{{ team.t_diff }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import gamesMixin from '@/mixins/gamesMixin'
import gameDisplayMixin from '@/mixins/gameDisplayMixin'

export default {
  name: 'ChartGroup',
  mixins: [gamesMixin, gameDisplayMixin],
  props: {
    chartRound: {
      type: String,
      default: null
    },
    chartGroup: {
      type: String,
      default: null
    },
    chartTeamList: {
      type: Object,
      default: null
    },
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
        var teams = []
        if (this.chartRound === '1') {
          const group = this.chartGroup.replace(/Group |Poule /, '')
          teams = this.chartTeamList
            .filter(value => value.t_group === group)
            .map(value => {
              return value.t_label
            })
        }
        if (teams.length === 0) {
          if (this.chartGames) {
            this.chartGames.forEach(element => {
              teams.push(this.gameEncode(element, 1))
              teams.push(this.gameEncode(element, 2))
            })
          } else {
            for (let i = 1; i <= this.chartTeamCount; i++) {
              teams.push(this.gameEncode('[T' + i + ']', 1))
            }
          }
        }
        this.anonymousTeams = [...new Set(teams)]
          .filter(value => value !== null)
          .sort()
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
