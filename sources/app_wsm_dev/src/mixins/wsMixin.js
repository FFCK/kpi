import { Stomp } from '@stomp/stompjs'
// import { debounce } from 'lodash.debounce'
import idbs from '@/services/idbStorage'
import liveApi from '@/network/liveApi'
import prefsMixin from '@/mixins/prefsMixin'

export default {
  mixins: [prefsMixin],
  data () {
    return {
      faker: false,
      broker: false,
      url: [],
      topic: [],
      stomp: [],
      login: [],
      password: [],
      toSend: [],
      validUrl: [],
      startedUrl: [],
      startedCount: 0,
      message: [],
      socket: [],
      tpsJeu: [],
      posses: [],
      tpsJeuFormated: [],
      possesFormated: [],
      statutChrono: [],
      statutMatch: [],
      period: [],
      scoreA: [],
      scoreB: [],
      penA: [],
      penB: [],
      game: []
    }
  },
  methods: {
    debounce (func, timeout = 300) {
      let timer
      return (...args) => {
        clearTimeout(timer)
        timer = setTimeout(() => {
          func.apply(this, args)
        }, timeout)
      }
    },
    changeUrl (id) {
      if (this.isUrlValid(this.url[id])) {
        this.validUrl[id] = true
        this.message[id] = ''
      } else {
        this.validUrl[id] = false
        this.message[id] = 'Incorrect url'
      }
    },
    startUrl (id) {
      if (this.stomp[id]) {
        this.stompCreate(id)
      } else {
        this.socketCreate(id)
      }
    },
    stopUrl (id) {
      if (this.stomp[id]) {
        this.socket[id].disconnect(() => {
        })
        this.printControlMessage(id, 'Stomp disconnected')
        this.startedUrl[id] = false
        this.startedCount--
        this.saveConnection(id)
        this.socket[id] = null
      } else {
        this.socket[id].close()
        this.printControlMessage(id, 'Websocket closed.')
        this.socket[id] = null
      }
    },
    sendMessage (id, message = null, topic = null) {
      if (this.stomp[id]) {
        this.socket[id].send(topic || this.topic[id], {}, message || this.toSend[id])
      } else {
        this.socket[id].send(message || this.toSend[id])
      }
      this.toSend[id] = ''
    },
    printLog (id, log) {
      const div = document.createElement('div')
      div.append(log)
      document.querySelector('#flow-' + id).prepend(div)
    },
    printControlMessage (id, message) {
      this.message[id] = message
      setTimeout(() => {
        this.message[id] = null
      }, 5000)
    },
    resetLogs (id) {
      if (document.querySelector('#flow-' + id)) {
        document.querySelector('#flow-' + id).innerHTML = ''
      }
    },
    isUrlValid (input) {
      // eslint-disable-next-line
      const regex = '^((ws:\/\/)|(wss:\/\/))((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])|([a-z.-]+))(:[0-9]{2,5})?([a-z0-9.-/]*)$'
      const url = new RegExp(regex, 'g')
      return url.test(input)
    },
    urlUsed (id) {
      if (id === 0 || id === 20) {
        return false
      }
      for (const key in this.startedUrl) {
        if (key > 0 && key < 20 && this.startedUrl[key] && this.url[key] === this.url[id]) {
          this.printControlMessage(id, 'Url already used')
          return true
        }
      }
      return false
    },
    async loadConnections () {
      const connections = await idbs.dbGetAll('connections')
      if (connections.length >= 1) {
        connections.forEach(connection => {
          this.url[connection.id] = connection.url
          this.topic[connection.id] = connection.topic
          this.stomp[connection.id] = connection.stomp
          this.login[connection.id] = connection.login
          this.password[connection.id] = atob(connection.password)
          this.tpsJeu[connection.id] = connection.tpsJeu
          this.posses[connection.id] = connection.posses
          this.tpsJeuFormated[connection.id] = this.msToMMSS(connection.tpsJeu, true)
          this.possesFormated[connection.id] = this.msToSS(connection.posses)
          this.statutChrono[connection.id] = connection.statutChrono
          this.statutMatch[connection.id] = connection.statutMatch || 'ATT'
          this.period[connection.id] = connection.period
          this.scoreA[connection.id] = connection.scoreA
          this.scoreB[connection.id] = connection.scoreB
          this.penA[connection.id] = connection.penA || 0
          this.penB[connection.id] = connection.penB || 0
          this.game[connection.id] = connection.game
          this.changeUrl(connection.id)
          if (this.prefs.selectedEvent && connection.startedUrl) {
            this.startUrl(connection.id)
          }
        })
      }
    },
    async saveConnection (id) {
      const game = this.game[id] ? JSON.parse(JSON.stringify(this.game[id])) : null
      const connexion = {
        id: id,
        url: this.url[id],
        topic: this.topic[id],
        stomp: this.stomp[id],
        login: this.login[id],
        password: btoa(this.password[id]),
        tpsJeu: this.tpsJeu[id],
        posses: this.posses[id],
        statutChrono: this.statutChrono[id],
        statutMatch: this.statutMatch[id],
        period: this.period[id],
        scoreA: this.scoreA[id],
        scoreB: this.scoreB[id],
        penA: this.penA[id],
        penB: this.penB[id],
        game: game,
        startedUrl: this.startedUrl[id]
      }
      idbs.dbPut('connections', connexion)
      console.log('Connection ' + id + ' saved')
    },
    generateJson () {
      liveApi
        .setEventNetwork(
          this.selectedEvent,
          {
            global: {
              stomp: this.stomp[0],
              url: this.url[0],
              login: this.login[0],
              password: this.password[0],
              topic: this.topic[0]
            }
          }
        )
        .then(result => {
          if (result.data) {
            this.printLog(0, 'Json generated')
          }
        })
    },
    socketCreate (id) {
      if (this.urlUsed(id)) {
        return
      }
      const topic = this.topic[id] !== '' ? this.topic[id] : false
      this.socket[id] = new WebSocket(this.url[id], topic)

      this.socket[id].onopen = (e) => {
        this.printControlMessage(id, 'Websocket Open.')
        this.startedUrl[id] = true
        this.startedCount++
        this.saveConnection(id)
      }

      this.socket[id].onmessage = (event) => {
        this.printLog(id, event.data)
      }

      this.socket[id].onclose = (event) => {
        if (event.wasClean) {
          this.printControlMessage(id, 'Websocket Close.')
        } else {
          this.printControlMessage(id, 'Websocket Died.')
        }
        this.startedUrl[id] = false
        this.startedCount--
        this.saveConnection(id)
        this.resetLogs(id)
      }

      this.socket[id].onerror = (error) => {
        console.log(error)
        this.printControlMessage(id, error.message || 'Error')
      }
    },
    stompCreate (id) {
      if (this.urlUsed(id)) {
        return
      }
      this.socket[id] = Stomp.client(this.url[id])
      this.socket[id].debug = (str) => { } // disable logs
      this.socket[id].reconnect_delay = 5000
      this.socket[id].heartbeat.outgoing = 0
      this.socket[id].heartbeat.incoming = 0

      this.socket[id].connect(this.login[id], this.password[id], () => {
        this.printControlMessage(id, 'Stomp Connected')
        this.startedUrl[id] = true
        this.startedCount++
        this.saveConnection(id)
        this.resetLogs(id)
        this.stompSubscribe(id)
      })
    },
    stompSubscribe (id) {
      if (id > 0 && id < 20) {
        this.socket[id].subscribe(this.topic[id], (message) => {
          this.printLog(id, message.body)
        })
        this.printLog(id, 'Hello pitch ' + id + ', I\'m ready!')

        // Set-teams
        this.socket[id].subscribe('/game/ready-to-start-game', async () => {
          // console.log('/game/ready-to-start-game')
          this.game[id] = null
          if (await this.setTeams(id)) {
            console.log('game', this.game[id])
          }
        })
        this.socket[id].subscribe('/game/set-teams', (message) => {
          const setTeams = JSON.parse(message.body)
          // console.log('/game/set-teams', setTeams)
          if (this.game[id] && this.statutMatch[id] !== 'ON' && setTeams.success) {
            this.statutMatch[id] = 'ON'
            this.sync(this.game[id].id_match, 'Statut', 'ON')
            this.sync(this.game[id].id_match, 'ScoreDetailA', this.scoreA[id])
            this.sync(this.game[id].id_match, 'ScoreDetailB', this.scoreB[id])
            this.syncTimer(this.game[id].id_match, this.tpsJeu[id], 360000, 'stop')
            this.printLog(id, '-set-teams => success')
            this.saveConnection(id)
          }
        })
        // State
        this.socket[id].subscribe('/game/game-state', (message) => {
          const state = JSON.parse(message.body)
          // console.log('/game/game-state', state)
          if (this.game[id] && state.matchState) {
            this.printLog(id, '-State => ' + state.matchState)
            if (this.game[id] && this.statutMatch[id] !== 'END' && state.matchState === 'QUIT_MATCH') {
              this.statutMatch[id] = 'END'
              this.sync(this.game[id].id_match, 'Statut', 'END')
              this.sync(this.game[id].id_match, 'ScoreA', this.scoreA[id])
              this.sync(this.game[id].id_match, 'ScoreB', this.scoreB[id])
              this.saveConnection(id)
            }
            if (this.game[id] && this.statutMatch[id] !== 'END' && state.matchState === 'MATCH_NOT_STARTED') {
              this.statutMatch[id] = 'END'
              this.sync(this.game[id].id_match, 'Statut', 'END')
              this.sync(this.game[id].id_match, 'ScoreA', this.scoreA[id])
              this.sync(this.game[id].id_match, 'ScoreB', this.scoreB[id])
              this.saveConnection(id)
            }
          }
        })
        // Period
        this.socket[id].subscribe('/game/period', (message) => {
          const period = JSON.parse(message.body)
          // console.log('/game/period', period)
          const currentPeriod = (period.prolongation) ? 'P' + period.currentPeriod : 'M' + period.currentPeriod
          if (this.game[id] && this.period[id] !== currentPeriod) {
            this.period[id] = currentPeriod
            this.sync(this.game[id].id_match, 'Periode', this.period[id])
            this.printLog(id, '-Period => ' + this.period[id])
          }
        })
        // Score
        this.socket[id].subscribe('/game/data-game', (message) => {
          const dataGame = JSON.parse(message.body)
          // console.log('/game/data-game', dataGame)
          if (this.game[id]) {
            if (dataGame.typeTeam === 'HOME') {
              if (dataGame.score !== this.scoreA[id]) {
                this.scoreA[id] = dataGame.score
                this.sync(this.game[id].id_match, 'ScoreDetailA', this.scoreA[id])
                this.printLog(id, '-Score A => ' + this.scoreA[id])
              }
              this.penA[id] = dataGame.nbPenalities
              this.printLog(id, '-Penalities A => ' + this.penA[id])
            } else {
              if (dataGame.score !== this.scoreB[id]) {
                this.scoreB[id] = dataGame.score
                this.sync(this.game[id].id_match, 'ScoreDetailB', this.scoreB[id])
                this.printLog(id, '-Score B => ' + this.scoreB[id])
              }
              this.penB[id] = dataGame.nbPenalities
              this.printLog(id, '-Penalities B => ' + this.penB[id])
            }
          }
        })
        // Chrono
        this.socket[id].subscribe('/game/chrono', (message) => {
          const chrono = JSON.parse(message.body)
          // console.log('/game/chrono', chrono.chronoName, chrono)
          if (this.game[id] && chrono.chronoName === 'TPS-JEU') {
            if (chrono.value >= 10000 && (!this.tpsJeu[id] || Math.abs(this.tpsJeu[id] - Math.ceil(chrono.value / 1000) * 1000) >= 1000)) {
              this.tpsJeu[id] = chrono.value
              this.tpsJeuFormated[id] = this.msToMMSS(chrono.value, true, false)
            } else if (chrono.value < 10000 && (!this.tpsJeu[id] || Math.abs(this.tpsJeu[id] - Math.ceil(chrono.value / 100) * 100) >= 100)) {
              this.tpsJeu[id] = chrono.value
              this.tpsJeuFormated[id] = this.msToMMSS(chrono.value, false, true)
            }
            if (chrono.started !== this.statutChrono[id]) {
              this.statutChrono[id] = chrono.started
              const action = chrono.started ? 'run' : 'stop'
              this.printLog(id, '-Chrono ' + action)
              this.syncTimer(this.game[id].id_match, this.tpsJeu[id], chrono.initValue, action)
            }
          } else if (this.game[id] && chrono.chronoName === 'POSSES') {
            if (chrono.value >= 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 1000)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, true, false)
            } else if (chrono.value < 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 100)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, false, true)
            }
          }
        })
        // Player-info
        this.socket[id].subscribe('/game/player-info', (message) => {
          const playerInfo = JSON.parse(message.body)
          // console.log('/game/player-info', playerInfo)
          if (this.game[id] && this.tpsJeuFormated[id]) {
            const evt = this.gameEvent(playerInfo.score, playerInfo.card)
            let team = {}
            let player = {}
            if (playerInfo.type === 'HOME') {
              team = { code: 'A', libelle: this.game[id].equipe1.nom }
              player = { matric: this.game[id].equipe1.joueurs[playerInfo.idPlayer - 1].matric, numero: this.game[id].equipe1.joueurs[playerInfo.idPlayer - 1].Numero }
            } else {
              team = { code: 'B', libelle: this.game[id].equipe2.nom }
              player = { matric: this.game[id].equipe2.joueurs[playerInfo.idPlayer - 1].matric, numero: this.game[id].equipe2.joueurs[playerInfo.idPlayer - 1].Numero }
            }
            if (evt.code !== '') {
              this.syncGameEvt(this.game[id].id_match, this.period[id], this.tpsJeuFormated[id], evt.code, player.matric, player.numero, team.code, null)
              this.printLog(id, this.tpsJeuFormated[id] + '-' + evt.libelle + ' => #' + player.numero + ' (' + team.libelle + ')')
            }
          }
        })
        // Phase
        this.socket[id].subscribe('/game/game-phase', (message) => {
          const phase = JSON.parse(message.body)
          console.log('gamePhase', phase)
        })
        // team-game
        this.socket[id].subscribe('/game/team-game', (message) => {
          const teamGame = JSON.parse(message.body)
          console.log('TeamGame', teamGame)
        })
      } else if (id === 0) {
        this.printLog(id, 'Hello KPI Broker, I\'m ready!')
      } else {
        this.printLog(id, 'Hello Faker, I\'m ready!')
      }
    },
    syncTimer (idMatch, tpsJeu, initValue, action) {
      const maxTime = this.msToMMSS(initValue, true, false)
      const startTime = new Date()
      startTime.setTime(startTime.getTime() + tpsJeu - initValue)
      if (this.prefs.databaseSync) {
        const params = {
          startTime: startTime.getTime(),
          runTime: tpsJeu,
          maxTime: maxTime,
          action: action
        }
        liveApi
          .setGameTimer(idMatch, params)
          .then(result => {
            if (result.data) {
              console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    sync (idMatch, param, value) {
      // this.debounce(() => {
      if (this.prefs.databaseSync) {
        liveApi
          .setGameParams(idMatch, param, value)
          .then(result => {
            if (result.data) {
              console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    syncGameEvt (idMatch, period, tpsJeuFormated, code, player, number, team, reason = null, uid = null) {
      if (this.prefs.databaseSync && code !== '') {
        const params = {
          period: period,
          tpsJeu: '00:' + tpsJeuFormated?.split('.')[0] || '00:10:00',
          code: code,
          player: player,
          number: number,
          team: team,
          reason: reason,
          uid: uid
        }
        liveApi
          .setGameEvent(idMatch, params)
          .then(result => {
            if (result.data) {
              console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    gameEvent (score, card) {
      if (parseInt(score, 10) > 0) {
        return { code: 'B', libelle: 'GOAL' }
      }
      switch (card) {
        case 'GREEN':
          return { code: 'V', libelle: 'GREEN' }
        case 'YELLOW':
          return { code: 'J', libelle: 'YELLOW' }
        case 'RED':
          return { code: 'R', libelle: 'RED' }
        case 'EJECTION':
          return { code: 'D', libelle: 'EJECTION' }
        default:
          return { code: '', libelle: '' }
      }
    },
    syncRequest (id) {
      this.sendMessage(id, 'Please sync', '/api/game/sync')
      this.printLog(id, '-Sync request OK')
    },
    async setTeams (id) {
      const game = await this.fetchGame(null, this.prefs.selectedEvent, id, true)
      if (game) {
        this.game[id] = game
        const players1 = []
        const coach1 = []
        const logo1 = ''
        // if (game.equipe1.logo) {
        //   logo1 = await this.imgUrlToBase64(game.equipe1.logo)
        //   console.log('logo1:', logo1)
        // }
        for (const [key, value] of Object.entries(game.equipe1.joueurs)) {
          const key2 = parseInt(key) + 1
          if (value.Capitaine === 'E') {
            coach1.push({
              id: key2,
              name: value.Nom + ' ' + value.Prenom
            })
          } else {
            if (value.Capitaine === 'C') {
              value.Prenom += ' (C)'
            }
            players1.push({
              id: key2,
              name: value.Nom + ' ' + value.Prenom,
              shirtNumber: value.Numero
            })
          }
        }
        const players2 = []
        const coach2 = []
        const logo2 = ''
        // if (game.equipe2.logo) {
        //   logo2 = await this.imgUrlToBase64(game.equipe2.logo)
        //   console.log('logo2:', logo2)
        // }
        for (const [key, value] of Object.entries(game.equipe2.joueurs)) {
          const key2 = parseInt(key) + 1
          if (value.Capitaine === 'E') {
            coach2.push({
              id: key2,
              name: value.Nom + ' ' + value.Prenom
            })
          } else {
            if (value.Capitaine === 'C') {
              value.Prenom += ' (C)'
            }
            players2.push({
              id: key2,
              name: value.Nom + ' ' + value.Prenom,
              shirtNumber: value.Numero
            })
          }
        }

        const setTeams = {
          teamHome: {
            name: game.equipe1.nom,
            displayName: game.equipe1.nom,
            trigramName: '',
            textColor: game.equipe1.colortext || '',
            shirtColor: game.equipe1.color1 || '',
            strokeColor: game.equipe1.color2 || '',
            coach: coach1,
            players: players1,
            logoBase64: logo1
          },
          teamGuest: {
            name: game.equipe2.nom,
            displayName: game.equipe2.nom,
            trigramName: '',
            textColor: game.equipe2.colortext || '',
            shirtColor: game.equipe2.color1 || '',
            strokeColor: game.equipe2.color2 || '',
            coach: coach2,
            players: players2,
            logoBase64: logo2
          }
        }
        // console.log(setTeams)
        this.sendMessage(id, JSON.stringify(setTeams), '/api/game/set-teams')
        this.printLog(id, '-setTeams : ' + game.equipe1.nom + ' / ' + game.equipe2.nom)
        this.saveConnection(id)
        return true
      } else {
        this.printLog(id, '-No game to load')
        return false
      }
    }
  },
  created () {
  },
  mounted () {
  }
}
