<template>
  <div class="row">
    <div
      v-for="game in chartGames"
      :key="game.g_id"
      class="col-xl-4 col-md-6 col-sm-12"
    >
      <div class="row mb-1">
        <div class="text-end col-5">
          <div
            :class="{
              btn: true,
              'btn-sm': true,
              'text-nowrap': true,
              team_name: true,
              winner:
                game.g_status === 'END' &&
                game.g_validation === 'O' &&
                (game.g_score_b === 'F' ||
                  parseInt(game.g_score_a) > parseInt(game.g_score_b)),
              looser:
                game.g_status !== 'END' ||
                game.g_validation !== 'O' ||
                game.g_score_a === 'F' ||
                parseInt(game.g_score_a) <= parseInt(game.g_score_b)
            }"
            @mouseenter="teamHover"
            @mouseleave="teamOut"
            v-html="showCode(game.t_a_label)"
          />
        </div>
        <div class="col-2 text-center text-nowrap">
          <span
            v-if="game.g_status !== 'ATT'"
            :class="{
              btn: true,
              'btn-sm': true,
              'text-nowrap': true,
              score: true,
              lcd: true,
              winner:
                game.g_status === 'END' &&
                game.g_validation === 'O' &&
                (game.g_score_b === 'F' ||
                  parseInt(game.g_score_a) > parseInt(game.g_score_b)),
              looser:
                game.g_status !== 'END' ||
                game.g_validation !== 'O' ||
                game.g_score_a === 'F' ||
                parseInt(game.g_score_a) <= parseInt(game.g_score_b),
              'text-danger': game.g_validation !== 'O'
            }"
          >
            {{ game.g_score_a.replace("?", "") || "&nbsp;" }}
          </span>
          <span
            v-if="game.g_status !== 'ATT'"
            :class="{
              btn: true,
              'btn-sm': true,
              'text-nowrap': true,
              score: true,
              lcd: true,
              winner:
                game.g_status === 'END' &&
                game.g_validation === 'O' &&
                (game.g_score_a === 'F' ||
                  parseInt(game.g_score_b) > parseInt(game.g_score_a)),
              looser:
                game.g_status !== 'END' ||
                game.g_validation !== 'O' ||
                game.g_score_b === 'F' ||
                parseInt(game.g_score_b) <= parseInt(game.g_score_a),
              'text-danger': game.g_validation !== 'O'
            }"
          >
            {{ game.g_score_b.replace("?", "") || "&nbsp;" }}
          </span>
        </div>
        <div class="text-start col-5">
          <div
            :class="{
              btn: true,
              'btn-sm': true,
              'text-nowrap': true,
              team_name: true,
              winner:
                game.g_status === 'END' &&
                game.g_validation === 'O' &&
                (game.g_score_a === 'F' ||
                  parseInt(game.g_score_b) > parseInt(game.g_score_a)),
              looser:
                game.g_status !== 'END' ||
                game.g_validation !== 'O' ||
                game.g_score_b === 'F' ||
                parseInt(game.g_score_b) <= parseInt(game.g_score_a)
            }"
            @mouseenter="teamHover"
            @mouseleave="teamOut"
            v-html="showCode(game.t_b_label)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gamesDisplayMixin } from '@/mixins/mixins'
export default {
  name: 'ChartChampionship',
  mixins: [gamesDisplayMixin],
  props: {
    chartGames: {
      type: Object,
      default: null
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
