<template>
  <div class="row">
    <div v-for="game in games" :key="game.g_id" class="col mb-3">
      <div>
        <table class="table-sm float-end">
          <tbody>
            <tr>
              <td rowspan="2">
                <span class="num_match">#{{ game.g_number }}</span>
              </td>
              <td class="text-nowrap text-end">
                <div
                  :class="{
                    btn: true,
                    'btn-sm': true,
                    'text-nowrap': true,
                    team_name: true,
                    winner: game.g_status === 'END',
                    looser: game.g_status !== 'END'
                  }"
                  v-html="
                    showCode(
                      game.g_score_a >= game.g_score_b
                        ? game.t_a_label
                        : game.t_b_label
                    )
                  "
                  @mouseenter="teamHover"
                  @mouseleave="teamOut"
                />
                <span
                  v-if="game.g_status !== 'ATT'"
                  :class="{
                    btn: true,
                    'btn-sm': true,
                    'text-nowrap': true,
                    score: true,
                    lcd: true,
                    winner: game.g_status === 'END',
                    looser: game.g_status === 'ON',
                    'text-danger': game.g_validation !== 'O'
                  }"
                >
                  {{
                    game.g_score_a > game.g_score_b
                      ? game.g_score_a
                      : game.g_score_b || "&nbsp;"
                  }}
                </span>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap text-end">
                <div
                  :class="{
                    btn: true,
                    'btn-sm': true,
                    'text-nowrap': true,
                    team_name: true,
                    looser: true
                  }"
                  v-html="
                    showCode(
                      game.g_score_b > game.g_score_a
                        ? game.t_a_label
                        : game.t_b_label
                    )
                  "
                  @mouseenter="teamHover"
                  @mouseleave="teamOut"
                />
                <span
                  v-if="game.g_status !== 'ATT'"
                  :class="{
                    btn: true,
                    'btn-sm': true,
                    'text-nowrap': true,
                    score: true,
                    lcd: true,
                    looser: true,
                    'text-danger': game.g_validation !== 'O'
                  }"
                >
                  {{
                    game.g_score_b > game.g_score_a
                      ? game.g_score_a
                      : game.g_score_b || "&nbsp;"
                  }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { gamesMixin, gamesDisplayMixin } from '@/mixins/mixins'
export default {
  name: 'ChartGame',
  mixins: [gamesMixin, gamesDisplayMixin],
  props: {
    chartGames: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      games: null
    }
  },
  mounted () {
    this.chartGames.map(game => {
      game.t_a_label ??= this.gameEncode(game.g_code, 1)
      game.t_b_label ??= this.gameEncode(game.g_code, 2)
      // console.log(game.g_status, game.t_a_label, game.g_score_a, game.g_score_b, game.t_b_label)
      return game
    })
    this.games = this.chartGames
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
