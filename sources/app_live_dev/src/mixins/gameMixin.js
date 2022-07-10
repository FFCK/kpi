import liveApi from '@/network/liveApi'
import { WsCloseAll } from '@/network/wsGames'
import { Stomp } from '@stomp/stompjs'

export default {
  computed: {
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
      events: null,
      selectedEvent: null,
      network: null,
      socket: null
    }
  },
  methods: {
    async fetchEvents () {
      await liveApi
        .getEvents()
        .then(async resultEvents => {
          if (resultEvents.data) {
            this.events = resultEvents.data
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
    async fetchNetwork () {
      if (this.event > 0) {
        await liveApi
          .getEventNetwork(this.event)
          .then(resultEventNetwork => {
            if (resultEventNetwork.data) {
              this.network = resultEventNetwork.data.network
              if (this.network.global.stomp) {
                this.stompProcess()
              } else {
                this.wsConnect()
              }
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
        this.socket = new WebSocket(this.network.global.url, this.network.global.topic)
      } else {
        this.socket = new WebSocket(this.network.global.url)
      }

      this.socket.onopen = (e) => {
        console.log('ws open')
        this.socket.send(JSON.stringify({ p: this.event + '_' + this.pitch, connect: 'reader' }))
      }

      this.socket.onmessage = (event) => {
        this.wsProcess(JSON.parse(event.data))
      }

      this.socket.onclose = (event) => {
        if (event.wasClean) {
          console.log('Websocket Close.')
        } else {
          console.log('Websocket Died.')
        }
      }

      this.socket.onerror = (error) => {
        console.log(error)
      }
    },
    wsProcess (msg) {
      if (msg.p === this.event + '_' + this.pitch) {
        switch (msg.t) {
          case 'chrono':
            this.matchHorloge = msg.v.time
            this.matchHorlogeStarted = msg.v.run
            break
          case 'posses':
            this.matchPossession = msg.v
            break
          case 'period':
            this.matchPeriode = (msg.v.substring(0, 1) !== 'M') ? 'OVT' : msg.v.substring(1)
            break
          case 'scoreA':
            this.score1 = msg.v
            break
          case 'scoreB':
            this.score2 = msg.v
            break
          case 'penA':
            this.pen1 = msg.v
            break
          case 'penB':
            this.pen2 = msg.v
            break
          case 'evt':
            this.evtProcess(msg.v)
            break
          default:
            break
        }
      } else {
        console.log('pitch' + msg.p, msg)
      }
    },
    evtProcess (obj) {
      if (!['B', 'V', 'J', 'R', 'D'].includes(obj.evt)) {
        return
      }
      let line1 = ''
      let line2 = ''
      if (obj.team === 'A') {
        line1 = document.querySelector('#nation1').innerHTML
        line1 += '&nbsp;' + document.querySelector('#equipe1').innerHTML
      } else {
        line1 = document.querySelector('#nation2').innerHTML
        line1 += '&nbsp;' + document.querySelector('#equipe2').innerHTML
      }
      document.querySelector('#match_event_line1').innerHTML = line1

      if (obj.cap !== 'E') {
        line2 = '<span class="clair numero">' + obj.num + '</span>&nbsp;'
      }
      line2 += '<span class="nom">'
      line2 += this.truncateStr(obj.nom, 16)
      line2 += '</span> <span class="prenom">'
      line2 += this.truncateStr(obj.prenom, 16)
      line2 += '</span>'

      if (obj.cap === 'C') {
        line2 += ' <span class="badge bg-warning capitaine">C</span>'
      } else if (obj.cap === 'E') {
        line2 += ' (Coach)'
      }
      document.querySelector('#match_event_line2').innerHTML = line2

      document.querySelector('#match_player img').src = this.baseUrl + '/img/KIP/players/' + obj.matric + '.png'

      const banScore = document.querySelector('#ban_score, #ban_score_club')
      const categorie = document.querySelector('#categorie')
      const goalCardImg = document.querySelector('#goal_card_img')
      goalCardImg.src = this.getSrcEvtMatch(obj.evt)
      const bandeauGoal = document.querySelector('#bandeau_goal')
      if (['full'].includes(this.mode)) {
        banScore.classList.remove('animate__fadeInDown')
        banScore.classList.add('animate__fadeOutUp')
        categorie.classList.remove('animate__fadeInUp')
        categorie.classList.add('animate__fadeOutDown')
      }
      if (['full', 'events', 'static'].includes(this.mode)) {
        bandeauGoal.style.display = 'block'
        bandeauGoal.classList.remove('animate__fadeOutLeft')
        bandeauGoal.classList.add('animate__fadeInLeft')
      }
      if (['full', 'events'].includes(this.mode)) {
        setTimeout(() => {
          bandeauGoal.classList.remove('animate__fadeInLeft')
          bandeauGoal.classList.add('animate__fadeOutLeft')
          if (['full'].includes(this.mode)) {
            banScore.classList.remove('animate__fadeOutUp')
            banScore.classList.add('animate__fadeInDown')
            categorie.classList.remove('animate__fadeOutDown')
            categorie.classList.add('animate__fadeInUp')
          }
        }, process.env.VUE_APP_INTERVAL_GAMEEVENTSHOW || 8000)
      }
    },
    getSrcEvtMatch (evtMatch) {
      switch (evtMatch) {
        case 'B':
          return this.baseUrl + '/live/img/ball.png'
        case 'V':
          return this.baseUrl + '/live/img/greencard.png'
        case 'J':
          return this.baseUrl + '/live/img/yellowcard.png'
        case 'R':
        case 'D':
          return this.baseUrl + '/live/img/redcard.png'
        default:
          break
      }
      return ''
    },
    truncateStr (str, num = 12) {
      if (str.length <= num) {
        return str
      }
      return str.slice(0, num) + '.'
    },
    stompProcess () {
      this.socket = Stomp.client(this.network.global.url)
      this.socket.debug = (str) => { } // disable logs
      this.socket.reconnect_delay = 5000
      this.socket.heartbeat.outgoing = 0
      this.socket.heartbeat.incoming = 0

      this.socket.connect(this.network.global.login, this.network.global.password, () => {
        console.log('Stomp Connected')
        // Chrono
        this.socket.subscribe('/game/chrono', (message) => {
          const chrono = JSON.parse(message.body)
          if (chrono.chronoName === 'TPS-JEU') {
            this.matchHorloge = this.msToMMSS(chrono.value)
            this.matchHorlogeStarted = chrono.started
          } else if (chrono.chronoName === 'POSSES') {
            this.matchPossession = this.msToSS(chrono.value)
          }
        })
        // Period
        this.socket.subscribe('/game/period', (message) => {
          const period = JSON.parse(message.body)
          if (period.prolongation) {
            this.matchPeriode = 'OVT'
          } else {
            this.matchPeriode = period.currentPeriod
          }
        })
        // Score
        this.socket.subscribe('/game/data-game', (message) => {
          const dataGame = JSON.parse(message.body)
          if (dataGame.typeTeam === 'HOME') {
            this.score1 = dataGame.score
          } else {
            this.score2 = dataGame.score
          }
        })
      })
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
        line = ''
        if (gameEvent.Capitaine !== 'E') {
          line = '<span class="clair numero">' + gameEvent.Numero + '</span>&nbsp;'
        }
        line += '<span class="nom">'
        line += this.truncateStr(gameEvent.Nom, 16)
        line += '</span> <span class="prenom">'
        line += this.truncateStr(gameEvent.Prenom, 16)
        line += '</span>'

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
        }, process.env.VUE_APP_INTERVAL_GAMEEVENTSHOW || 8000)
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
          setTimeout(function () {
            bandeauGoal.style.display = 'none'
          }, 2000)
        }, process.env.VUE_APP_INTERVAL_GAMEEVENTSHOW || 8000)
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
    msToMMSS (ms) {
      return (~~((ms / 1000) % 60) < 10) ? ~~(ms / 60000) + ':0' + ~~((ms / 1000) % 60) : ~~(ms / 60000) + ':' + ~~((ms / 1000) % 60)
    },
    msToSS (ms) {
      return ~~(ms / 1000)
    },
    msToMMSSD (ms) {
      return (~~((ms / 1000) % 60) < 10) ? ~~(ms / 60000) + ':0' + ~~((ms / 1000) % 60) + '.' + ~~((ms % 1000) / 100) : ~~(ms / 60000) + ':' + ~~((ms / 1000) % 60) + '.' + ~~((ms % 1000) / 100)
    },
    imgUrlToBase64 (url) {
      fetch(url)
        .then((res) => res.blob())
        .then((blob) => {
          const reader = new FileReader()
          reader.onloadend = () => {
            const base64 = reader.result.replace('data:', '').replace(/^.+,/, '')
            console.log('base64', base64)
          }
          reader.readAsDataURL(blob)
        })
      return 'ok'
    }
  },
  created () {
    this.$watch(() => this.$route.fullPath, (tofullPath, previousfullPath) => {
      this.options = this.$route.params.options
      this.fetchForcedGame()
    })
  },
  mounted () {
    this.fetchForcedGame()
  }
}
