<template>
  <div>
    <div class="content-table">
      <table class="table table-sm table-striped mt-2">
        <caption>{{ filteredGamesCount }}/{{ gamesCount }} {{ $t('Games.games') }}</caption>
        <thead class="thead-light">
          <tr>
            <th class="align-middle small d-none d-lg-table-cell"><b>#</b></th>
            <th class="align-middle text-left text-nowrap d-none d-lg-table-cell"><b>{{ $t('Games.Cat') }} | {{ $t('Games.Group') }}</b></th>
            <th class="align-middle text-center text-nowrap d-none d-lg-table-cell">
              <span class="badge badge-light mr-1">{{ $t('Games.Time') }}</span>
              <span class="badge badge-secondary">{{ $t('Games.Pitch') }}</span>
            </th>
            <th class="cliquableNomEquipe">{{ $t('Games.Team') }} A</th>
            <th class="cliquableScore">{{ $t('Games.Score') }}</th>
            <th class="cliquableNomEquipe">{{ $t('Games.Team') }} B</th>
            <th class="d-none d-lg-table-cell" v-if="showRefs">{{ $t('Games.Referee') }}</th>
          </tr>
        </thead>

        <tbody v-for="game_group in games" :key="game_group.goupDate">
          <tr class="thead-dark">
            <th colspan="100%" class="align-middle text-left pl-3">{{ $d(new Date(game_group.goupDate), 'short') }}</th>
          </tr>

          <tr v-for="game in game_group.filtered" :key="game.g_id">
            <td class="align-middle text-secondary small text-center d-none d-lg-table-cell">
              <i>#{{ game.g_number }}</i>
            </td>
            <td class="align-middle text-left text-nowrap d-none d-lg-table-cell">
              <span>
                {{ game.c_code }}
                <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
              </span>
            </td>
            <td class="align-middle text-center text-nowrap d-none d-lg-table-cell">
              <span class="badge badge-light mr-1">{{ game.g_time }}</span>
              <span class="badge badge-secondary">{{ game.g_pitch }}</span>
            </td>
            <td class="text-right align-middle">
              <div class="d-md-block d-lg-none text-left align-top text-nowrap">
                <span>
                  {{ game.c_code }}
                  <span v-if="game.d_phase"> | {{ game.d_phase }}</span>
                </span>
              </div>
              <div :class="{ btn: true, 'btn-sm': true, 'text-nowrap': true, team_name: true, winner: game.g_score_a > game.g_score_b }" v-html="game.t_a_label"></div>
              <img class="team_logo d-none d-lg-block float-right mt-2" src="//medias.lequipe.fr/img-flags-default/AUT/140">
              <div class="d-md-block d-lg-none text-left" v-if="showRefs">
                <small v-html="game.r_1"></small>
              </div>
            </td>
            <td class="text-secondary small align-middle text-center">
              <div class="d-md-block d-lg-none text-center">
                <i>#{{ game.g_number }}</i>
              </div>
              <div class="btn btn-sm btn-dark text-nowrap">
                <span :class="{ winner: game.g_score_a > game.g_score_b }">{{ game.g_score_a }}</span>
                <span> - </span>
                <span :class="{ winner: game.g_score_b > game.g_score_a }">{{ game.g_score_b }}</span>
              </div>
              <div class="d-md-block d-lg-none text-center" v-if="showRefs">&nbsp;</div>
            </td>
            <td class="text-left align-middle">
              <div class="d-md-block d-lg-none text-right align-top text-nowrap">
                <span class="badge badge-light mr-1">{{ game.g_time }}</span>
                <span class="badge badge-secondary">{{ $t('Games.Pitch') }} {{ game.g_pitch }}</span>
              </div>
              <div :class="{ btn: true, 'btn-sm': true, 'text-nowrap': true, team_name: true, winner: game.g_score_b > game.g_score_a }" v-html="game.t_b_label"></div>
              <img class="team_logo d-none d-lg-block float-left mt-2" src="//medias.lequipe.fr/img-flags-default/AUT/140">
              <div class="d-md-block d-lg-none text-right" v-if="showRefs">
                <small v-html="game.r_2"></small>
              </div>
            </td>
            <td class="d-none d-lg-table-cell" v-if="showRefs">
              <div>
                <small v-html="game.r_1"></small>
              </div>
              <div>
                <small v-html="game.r_2"></small>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GameList',
  components: {
  },
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
  methods: {
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
.team_name {
  font-size: 14px
}

.winner {
  font-weight: bold;
}

.team_logo {
  width: 20px;
}

</style>
