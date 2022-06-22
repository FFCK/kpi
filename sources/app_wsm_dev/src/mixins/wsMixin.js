import { Client } from '@stomp/stompjs'
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
      game: [],
      cardCode: {
        NONE: '',
        GREEN: 'V',
        YELLOW: 'J',
        RED: 'R',
        EJECTION: 'D'
      }
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
    async stopUrl (id) {
      if (this.stomp[id]) {
        // this.socket[id].disconnect(() => {})
        await this.socket[id].deactivate()
        this.socket[id] = null

        this.printControlMessage(id, 'Stomp disconnected')
        this.startedUrl[id] = false
        this.startedCount--
        this.saveConnection(id)
      } else {
        this.socket[id].close()
        this.printControlMessage(id, 'Websocket closed.')
        this.socket[id] = null
      }
    },
    sendMessage (id, message = null, topic = null) {
      if (this.stomp[id]) {
        // this.socket[id].send(topic || this.topic[id], {}, message || this.toSend[id])
        this.socket[id].publish({ destination: topic || this.topic[id], body: message || this.toSend[id] })
      } else {
        this.socket[id].send(message || this.toSend[id])
      }
      this.toSend[id] = ''
    },
    broadcast (pitch, topic, message, id = 0) {
      // console.log(pitch, topic, message, id)
      if (this.startedUrl[id]) {
        if (this.stomp[id]) {
          const dest = '/pitch' + pitch + topic
          this.socket[id].publish({ destination: dest, body: message })
        } else {
          const obj = {
            p: pitch,
            t: topic.substr(1),
            m: message
          }
          this.socket[id].send(JSON.stringify(obj))
        }
      }
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
          this.password[connection.id] = window.atob(connection.password)
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
            if (connection.id > 0 && connection.id < 20) {
              setTimeout(this.syncRequest, 2000, connection.id)
            }
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
        password: window.btoa(this.password[id]),
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
      // console.log('Connection ' + id + ' saved')
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
              password: window.btoa(this.password[0]),
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
        console.log(id, event.data)
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
      this.socket[id] = new Client({
        brokerURL: this.url[id],
        connectHeaders: {
          login: this.login[id],
          passcode: this.password[id]
        },
        debug: (str) => { }, // disable logs
        reconnectDelay: 5000,
        heartbeatIncoming: 0,
        heartbeatOutgoing: 0
      })
      this.socket[id].onConnect = (frame) => {
        this.printControlMessage(id, 'Stomp Connected')
        this.startedUrl[id] = true
        this.startedCount++
        this.saveConnection(id)
        this.resetLogs(id)
        this.stompSubscribe(id)
      }
      this.socket[id].onStompError = (frame) => {
        console.log('Broker reported error: ' + frame.headers.message)
        console.log('Additional details: ' + frame.body)
      }
      this.socket[id].activate()
    },
    stompSubscribe (id) {
      if (id > 0 && id < 20) {
        this.socket[id].subscribe(this.topic[id], (message) => {
          this.printLog(id, message.body)
        })
        this.printLog(id, 'Hello pitch ' + id + ', I\'m ready!')

        // Set-teams
        this.socket[id].subscribe('/game/ready-to-start-game', async () => {
          this.game[id] = null
          if (await this.setTeams(id)) {
            // console.log('game', this.game[id])
          }
        })
        this.socket[id].subscribe('/game/set-teams', (message) => {
          const setTeams = JSON.parse(message.body)
          // if (this.game[id] && this.statutMatch[id] !== 'ON' && setTeams.success) {
          if (this.game[id] && setTeams.success) {
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
          if (this.game[id] && state.matchState) {
            // this.printLog(id, '-State => ' + state.matchState)
            if (this.game[id] && this.statutMatch[id] !== 'END' && (state.matchState === 'QUIT_MATCH' || state.matchState === 'MATCH_NOT_STARTED')) {
              if (this.game[id] && this.period[id] !== 'M1') {
                this.sync(this.game[id].id_match, 'ScoreA', this.scoreA[id])
                this.sync(this.game[id].id_match, 'ScoreB', this.scoreB[id])
                this.sync(this.game[id].id_match, 'Statut', 'END')
                this.printLog(id, '-Fin de match !')
              } else {
                this.printLog(id, '-Match quitté non terminé !')
              }
              this.statutMatch[id] = 'END'
              this.saveConnection(id)
            }
          }
        })
        // Period
        this.socket[id].subscribe('/game/period', (message) => {
          const period = JSON.parse(message.body)
          if (this.game[id] && this.statutMatch[id] === 'ON') {
            const currentPeriod = (period.prolongation) ? 'P' + (period.currentPeriod - 2) : 'M' + period.currentPeriod
            if (this.game[id] && this.period[id] !== currentPeriod) {
              this.period[id] = currentPeriod
              this.sync(this.game[id].id_match, 'Periode', this.period[id])
              this.printLog(id, '-Period => ' + this.period[id])
              this.broadcast(id, '/period', currentPeriod)
            }
          }
        })
        // Chrono
        this.socket[id].subscribe('/game/chrono', (message) => {
          const chrono = JSON.parse(message.body)
          chrono.value = Number(chrono.value)
          if (chrono.chronoName === 'TPS-JEU' && this.game[id] && this.statutMatch[id] === 'ON') {
            if (chrono.value >= 10000 && (!this.tpsJeu[id] || Math.abs(this.tpsJeu[id] - Math.ceil(chrono.value / 1000) * 1000) >= 1000)) {
              this.tpsJeu[id] = chrono.value
              this.tpsJeuFormated[id] = this.msToMMSS(chrono.value, true, false)
              this.broadcast(id, '/chrono', this.tpsJeuFormated[id])
            } else if (chrono.value < 10000 && (!this.tpsJeu[id] || Math.abs(this.tpsJeu[id] - Math.ceil(chrono.value / 100) * 100) >= 100)) {
              this.tpsJeu[id] = chrono.value
              this.tpsJeuFormated[id] = this.msToMMSS(chrono.value, false, true)
              this.broadcast(id, '/chrono', this.tpsJeuFormated[id])
            }

            if (chrono.value !== this.statutChrono[id]) {
              this.statutChrono[id] = chrono.value
              const action = chrono.started ? 'run' : 'stop'
              this.printLog(id, '-Chrono ' + action)
              this.syncTimer(this.game[id].id_match, this.tpsJeu[id], chrono.initValue, action)
              // console.log('syncTimer', this.game[id].id_match, this.tpsJeu[id], chrono.initValue, action)
            }
          } else if (chrono.chronoName === 'POSSES' && this.game[id]) {
            if (chrono.value >= 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 100)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, true, false)
              this.broadcast(id, '/posses', this.possesFormated[id])
            } else if (chrono.value < 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 100)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, false, true)
              this.broadcast(id, '/posses', this.possesFormated[id])
            }
          } // PEN_H1, PEN_H2, PEN_G1, PEN_G2
        })
        // Score
        this.socket[id].subscribe('/game/data-game', (message) => {
          const dataGame = JSON.parse(message.body)
          if (this.game[id] && this.statutMatch[id] === 'ON') {
            if (dataGame.typeTeam === 'HOME') {
              if (dataGame.score !== this.scoreA[id]) {
                this.scoreA[id] = dataGame.score
                this.sync(this.game[id].id_match, 'ScoreDetailA', this.scoreA[id])
                this.printLog(id, '-Score A => ' + this.scoreA[id])
                this.broadcast(id, '/scoreA', this.scoreA[id])
              }
              this.penA[id] = dataGame.nbPenalities
              this.broadcast(id, '/penA', this.penA[id])
            } else {
              if (dataGame.score !== this.scoreB[id]) {
                this.scoreB[id] = dataGame.score
                this.sync(this.game[id].id_match, 'ScoreDetailB', this.scoreB[id])
                this.printLog(id, '-Score B => ' + this.scoreB[id])
                this.broadcast(id, '/scoreB', this.scoreB[id])
              }
              this.penB[id] = dataGame.nbPenalities
              this.broadcast(id, '/penB', this.penB[id])
            }
          }
        })
        // Player-info
        this.socket[id].subscribe('/game/player-info', (message) => {
          const playerInfo = JSON.parse(message.body)
          if (this.game[id] && this.statutMatch[id] === 'ON' && this.tpsJeuFormated[id]) {
            const equipe = (playerInfo.type === 'HOME') ? 'equipe1' : 'equipe2'
            const player = this.game[id][equipe].joueurs[playerInfo.idPlayer - 1] || null
            // console.log('player', player)
            if (player) {
              const score = Number(playerInfo.score)
              const card = playerInfo.card
              const team = { code: (playerInfo.type === 'HOME') ? 'A' : 'B', libelle: this.game[id][equipe].nom }
              let action = null
              const pScore = player?.score || 0
              const pCard = player?.card || 'NONE'
              if (score > pScore) {
                action = 'add'
                this.syncGameEvt(this.game[id].id_match, this.period[id], this.tpsJeuFormated[id], 'B', player.matric, player.Numero, team.code, action)
                // console.log('But!')
                this.printLog(id, this.tpsJeuFormated[id] + '-GOAL => #' + player.Numero + ' (' + team.libelle + ')')
                this.game[id][equipe].joueurs[playerInfo.idPlayer - 1].score = score
                this.broadcast(id, '/evt',
                  {
                    evt: 'B',
                    team: team.code,
                    matric: player.matric,
                    num: player.Numero,
                    nom: player.Nom,
                    prenom: player.Prenom,
                    cap: player.Capitaine
                  }
                )
              } else if (score < pScore) {
                action = 'remove'
                this.syncGameEvt(this.game[id].id_match, this.period[id], this.tpsJeuFormated[id], 'B', player.matric, player.Numero, team.code, action)
                // console.log('Annulation du but!')
                this.printLog(id, this.tpsJeuFormated[id] + '-GOAL (REMOVE) => #' + player.Numero + ' (' + team.libelle + ')')
                this.game[id][equipe].joueurs[playerInfo.idPlayer - 1].score = score
              } else if (card !== 'NONE' && card !== pCard) {
                action = 'add'
                this.syncGameEvt(this.game[id].id_match, this.period[id], this.tpsJeuFormated[id], this.cardCode[card], player.matric, player.Numero, team.code, action)
                // console.log('Carton!')
                this.printLog(id, this.tpsJeuFormated[id] + '-' + card + ' => #' + player.Numero + ' (' + team.libelle + ')')
                this.game[id][equipe].joueurs[playerInfo.idPlayer - 1].card = card
                this.broadcast(id, '/evt',
                  {
                    evt: this.cardCode[card],
                    team: team.code,
                    matric: player.matric,
                    num: player.Numero,
                    nom: player.Nom,
                    prenom: player.Prenom,
                    cap: player.Capitaine
                  }
                )
              } else if (card === 'NONE' && card !== pCard) {
                action = 'remove'
                this.syncGameEvt(this.game[id].id_match, this.period[id], this.tpsJeuFormated[id], this.cardCode[pCard], player.matric, player.Numero, team.code, action)
                // console.log('Annulation du carton!')
                this.printLog(id, this.tpsJeuFormated[id] + '-' + pCard + ' (REMOVE) => #' + player.Numero + ' (' + team.libelle + ')')
                this.game[id][equipe].joueurs[playerInfo.idPlayer - 1].card = card
              }
            }
          }
        })
        // team-game
        this.socket[id].subscribe('/game/team-game', (message) => {
          const teamGame = JSON.parse(message.body)
          if (this.game[id] && this.statutMatch[id] === 'ON') {
            teamGame.teamGameHome.playersGame.forEach(teamGamePlayer => {
              const localPlayer = this.game[id].equipe1.joueurs[teamGamePlayer.idPlayer - 1] || null
              const pSelected = (typeof localPlayer?.selected !== 'undefined') ? localPlayer.selected : true
              if (teamGamePlayer.selected !== pSelected) {
                this.syncPlayerSelected(this.game[id].id_match, 'A', localPlayer.matric, teamGamePlayer.selected, localPlayer.Capitaine)
                this.game[id].equipe1.joueurs[teamGamePlayer.idPlayer - 1].selected = teamGamePlayer.selected
                this.printLog(id, this.tpsJeuFormated[id] + '- #' + teamGamePlayer.shirtNumber + ' Active: ' + teamGamePlayer.selected + ' (' + this.game[id].equipe1.nom + ')')
              }
            })
            teamGame.teamGameGuest.playersGame.forEach(teamGamePlayer => {
              const localPlayer = this.game[id].equipe2.joueurs[teamGamePlayer.idPlayer - 1]
              const pSelected = (typeof localPlayer.selected !== 'undefined') ? localPlayer.selected : true
              if (teamGamePlayer.selected !== pSelected) {
                this.syncPlayerSelected(this.game[id].id_match, 'B', localPlayer.matric, teamGamePlayer.selected, localPlayer.Capitaine)
                this.game[id].equipe2.joueurs[teamGamePlayer.idPlayer - 1].selected = teamGamePlayer.selected
                this.printLog(id, this.tpsJeuFormated[id] + '- #' + teamGamePlayer.shirtNumber + ' Active: ' + teamGamePlayer.selected + ' (' + this.game[id].equipe2.nom + ')')
              }
            })
          }
        })
        // Phase
        this.socket[id].subscribe('/game/game-phase', (message) => {
          // const phase = JSON.parse(message.body)
          // console.log('gamePhase', phase)
        })
      } else if (id === 0) { // Général broker
        this.printLog(id, 'Hello KPI Broker, I\'m ready!')
      } else if (id === 20) { // Faker
        this.printLog(id, 'Hello Faker, I\'m ready!')
        this.socket[id].subscribe('/pitch1/period', (message) => {
          console.log('Pitch1-period', message.body)
        })
        this.socket[id].subscribe('/pitch1/chrono', (message) => {
          console.log('Pitch1-chrono', message.body)
        })
        this.socket[id].subscribe('/pitch1/possession', (message) => {
          console.log('Pitch1-possession', message.body)
        })
        this.socket[id].subscribe('/pitch1/scoreA', (message) => {
          console.log('Pitch1-scoreA', message.body)
        })
        this.socket[id].subscribe('/pitch1/scoreB', (message) => {
          console.log('Pitch1-scoreB', message.body)
        })
      }
    },
    syncPlayerSelected (idMatch, team, player, selected, capitaine) {
      if (this.prefs.databaseSync) {
        const params = {
          team: team,
          player: player,
          status: selected ? capitaine : 'X'
        }
        liveApi
          .setPlayerStatus(idMatch, params)
          .then(result => {
            if (result.data) {
              // console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    syncTimer (idMatch, tpsJeu, initValue, action) {
      tpsJeu = Number(tpsJeu)
      const maxTime = this.msToMMSS(initValue, true, false)
      const startTime = new Date()
      // console.log('time', startTime.getTime(), tpsJeu, initValue, startTime.getTime() + tpsJeu - initValue)
      startTime.setTime(startTime.getTime() + tpsJeu - initValue)
      // console.log('startTime', startTime.getTime())
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
              // console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    sync (idMatch, param, value) {
      if (this.prefs.databaseSync) {
        liveApi
          .setGameParams(idMatch, param, value)
          .then(result => {
            if (result.data) {
              // console.log('DB Updated')
            }
          })
      } else {
        console.log('DBSync: false')
      }
    },
    syncGameEvt (idMatch, period, tpsJeuFormated, code, player, number, team, action = null, reason = null) {
      if (this.prefs.databaseSync && code !== '') {
        const params = {
          period: period,
          tpsJeu: '00:' + tpsJeuFormated?.split('.')[0] || '00:10:00',
          code: code,
          player: player,
          number: number,
          team: team,
          reason: reason,
          action: action
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
    syncRequest (id) {
      this.period[id] = 'M1'
      this.tpsJeu[id] = '600000'
      this.posses[id] = '60000'
      this.tpsJeuFormated[id] = '10:00'
      this.possesFormated[id] = '60'
      this.statutChrono[id] = 'waiting'
      this.scoreA[id] = '0'
      this.scoreB[id] = '0'
      this.sendMessage(id, 'Please sync', '/api/game/sync')
      this.printLog(id, '-Sync request OK')
    },
    async setTeams (id) {
      const game = await this.fetchGame(null, this.prefs.selectedEvent, id, true)
      if (game) {
        this.statutMatch[id] = 'ATT'
        // const logo1 = await this.fetchLogo(game.equipe1.id)
        // const logo2 = await this.fetchLogo(game.equipe2.id)
        const logo1 = { logo: '' }
        const logo2 = { logo: '' }
        this.game[id] = game
        const players1 = []
        const coach1 = []
        let prevShirtNumber = ''
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
              shirtNumber: (value.Numero === prevShirtNumber) ? '0' + value.Numero : value.Numero
            })
          }
          prevShirtNumber = value.Numero
        }
        const players2 = []
        const coach2 = []
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
              shirtNumber: (value.Numero === prevShirtNumber) ? '0' + value.Numero : value.Numero
            })
          }
          prevShirtNumber = value.Numero
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
            logoBase64: logo1?.logo || ''
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
            logoBase64: logo2?.logo || ''
          }
        }
        console.log('setTeams', setTeams)
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
