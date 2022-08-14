<template>
  <div class="container-fluid" v-if="prefs">
    <h1 v-if="!network">KPI Stats</h1>
    <div class="row text-center" v-if="events">
      <div class="col-7">
        <label v-if="!network">{{ $t("Event") }}</label>
        <select v-model="statsEvent" :disabled="network" class="m-1">
            <option v-for="event in events" :value="event.id" :key="event.id">{{ event.id }} - {{ event.libelle }}</option>
        </select>
      </div>
      <div class="col-3">
        <label v-if="!network">{{ $t("Pitch") }}</label>
        <select v-model="statsPitch" :disabled="network" class="m-1">
            <option v-for="(n, index) in 8" :key="index" :value="n">{{ $t("Pitch") }} {{ n }}</option>
        </select>
      </div>
      <div class="col-2">
        <span v-if="statsEvent > 0 && statsPitch > 0" class="m-1">
          <button v-if="!network" class="btn btn-sm btn-success" @click="connect">Connect</button>
          <button v-if="network" class="btn btn-sm btn-danger" @click="disconnect">Stop</button>
        </span>
      </div>
    </div>

    <div v-if="network && game" class="container-fluid mt-1">
      <div class="row">
        <div class="col text-center">
          <div id="equipe1" class="p-2" :style="'color: ' + game.equipe1.colortext + '; background-color: ' + game.equipe1.color1 +';'">
            <b>{{ game.equipe1.nom }}</b>
            <span id="score1" class="badge bg-dark text-light float-end score p-2">{{ score1 || '0' }}</span>
          </div>
        </div>
        <div class="col-3 text-center">
          <button
            id="match_horloge"
            :class="{
              'me-1': true,
              btn: true,
              'btn-sm': true,
              'btn-danger': !matchHorlogeStarted,
              'btn-success': matchHorlogeStarted
            }"
          >{{ matchHorloge }}</button>
          <span id="match_periode" class="badge bg-dark text-light">{{ matchPeriodFormated }}</span>
        </div>
        <div class="col text-center">
          <div id="equipe2" class="p-2" :style="'color: ' + game.equipe2.colortext + '; background-color: ' + game.equipe2.color1 +';'">
            <span id="score2" class="badge bg-dark text-light float-start score p-2">{{ score2 || '0' }}</span>
            <b >{{ game.equipe2.nom }}</b>
          </div>
        </div>
      </div>
      <!-- <div class="row mt-1 text-center">
      </div> -->
      <div class="row mt-1 gx-1">
        <div class="col border text-center p-1" :style="'color: ' + game.equipe1.colortext + '; background-color: ' + game.equipe1.color1 +';'">
          <div class="row g-1">
            <span class="col-md-2 col-sm-4 col-xs-6" v-for="(player, index) in team1" :key="index">
              <button
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-light': true,
                  'btn-secondary': [0, 3, 6, 7, 8].includes(btnMode),
                  'btn-outline-secondary': btnMode === 9,
                  'btn-success': btnMode === 1,
                  'btn-danger': btnMode === 2,
                  'btn-primary': btnMode === 4,
                  'btn-warning': btnMode === 5,
                  }"
                :disabled="[3, 6, 7].includes(btnMode)"
                v-if="player.matric"
                @click="action(1, player.matric)">
                <span>{{ player.Numero }}</span>
              </button>
              <button class="btn btn-sm" v-else>
                &nbsp;
              </button>
            </span>
            <span class="col-md-12 col-sm-8">
              <button
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-light': true,
                  'm-1': true,
                  'btn-secondary': [0, 3, 6, 7, 8].includes(btnMode),
                  'btn-outline-secondary': btnMode === 9,
                  'btn-success': btnMode === 1,
                  'btn-danger': btnMode === 2,
                  'btn-primary': btnMode === 4,
                  'btn-warning': btnMode === 5,
                  }"
                :disabled="[3, 6, 7].includes(btnMode)"
                @click="action(1, 0)">
                {{ $t("Team") }}
              </button>
            </span>
          </div>
        </div>
        <div class="col border text-center p-1" :style="'color: ' + game.equipe2.colortext + '; background-color: ' + game.equipe2.color1 +';'">
          <div class="row g-1">
            <span class="col-md-2 col-sm-4" v-for="(player, index) in team2" :key="index">
              <button
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-light': true,
                  'btn-secondary': [0, 2, 4, 5, 9].includes(btnMode),
                  'btn-outline-secondary': btnMode === 8,
                  'btn-success': btnMode === 1,
                  'btn-danger': btnMode === 3,
                  'btn-primary': btnMode === 6,
                  'btn-warning': btnMode === 7,
                  }"
                :disabled="[2, 4, 5].includes(btnMode)"
                v-if="player.matric"
                @click="action(2, player.matric)">
                <span>{{ player.Numero }}</span>
              </button>
              <button class="btn btn-sm" v-else>
                &nbsp;
              </button>
            </span>
            <span class="col-md-12 col-sm-8">
              <button
                :class="{
                  btn: true,
                  'btn-sm': true,
                  'text-light': true,
                  'm-1': true,
                  'btn-secondary': [0, 2, 4, 5, 9].includes(btnMode),
                  'btn-outline-secondary': btnMode === 8,
                  'btn-success': btnMode === 1,
                  'btn-danger': btnMode === 3,
                  'btn-primary': btnMode === 6,
                  'btn-warning': btnMode === 7,
                  }"
                :disabled="[2, 4, 5].includes(btnMode)"
                @click="action(2, 0)">
                {{ $t("Team") }}
              </button>
            </span>
          </div>
        </div>
      </div>

      <div class="row mt-1 g-1">

        <div class="d-grid col-3">
          <button
            :title="$t('Shot_on')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-primary': btnMode !== 4,
              'btn-primary': btnMode === 4
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 4">
            {{ $t("Shot") }} <i class="bi bi-caret-up-square-fill"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Shot_stop')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-warning': btnMode !== 5,
              'btn-warning': btnMode === 5
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 5">
            {{ $t("Stop") }} <i class="bi bi-x-square-fill"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Shot_stop')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-warning': btnMode !== 7,
              'btn-warning': btnMode === 7
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 7">
            {{ $t("Stop") }} <i class="bi bi-x-square-fill"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Shot_on')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-primary': btnMode !== 6,
              'btn-primary': btnMode === 6
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 6">
            {{ $t("Shot") }} <i class="bi bi-caret-up-square-fill"></i>
          </button>
        </div>

        <div class="d-grid col-5">
          <button
            :title="$t('Pass_possession')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-secondary': btnMode > 0 && btnMode !== 8,
              'btn-secondary': btnMode === 0 || btnMode === 8
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 0">
            {{ $t("Pass_possession") }}
          </button>
        </div>
        <div class="d-grid col-2">
          <button
            :title="$t('Neutral_ball')"
            class="btn btn-sm btn-dark"
            @click="btnMode = 0">
            {{ $t("Neutral") }}
          </button>
        </div>
        <div class="d-grid col-5">
          <button
            :title="$t('Pass_possession')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-secondary': btnMode > 0 && btnMode !== 9,
              'btn-secondary': btnMode === 0 || btnMode === 9
            }"
            :disabled="btnMode > 0 && btnMode < 4"
            @click="btnMode = 0">
            {{ $t("Pass_possession") }}
          </button>
        </div>

        <div class="d-grid col-3">
          <button
            :title="$t('Kickoff_won')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-success': btnMode !== 1,
              'btn-success': btnMode === 1
            }"
            :disabled="btnMode >= 4 && btnMode <= 7"
            @click="btnMode = 1">
            {{ $t("Kickoff") }} <i class="bi bi-emoji-sunglasses"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Kickoff_lost')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-danger': btnMode !== 2,
              'btn-danger': btnMode === 2
            }"
            :disabled="btnMode !== 2"
            @click="btnMode = 2">
            {{ $t("Kickoff") }} <i class="bi bi-emoji-frown"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Kickoff_lost')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-danger': btnMode !== 3,
              'btn-danger': btnMode === 3
            }"
            :disabled="btnMode !== 3"
            @click="btnMode = 3">
            {{ $t("Kickoff") }} <i class="bi bi-emoji-frown"></i>
          </button>
        </div>
        <div class="d-grid col-3">
          <button
            :title="$t('Kickoff_won')"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-outline-success': btnMode !== 1,
              'btn-success': btnMode === 1
            }"
            :disabled="btnMode >= 4 && btnMode <= 7"
            @click="btnMode = 1">
            {{ $t("Kickoff") }} <i class="bi bi-emoji-sunglasses"></i>
          </button>
        </div>
      </div>
    </div>
    <br>
    <!-- <div class="row">
      <div>btnMode : {{ btnMode }}</div>
      <input type="range" class="form-range" min="0" max="7" v-model.number="btnMode" />
    </div> -->

  </div>
</template>

<script>
import User from '@/store/models/User'
import routeMixin from '@/mixins/routeMixin'
import gameMixin from '@/mixins/gameMixin'
import wsMixin from '@/mixins/wsMixin'
import liveApi from '@/network/liveApi'

export default {
  name: 'Stats',
  mixins: [routeMixin, gameMixin, wsMixin],
  computed: {
    user () {
      return User.query().first()
    }
  },
  data () {
    return {
      statsEvent: 0,
      statsPitch: 0,
      network: null,
      socketStats: null,
      matchHorloge: '10:00',
      matchHorlogeStarted: false,
      matchPeriod: null,
      matchPeriodFormated: null,
      score1: null,
      score2: null,
      gameTarget: null,
      game: null,
      team1: {},
      team2: {},
      btnMode: 1
    }
  },
  methods: {
    connect () {
      this.fetchGameRotate()
      this.fetchNetwork()
    },
    async disconnect () {
      clearInterval(this.intervalGame)
      this.intervalGame = null
      this.gameTarget = null
      this.game = null
      if (this.socketStats) {
        await this.socketStats.close()
        this.socketStats = null
      }
      this.network = null
      document.querySelector('.top-margin').style.display = 'block'
    },
    fetchGameRotate () {
      if (!this.intervalGame) {
        this.fetchGame()
        this.intervalGame = setInterval(this.fetchGame, process.env.VUE_APP_INTERVAL_GAME || 20000)
      }
    },
    async fetchGame () {
      if (this.statsEvent > 0 && this.statsPitch > 0) {
        await liveApi
          .getGameId(this.statsEvent, this.statsPitch)
          .then(async resultGameId => {
            if (resultGameId.data) {
              this.gameTarget = resultGameId.data.id_match
            } else if (!resultGameId.data) {
              console.log('No result')
              return null
            }
          })
          .catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            } else {
              console.log('Error')
            }
          })
      }
      if (this.gameTarget && this.gameTarget !== this.game?.id_match) {
        try {
          const resultGameFetch = await liveApi.getGame(this.gameTarget)
          const scoreGameFetch = await liveApi.getScore(this.gameTarget)
          this.gameProcess(resultGameFetch.data, scoreGameFetch.data)
        } catch (error) {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          } else {
            console.log('Error')
          }
        }
      } else if (!this.gameTarget) {
        console.log('No game to load')
        return null
      }
    },
    gameProcess (game, score) {
      // console.log(game, score)
      this.game = game
      for (let i = 1; i <= 10; i++) {
        this.team1[i] = this.game.equipe1.joueurs.filter(
          p => { return p.Numero === i && p.Capitaine !== 'E' && p.Capitaine !== 'X' }
        )[0] || { matric: null }
        this.team2[i] = this.game.equipe2.joueurs.filter(
          p => { return p.Numero === i && p.Capitaine !== 'E' && p.Capitaine !== 'X' }
        )[0] || { matric: null }
      }
      this.score1 = score.score1
      this.score2 = score.score2
      this.matchPeriod = score.periode
      this.matchPeriodFormated = (score.periode.substring(0, 1) !== 'M') ? 'OVT' + score.periode.substring(1) : score.periode.substring(1)
      this.btnMode = 1
    },
    async fetchNetwork () {
      if (this.statsEvent > 0) {
        await liveApi
          .getEventNetwork(this.statsEvent)
          .then(resultEventNetwork => {
            if (resultEventNetwork.data) {
              this.network = resultEventNetwork.data.network
              document.querySelector('.top-margin').style.display = 'none'
              this.wsConnect()
            } else {
              console.log('No network')
            }
          })
          .catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            } else {
              console.log(error)
            }
          })
      }
    },
    wsConnect () {
      if (this.network.global.topic !== '') {
        this.socketStats = new WebSocket(this.network.global.url, this.network.global.topic)
      } else {
        this.socketStats = new WebSocket(this.network.global.url)
      }

      this.socketStats.onopen = (e) => {
        console.log('ws open')
        this.socketStats.send(JSON.stringify({ p: this.statsEvent + '_' + this.statsPitch, connect: 'reader' }))
      }

      this.socketStats.onmessage = (event) => {
        this.wsProcess(JSON.parse(event.data))
      }

      this.socketStats.onclose = (event) => {
        if (event.wasClean) {
          console.log('Websocket Close.')
        } else {
          console.log('Websocket Died.')
        }
      }

      this.socketStats.onerror = (error) => {
        console.log(error)
      }
    },
    wsProcess (msg) {
      if (msg.p === this.statsEvent + '_' + this.statsPitch) {
        switch (msg.t) {
          case 'chrono':
            this.matchHorloge = msg.v.time
            this.matchHorlogeStarted = msg.v.run
            break
          case 'period':
            if (msg.v !== this.matchPeriod) {
              this.btnMode = 1
              this.matchPeriod = msg.v
              this.matchPeriodFormated = (msg.v.substring(0, 1) !== 'M') ? 'OVT' : msg.v.substring(1)
            }
            break
          case 'scoreA':
            this.score1 = msg.v
            break
          case 'scoreB':
            this.score2 = msg.v
            break
          default:
            break
        }
      } else {
        console.log('pitch' + msg.p, msg)
      }
    },
    action (team, player) {
      switch (this.btnMode) {
        case 0:
        case 8:
        case 9:
          this.submit(this.game['equipe' + team].id, player, 'possession')
          this.btnMode = (team === 1) ? 8 : 9
          break
        case 1:
          this.submit(this.game['equipe' + team].id, player, 'kickoff')
          this.btnMode = (team === 1) ? 3 : 2
          break
        case 2:
        case 3:
          this.submit(this.game['equipe' + team].id, player, 'kickoff-ko')
          this.btnMode = 0
          break
        case 4:
        case 6:
          this.submit(this.game['equipe' + team].id, player, 'shot-in')
          this.btnMode = 0
          break
        case 5:
        case 7:
          this.submit(this.game['equipe' + team].id, player, 'shot-stop')
          this.btnMode = 0
          break
      }
    },
    submit (team, player, action) {
      const obj = {
        user: this.user.id,
        game: this.game.id_match,
        team: team,
        player: player,
        action: action,
        period: this.matchPeriod,
        timer: this.matchHorloge.substr(0, 5)
      }
      liveApi
        .setStat(obj)
        .then(result => {
          if (result.data) {
            console.log(result.data)
          }
        })
    }
  },
  mounted () {
  },
  created () {
    this.fetchEvents()
  }
}
</script>

<style>
div {
  /* border: 1px solid red; */
}

#match_horloge {
  width: 70px;
}

#match_periode {
  width: 40px
}

.btn-warning {
    background-color: #e26700;
    border-color: #e26700;
}
.btn-warning:hover {
    background-color: #e26700;
    border-color: #e26700;
}
.btn-outline-warning {
    color: #e26700;
    border-color: #e26700;
}
.btn-outline-warning:hover {
    background-color: #e26700;
    border-color: #e26700;
}
.btn-outline-warning:disabled, .btn-outline-warning.disabled {
    color: #e26700;
}
.btn-check:focus + .btn-warning, .btn-warning:focus {
    background-color: #e26700;
    border-color: #e26700;
}
</style>
