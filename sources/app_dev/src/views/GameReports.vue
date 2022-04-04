<template>
  <div>
    <div class="container-fluid" v-if="showGame">
      <!-- <div class="text-center">
        <button class="btn btn-outline-dark my-1" @click="showGame = false">
          {{ $t("GameReports.Back") }}
        </button>
      </div> -->

      <game-report
        :current-game="currentGame"
        v-if="currentGame"
        @hide="showGame = false"
      />
    </div>
    <div v-else>
      <title-component :text="$t('nav.GameReports')" />

      <div class="container">
        <div class="row my-3">
          <button
            class="btn btn-outline-dark btn-lg col-12 my-1"
            v-if="currentGame"
            @click="showGame = true"
          >
            {{ $t("GameReports.BackToGame") }}
            <i class="bi bi-play-fill" />
          </button>
          <button class="btn btn-outline-dark btn-lg col-12 my-1">
            {{ $t("GameReports.NewGame") }}
            <i class="bi bi-plus-square-fill" />
          </button>
          <button
            class="btn btn-outline-dark btn-lg col-12 my-1"
            @click="showGames"
          >
            {{ $t("GameReports.LoadGames") }}
          </button>
          <button class="btn btn-outline-dark btn-lg col-12 my-1">
            <div class="input-group mb-2">
              <input
                type="text"
                size="10"
                pattern="[0-9]{7,8}"
                class="form-control form-control-sm"
                :placeholder="$t('GameReports.GameId')"
                id="GameId"
              />
              <button
                class="btn btn-primary"
                type="button"
                id="button-addon2"
                @click="changeGame"
              >
                {{ $t("GameReports.Load") }}
              </button>
            </div>
          </button>
        </div>

        <game-report-list :games="games" />
      </div>
    </div>
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import GameReportList from '@/components/GameReportList.vue'
import Games from '@/store/models/Games'
import Status from '@/store/models/Status'
import statusMixin from '@/mixins/statusMixin'
import prefsMixin from '@/mixins/prefsMixin'
import reportApi from '@/network/reportApi'
import GameReports from '@/store/models/GameReports'
import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'
import GameReport from '../components/GameReport.vue'

export default {
  name: 'GameReports',
  components: {
    TitleComponent,
    GameReportList,
    GameReport
  },
  mixins: [prefsMixin, statusMixin],
  data () {
    return {
      games: [],
      currentGame: null,
      showGame: false
    }
  },
  methods: {
    async showGames () {
      this.games = Games.all()
    },
    changeGame () {
      const inputId = document.querySelector('#GameId:not(:invalid)')
      if (inputId === null || inputId.value === '') {
        Status.update({
          where: 1,
          data: {
            messageText: this.$t('GameReports.IncorrectId'),
            messageClass: 'alert-danger'
          }
        })
      } else {
        this.loadGame(inputId.value)
      }
    },
    async loadGame (gameId) {
      if (!(await this.checkOnline())) {
        return
      }
      await reportApi.getGame(this.prefs.event, gameId).then(async result => {
        if (result.data !== []) {
          await GameReports.insertOrUpdate({
            data: result.data
          })
          this.currentGame = GameReports.query()
            .withAll()
            .find(result.data.g_id)
          Preferences.update({
            where: 1,
            data: {
              current_game_id: result.data.g_id
            }
          })
          await idbs.dbPut('preferences', Preferences.query().first())
          await idbs.dbPut(
            'reports',
            GameReports.query()
              .withAll()
              .find(result.data.g_id)
          )
          this.showGame = true
        } else {
          console.log('Résultat vide')
        }
      })
    }
  },
  async mounted () {
    await this.getPrefs()
    await this.prefs
    if (this.prefs.current_game_id) {
      if (GameReports.find(this.prefs.current_game_id) === null) {
        this.currentGame = await idbs.dbGet(
          'reports',
          this.prefs.current_game_id
        )
        if (this.currentGame) {
          GameReports.insertOrUpdate({
            data: this.currentGame
          })
        }
      } else {
        this.currentGame = GameReports.query()
          .withAll()
          .find(this.prefs.current_game_id)
      }
      this.showGame = true
    }
  }
}
</script>
