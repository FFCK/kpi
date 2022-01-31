<template>
  <div>
    <div class="mt-1">
      <table class="table table-sm table-striped mt-2" v-if="games.length > 0">
        <thead class="table-light">
          <tr>
            <th class="align-middle small ps-1 d-none d-lg-table-cell">
              <b>#</b>
            </th>
            <th
              class="align-middle text-start text-nowrap d-none d-lg-table-cell"
            >
              <b>{{ $t("Games.Cat") }} | {{ $t("Games.Group") }}</b>
            </th>
            <th
              class="align-middle text-center text-nowrap d-none d-lg-table-cell"
            >
              <span class="btn btn-sm btn-light team_name me-1">{{
                $t("Games.Time")
              }}</span>
              <span class="btn btn-sm btn-secondary team_name">{{
                $t("Games.Pitch")
              }}</span>
            </th>
            <th class="cliquableNomEquipe text-end pe-5">
              {{ $t("Games.Team") }} A
            </th>
            <th class="cliquableScore text-center">
              {{ $t("Games.Score") }}
            </th>
            <th class="cliquableNomEquipe text-start ps-5">
              {{ $t("Games.Team") }} B
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="game in games" :key="game.g_id">
            <td
              class="align-middle text-secondary small ps-1 d-none d-lg-table-cell"
            >
              <i>#{{ game.g_number }}</i>
            </td>
            <td
              class="align-middle text-start text-nowrap d-none d-lg-table-cell mincontent"
            >
              <span>
                {{ game.c_code }}
                <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
              </span>
            </td>
            <td
              class="align-middle text-center text-nowrap d-none d-lg-table-cell mincontent"
            >
              <span class="btn btn-light team_name me-1">{{
                game.g_time
              }}</span>
              <span
                v-if="game.g_pitch > 0"
                class="btn btn-secondary team_name"
                >{{ game.g_pitch }}</span
              >
            </td>
            <td class="text-end align-top">
              <div
                class="d-md-block d-lg-none text-start align-top text-nowrap cat_group"
              >
                <span>
                  {{ game.c_code }}
                  <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
                </span>
              </div>
              <div class="text-nowrap">
                <span
                  :class="{
                    'align-top': true,
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
                  v-html="showCode(game.t_a_label)"
                />
              </div>
            </td>
            <td class="text-secondary small text-center align-top mincontent">
              <div class="d-md-block d-lg-none text-center num_match">
                <i>#{{ game.g_number }}</i>
              </div>
              <div>
                <div class="text-nowrap">
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
                    >{{ game.g_score_a }}</span
                  >
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
                    >{{ game.g_score_b }}</span
                  >
                </div>
                <div
                  :class="{
                    badge: true,
                    'bg-secondary': game.g_status === 'ATT',
                    'bg-primary': game.g_status === 'ON',
                    'bg-success': game.g_status === 'END'
                  }"
                >
                  {{
                    game.g_status !== "ON"
                      ? $t("Games.Status." + game.g_status)
                      : $t("Games.Period." + game.g_period)
                  }}
                </div>
              </div>
            </td>
            <td class="text-start align-top">
              <div class="d-md-block d-lg-none text-end align-top text-nowrap">
                <span class="btn btn-sm btn-light team_name me-1">{{
                  game.g_time
                }}</span>
                <span class="btn btn-sm btn-secondary team_name"
                  >{{ $t("Games.Pitch") }} {{ game.g_pitch }}</span
                >
              </div>
              <div class="text-nowrap">
                <span
                  :class="{
                    'align-top': true,
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
                  v-html="showCode(game.t_b_label)"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import gameDisplayMixin from '@/mixins/gameDisplayMixin'

export default {
  name: 'GameList',
  components: {},
  mixins: [gameDisplayMixin],
  props: {
    games: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL
    }
  },
  mounted () {}
}
</script>

<style scoped>
.score {
  margin-left: 1px;
  margin-right: 1px;
}
.table-sm {
  font-size: 13px;
}
.team_name {
  font-size: 14px;
}

.num_match,
.cat_group {
  line-height: 23px;
}

.winner {
  font-weight: bold;
}

.mincontent {
  width: 5px;
}
</style>
