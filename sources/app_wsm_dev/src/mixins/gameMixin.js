import liveApi from '@/network/liveApi'
import { WsCloseAll } from '@/network/wsGames'
import User from '@/store/models/User'
import idbs from '@/services/idbStorage'

export default {
  computed: {
    user () {
      return User.query().first()
    }
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      options: this.$route.params.options || [],
      intervalGame: null,
      event: parseInt(this.$route.params.event) || 0,
      pitch: parseInt(this.$route.params.pitch) || 0,
      forcedGameId: null,
      gameId: null,
      game: null,
      gameEventId: -1,
      gamePrevEventId: 0,
      events: null
    }
  },
  methods: {
    async getUser () {
      if (User.query().count() === 0) {
        const result = await idbs.dbGetAll('user')
        if (result.length === 1) {
          User.insertOrUpdate({
            data: result
          })
        }
      }
    },
    async fetchEvents () {
      await this.getUser()
      await liveApi
        .getEvents()
        .then(async resultEvents => {
          if (resultEvents.data) {
            const tempEvents = resultEvents.data
            const userEvents = this.user?.events.split('|').map(e => { return parseInt(e) })
            this.events = tempEvents.filter(event => userEvents.includes(event.id))
          } else if (!resultEvents.data) {
            console.log('No result')
          }
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          } else {
            console.log(error)
          }
        })
    },
    fetchForcedGame () {
      let forced = null
      this.options.forEach(option => {
        if (this.testGameId(option)) {
          forced = option
        }
      })
      this.forcedGameId = forced
    },
    fetchGameRotate () {
      if (!this.intervalGame) {
        console.log('Event', this.event, 'Pitch', this.pitch)
        this.updateGame()
        this.intervalGame = setInterval(this.updateGame, process.env.VUE_APP_INTERVAL_GAME || 20000)
      }
    },
    async fetchGameId (event, pitch, next = false) {
      console.log(event, pitch, next)
      if (event > 0 && pitch > 0) {
        await liveApi
          .getGameId(event, pitch)
          .then(async resultGameId => {
            if (resultGameId.data) {
              const gameTarget = next ? resultGameId.data.id_next.id : resultGameId.data.id_match
              console.log('gameTarget1', gameTarget)
              return gameTarget
            } else if (!resultGameId.data) {
              console.log('No result')
              return null
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
    async fetchGame (gameTarget = null, event = null, pitch = null, next = false, compare = null) {
      if (!gameTarget) {
        if (event > 0 && pitch > 0) {
          await liveApi
            .getGameId(event, pitch)
            .then(async resultGameId => {
              if (resultGameId.data) {
                gameTarget = next ? resultGameId.data.id_next.id : resultGameId.data.id_match
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
      }
      if (gameTarget && gameTarget !== compare) {
        try {
          const resultGameFetch = await liveApi.getGame(gameTarget)
          return resultGameFetch.data
        } catch (error) {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          } else {
            console.log('Error')
          }
          WsCloseAll()
        }
      } else if (gameTarget && gameTarget === compare) {
        console.log('Same game')
        return null
      } else {
        console.log('No game to load')
        return null
      }
    },
    async fetchLogo (numero) {
      try {
        const resultLogo = await liveApi.getLogo(numero)
        return resultLogo.data
      } catch (error) {
        if (error.message === 'Network Error') {
          console.log('Offline !')
        } else {
          console.log('Error')
        }
        return null
      }
    },
    async updateGame () {
      const game = await this.fetchGame(this.forcedGameId, this.event, this.pitch, this.options.includes('next'), this.gameId)
      if (game) {
        this.game = game
        this.gameId = game.id_match
      }
    },
    testGameId (id) {
      const regex = '^[0-9]{8,9}$'
      const gameIdFormat = new RegExp(regex, 'g')
      return gameIdFormat.test(id)
    },
    async fetchScore (id) {
      await liveApi
        .getScore(id)
        .then(resultScore => {
          const bandeauGoal = document.querySelector('#bandeau_goal')
          const banScore = (this.zone === 'club') ? document.querySelector('#ban_score_club') : document.querySelector('#ban_score')
          const categorie = document.querySelector('#categorie')

          if (!this.options.includes('events') && !this.options.includes('static')) {
            document.querySelector('#score1').innerHTML = resultScore.data.score1
            document.querySelector('#score2').innerHTML = resultScore.data.score2
            document.querySelector('#match_periode').innerHTML = this.formatPeriod(resultScore.data.periode)
          }
          if (this.options.includes('events')) {
            banScore.classList.add('d-none')
            categorie.classList.add('d-none')
          } else if (this.options.includes('static')) {
            banScore.classList.add('d-none')
            categorie.classList.add('d-none')
            bandeauGoal.classList.remove('animate__fadeOutLeft')
            bandeauGoal.classList.add('d-block')
          }
          if (!this.options.includes('only')) {
            if (resultScore.data.event.length === 0 && this.gameEventId === -1) {
              this.gameEventId = 0
            } else if (resultScore.data.event.length > 0 && this.gameEventId === -1) {
              this.gameEventId = resultScore.data.event[0].Id
            } else if (
              resultScore.data.event[0].Id !== this.gameEventId &&
              resultScore.data.event[0].Id !== this.gamePrevEventId
            ) {
              const gameEvent = resultScore.data.event[0]
              this.gamePrevEventId = resultScore.data.event[1].Id || this.gameEventId
              this.gameEventId = gameEvent.Id
              this.fetchGameEvent(gameEvent)
            } else if (resultScore.data.event.length > 0 && this.options.includes('static')) {
              this.fetchGameEvent(resultScore.data.event[0])
            }
          }
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          } else {
            console.log(error)
          }
        })
    },
    async fetchGameEvent (gameEvent) {
      // Line 1
      let line = ''
      if (gameEvent.Equipe_A_B === 'A') {
        line = document.querySelector('#nation1').innerHTML
        line += '&nbsp;' + document.querySelector('#equipe1').innerHTML
      } else {
        line = document.querySelector('#nation2').innerHTML
        line += '&nbsp;' + document.querySelector('#equipe2').innerHTML
      }
      line += '&nbsp;<span>'
      document.querySelector('#match_event_line1').innerHTML = line

      // Line 2
      if (gameEvent.Numero === null) {
        if (gameEvent.Equipe_A_B === 'A') {
          line = this.$t('Team') + ' ' + document.querySelector('#equipe1').innerHTML
        } else {
          line = this.$t('Team') + ' ' + document.querySelector('#equipe2').innerHTML
        }
      } else {
        if (gameEvent.Capitaine !== 'E') {
          line = '<span class="clair">' + gameEvent.Numero + '</span>&nbsp;'
        }
        line += ' '
        line += gameEvent.Nom
        line += ' '
        line += gameEvent.Prenom

        if (gameEvent.Capitaine === 'C') {
          line += ' <span class="badge bg-warning capitaine">C</span>'
        } else if (gameEvent.Capitaine === 'E') {
          line += ' (Coach)'
        }
      }
      line += '</span>'
      document.querySelector('#match_event_line2').innerHTML = line

      // Card
      document.querySelector('#goal_card').innerHTML = this.imgEvtMatch(gameEvent.Id_evt_match)

      this.showGameEvent()
    },
    async showGameEvent () {
      const bandeauGoal = document.querySelector('#bandeau_goal')
      const banScore = (this.zone === 'club') ? document.querySelector('#ban_score_club') : document.querySelector('#ban_score')
      const categorie = document.querySelector('#categorie')

      if (this.options.includes('events')) {
        console.log('on affiche l\'event')
        bandeauGoal.classList.remove('animate__fadeOutLeft')
        bandeauGoal.classList.add('d-block')
        bandeauGoal.classList.add('animate__fadeInLeft')
        setTimeout(function () {
          bandeauGoal.classList.remove('animate__fadeInLeft')
          bandeauGoal.classList.add('animate__fadeOutLeft')
        }, process.env.VUE_APP_INTERVAL_GAMEEVENTSHOW || 6000)
      } else if (this.options.includes('static')) {
        banScore.classList.add('d-none')
        categorie.classList.add('d-none')
        bandeauGoal.classList.remove('animate__fadeOutLeft')
        bandeauGoal.classList.add('d-block')
      } else {
        banScore.classList.remove('animate__fadeInDown')
        banScore.classList.add('animate__fadeOutUp')
        categorie.classList.remove('animate__fadeInUp')
        categorie.classList.add('animate__fadeOutDown')
        bandeauGoal.style.display = 'block'
        bandeauGoal.classList.remove('animate__fadeOutLeft')
        bandeauGoal.classList.add('animate__fadeInLeft')
        setTimeout(function () {
          bandeauGoal.classList.remove('animate__fadeInLeft')
          bandeauGoal.classList.add('animate__fadeOutLeft')
          banScore.classList.remove('animate__fadeOutUp')
          banScore.classList.add('animate__fadeInDown')
          categorie.classList.remove('animate__fadeOutDown')
          categorie.classList.add('animate__fadeInUp')
        }, process.env.VUE_APP_INTERVAL_GAMEEVENTSHOW || 6000)
      }
    },
    async fetchTimer (id) {
      await liveApi
        .getTimer(id)
        .then(resultTimer => {
          console.log('resultTimer', resultTimer.data)
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          } else {
            console.log(error)
          }
        })
    },
    formatPeriod (period) {
      switch (period) {
        case 'END':
          return 'Finished'
        case 'ATT':
        case null:
          return 'Waiting'
        case 'ON':
          return ''
        case 'M1':
          return '1' // 1st Period
        case 'M2':
          return '2' // 2d Period";
        case 'P1':
          return 'OVT' // "1st Prolong" Ovt = Overtime
        case 'P2':
          return 'OVT' // 2d Prolong";
        case 'TB':
          return 'PEN' // Penalties ...
        default:
          break
      }

      return period
    },
    imgEvtMatch (evtMatch) {
      switch (evtMatch) {
        case 'B':
          return '<img class="evt centre" src="' + this.baseUrl + '/live/img/ball.png" />'
        case 'V':
          return '<img class="evt centre" src="' + this.baseUrl + '/live/img/greencard.png" />'
        case 'J':
          return '<img class="evt centre" src="' + this.baseUrl + '/live/img/yellowcard.png" />'
        case 'R':
        case 'D':
          return '<img class="evt centre" src="' + this.baseUrl + '/live/img/redcard.png" />'
        default:
          break
      }

      return ''
    },
    logo48 (structure) {
      if (this.zone === 'inter') {
        structure = this.verifNation(structure)
        return '<img class="centre" src="' + this.baseUrl + '/img/Nations/' + structure + '.png" height="48" alt="" />'
      } else {
        return '<img class="centre" src="' + this.baseUrl + '/img/KIP/logo/' + structure + '-logo.png" height="48" alt="" />'
      }
    },
    teamName (name) {
      if (this.zone === 'inter') {
        return name.substr(0, 3).toUpperCase()
      }
      return name
    },
    verifNation (nation) {
      if (nation.length > 3) nation = nation.substr(0, 3)
      for (var i = 0; i < nation.length; i++) {
        var c = nation.substr(i, 1)
        if (c >= '0' && c <= '9') return 'FRA'
      }
      return nation
    },
    msToMMSS (ms, up = false, tenth = false) {
      if (isNaN(ms)) {
        return null
      }
      // Math.round()
      // Math.floor()
      // Math.ceil()
      let timer = 0
      if (up) {
        ms = Math.ceil(ms / 1000) * 1000
        if (Math.ceil((ms / 1000) % 60) < 10) {
          timer = Math.floor(ms / 60000) + ':0' + Math.ceil((ms / 1000) % 60)
        } else {
          timer = Math.floor(ms / 60000) + ':' + Math.ceil((ms / 1000) % 60)
        }
      } else if (tenth) {
        ms = Math.ceil(ms / 100) * 100
        if (Math.round((ms / 1000) % 60) < 10) {
          timer = Math.floor(ms / 60000) + ':0' + Math.floor((ms / 1000) % 60) + '.' + Math.ceil((ms % 1000) / 100)
        } else {
          timer = Math.floor(ms / 60000) + ':' + Math.floor((ms / 1000) % 60) + '.' + Math.ceil((ms % 1000) / 100)
        }
      } else {
        if (Math.round((ms / 1000) % 60) < 10) {
          timer = Math.floor(ms / 60000) + ':0' + Math.round((ms / 1000) % 60)
        } else {
          timer = Math.floor(ms / 60000) + ':' + Math.round((ms / 1000) % 60)
        }
      }
      return timer
    },
    msToSS (ms, up = true, tenth = false) {
      if (isNaN(ms)) {
        return null
      }
      let timer = 0
      if (up) {
        timer = Math.ceil(ms / 1000)
      } else if (tenth) {
        timer = Math.floor(ms / 1000) + '.' + Math.floor((ms % 1000) / 100)
      } else {
        timer = Math.round(ms / 1000)
      }
      return timer
    },
    imgUrlToBase64 (url) {
      fetch(url)
        .then((res) => res.blob())
        .then((blob) => {
          const reader = new FileReader()
          reader.onloadend = () => {
            const base64 = reader.result.replace('data:', '').replace(/^.+,/, '')
            return base64
          }
          reader.readAsDataURL(blob)
        })
    }
  },
  created () {
  },
  mounted () {
  }
}
