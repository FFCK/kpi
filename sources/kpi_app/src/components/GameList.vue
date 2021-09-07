<template>
  <div>
    <div class="mt-1">
      <table class="table table-sm table-striped mt-2">
        <caption>
          {{
            filteredGamesCount
          }}/{{
            gamesCount
          }}
          {{
            $t("Games.games")
          }}
        </caption>
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
            <th v-if="showRefs" class="d-none d-lg-table-cell">
              {{ $t("Games.Referee") }}
            </th>
          </tr>
        </thead>

        <tbody v-for="game_group in games" :key="game_group.goupDate">
          <tr class="table-dark">
            <th colspan="100%" class="align-middle text-start ps-3">
              {{ $d(new Date(game_group.goupDate), "short") }}
            </th>
          </tr>

          <tr v-for="game in game_group.filtered" :key="game.g_id">
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
                <img
                  class="team_logo d-none d-sm-inline me-1"
                  :src="`${baseUrl}/img/${game.t_a_logo}`"
                  alt=""
                />
                <span
                  :class="{
                    'align-top': true,
                    btn: true,
                    'btn-sm': true,
                    'text-nowrap': true,
                    team_name: true,
                    winner:
                      game.g_status === 'END' &&
                      game.g_score_a > game.g_score_b,
                    looser:
                      game.g_status !== 'END' ||
                      game.g_score_a <= game.g_score_b
                  }"
                  v-html="showCode(game.t_a_label)"
                />
              </div>
              <div v-if="showRefs" class="d-md-block d-lg-none text-start refs">
                <small v-html="showCode(game.r_1)" />
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
                      'btn-dark': game.g_status !== 'ATT',
                      'text-nowrap': true,
                      score: true,
                      lcd: true,
                      winner:
                        game.g_status === 'END' &&
                        game.g_score_a > game.g_score_b,
                      looser:
                        game.g_status !== 'END' ||
                        game.g_score_a <= game.g_score_b,
                      'text-danger': game.g_validation !== 'O'
                    }"
                    >{{ game.g_score_a }}</span
                  >
                  <span
                    v-if="game.g_status !== 'ATT'"
                    :class="{
                      btn: true,
                      'btn-sm': true,
                      'btn-dark': game.g_status !== 'ATT',
                      'text-nowrap': true,
                      score: true,
                      lcd: true,
                      winner:
                        game.g_status === 'END' &&
                        game.g_score_b > game.g_score_a,
                      looser:
                        game.g_status !== 'END' ||
                        game.g_score_b <= game.g_score_a,
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
              <div
                v-if="showRefs"
                class="d-md-block d-lg-none text-center refs"
              >
                &nbsp;
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
                      game.g_score_b > game.g_score_a,
                    looser:
                      game.g_status !== 'END' ||
                      game.g_score_b <= game.g_score_a
                  }"
                  v-html="showCode(game.t_b_label)"
                />
                <img
                  class="team_logo d-none d-sm-inline ms-1"
                  :src="`${baseUrl}/img/${game.t_b_logo}`"
                  alt=""
                />
              </div>
              <div v-if="showRefs" class="d-md-block d-lg-none text-end refs">
                <small v-html="showCode(game.r_2)" />
              </div>
            </td>
            <td v-if="showRefs" class="d-none d-lg-table-cell refs">
              <div>
                <small v-html="showCode(game.r_1)" />
              </div>
              <div>
                <small v-html="showCode(game.r_2)" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { gamesDisplayMixin } from '@/mixins/mixins'
export default {
  name: 'GameList',
  components: {
  },
  mixins: [gamesDisplayMixin],
  props: {
    games: {
      type: Object,
      default: null
    },
    showRefs: {
      type: Boolean,
      default: true
    },
    filteredGamesCount: {
      type: Number,
      default: 0
    },
    gamesCount: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL
    }
  },
  mounted () {
  }
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

.team_logo {
  width: 30px;
}

.mincontent {
  width: 5px;
}
</style>
