<template>
  <div>
    <div class="content-table">
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
            <th class="align-middle small d-none d-lg-table-cell">
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
              <span class="badge bg-light text-dark me-1">{{
                $t("Games.Time")
              }}</span>
              <span class="badge bg-secondary">{{ $t("Games.Pitch") }}</span>
            </th>
            <th class="cliquableNomEquipe text-end pe-5">
              {{ $t("Games.Team") }} A
            </th>
            <th class="cliquableScore">
              {{ $t("Games.Score") }}
            </th>
            <th class="cliquableNomEquipe text-start ps-5">
              {{ $t("Games.Team") }} B
            </th>
            <th
              v-if="showRefs"
              class="d-none d-lg-table-cell"
            >
              {{ $t("Games.Referee") }}
            </th>
          </tr>
        </thead>

        <tbody
          v-for="game_group in games"
          :key="game_group.goupDate"
        >
          <tr class="table-dark">
            <th
              colspan="100%"
              class="align-middle text-start pl-3"
            >
              {{ $d(new Date(game_group.goupDate), "short") }}
            </th>
          </tr>

          <tr
            v-for="game in game_group.filtered"
            :key="game.g_id"
          >
            <td
              class="align-middle text-secondary small text-center d-none d-lg-table-cell"
            >
              <i>#{{ game.g_number }}</i>
            </td>
            <td
              class="align-middle text-start text-nowrap d-none d-lg-table-cell"
            >
              <span>
                {{ game.c_code }}
                <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
              </span>
            </td>
            <td
              class="align-middle text-center text-nowrap d-none d-lg-table-cell"
            >
              <span class="badge bg-light text-dark me-1">{{
                game.g_time
              }}</span>
              <span
                v-if="game.g_pitch > 0"
                class="badge bg-secondary"
              >{{
                game.g_pitch
              }}</span>
            </td>
            <td class="text-end align-middle">
              <div
                class="d-md-block d-lg-none text-start align-top text-nowrap"
              >
                <span>
                  {{ game.c_code }}
                  <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
                </span>
              </div>
              <div
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-nowrap': true,
                  team_name: true,
                  winner:
                    game.g_status === 'END' && game.g_score_a > game.g_score_b,
                  looser:
                    game.g_status !== 'END' || game.g_score_a <= game.g_score_b
                }"
                v-html="showCode(game.t_a_label)"
              />
              <img
                class="team_logo float-end"
                :src="'http://localhost:8087/img/' + game.t_a_logo"
              >
              <div
                v-if="showRefs"
                class="d-md-block d-lg-none text-start"
              >
                <small v-html="showCode(game.r_1)" />
              </div>
            </td>
            <td class="text-secondary small text-center">
              <div class="d-md-block d-lg-none text-center">
                <i>#{{ game.g_number }}</i>
              </div>
              <div>
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
                >{{ game.g_score_a || "&nbsp;" }}</span>
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
                >{{ game.g_score_b || "&nbsp;" }}</span>
                <br>
                <span
                  :class="{
                    badge: true,
                    'bg-secondary': game.g_status === 'ATT',
                    'bg-primary': game.g_status === 'ON',
                    'bg-success': game.g_status === 'END'
                  }"
                >{{
                  game.g_status !== "ON"
                    ? $t("Games.Status." + game.g_status)
                    : $t("Games.Period." + game.g_period)
                }}</span>
              </div>
              <div
                v-if="showRefs"
                class="d-md-block d-lg-none text-center"
              >
                &nbsp;
              </div>
            </td>
            <td class="text-start align-middle">
              <div class="d-md-block d-lg-none text-end align-top text-nowrap">
                <span class="badge bg-light text-dark me-1">{{
                  game.g_time
                }}</span>
                <span
                  class="badge bg-secondary"
                >{{ $t("Games.Pitch") }} {{ game.g_pitch }}</span>
              </div>
              <div
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-nowrap': true,
                  team_name: true,
                  winner:
                    game.g_status === 'END' && game.g_score_b > game.g_score_a,
                  looser:
                    game.g_status !== 'END' || game.g_score_b <= game.g_score_a
                }"
                v-html="showCode(game.t_b_label)"
              />
              <img
                class="team_logo float-start"
                :src="'http://localhost:8087/img/' + game.t_b_logo"
              >
              <div
                v-if="showRefs"
                class="d-md-block d-lg-none text-end"
              >
                <small v-html="showCode(game.r_2)" />
              </div>
            </td>
            <td
              v-if="showRefs"
              class="d-none d-lg-table-cell"
            >
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
import { gamesDisplayMixin } from '@/services/mixins'
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
  mounted () {
  }
}
</script>

<style scoped>
.score {
  margin-left: 1px;
  margin-right: 1px;
}
.content-table {
  margin-top: 52px;
}
.table-sm {
  font-size: 13px;
}
.team_name {
  font-size: 14px;
}

.winner {
  font-weight: bold;
}

.team_logo {
  width: 30px;
}
</style>
