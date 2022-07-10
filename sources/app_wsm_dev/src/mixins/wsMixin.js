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
      urlsub: '',
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
        YELLOW_RED: 'R',
        RED: 'R',
        RED_EJECTION: 'D'
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
      console.log(this.socket[id])
      if (this.stomp[id]) {
        // this.socket[id].disconnect(() => {})
        await this.socket[id].deactivate()
        this.socket[id] = null

        this.printLog(id, 'Stomp disconnected')
        this.startedUrl[id] = false
        this.startedCount--
        this.saveConnection(id)
      } else {
        this.socket[id].close()
        this.printLog(id, 'Websocket closed.')
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
    broadcast (pitch, topic, value, id = 0) {
      // console.log(pitch, topic, message, id)
      if (this.startedUrl[id]) {
        if (this.stomp[id]) {
          const dest = '/pitch' + pitch + topic
          this.socket[id].publish({ destination: dest, body: value })
        } else {
          const obj = {
            p: this.selectedEvent + '_' + pitch,
            t: topic.substr(1),
            v: value
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
          if (connection.id === 0) {
            this.urlsub = connection.urlsub || ''
          }
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
      if (id === 0) {
        connexion.urlsub = this.urlsub
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
              url: this.urlsub,
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
      if (this.topic[id] !== '') {
        this.socket[id] = new WebSocket(this.url[id], this.topic[id])
      } else {
        this.socket[id] = new WebSocket(this.url[id])
      }

      this.socket[id].onopen = (e) => {
        this.printLog(id, 'Websocket Open.')
        this.startedUrl[id] = true
        this.startedCount++
        this.saveConnection(id)
      }

      this.socket[id].onmessage = (event) => {
        this.printLog(id, event.data)
      }

      this.socket[id].onclose = (event) => {
        if (event.wasClean) {
          this.printLog(id, 'Websocket Close.')
        } else {
          this.printLog(id, 'Websocket Died.')
        }
        this.startedUrl[id] = false
        this.startedCount--
        this.saveConnection(id)
        this.resetLogs(id)
      }

      this.socket[id].onerror = (error) => {
        console.log(error)
        this.printLog('WebSocket Error')
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
        this.printLog(id, 'Stomp Connected')
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
                const now = new Date()
                this.sync(this.game[id].id_match, 'Heure_fin', '00:' + now.getHours() + ':' + now.getMinutes())
                this.period[id] = 'M1'
                this.tpsJeu[id] = '600000'
                this.posses[id] = '60000'
                this.tpsJeuFormated[id] = '10:00'
                this.possesFormated[id] = '60'
                this.statutChrono[id] = 'waiting'
                this.scoreA[id] = '0'
                this.scoreB[id] = '0'
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
            }
            this.broadcast(id, '/period', currentPeriod)
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
            this.broadcast(id, '/chrono', { time: this.tpsJeuFormated[id], run: this.statutChrono[id] })
          } else if (chrono.chronoName === 'POSSES' && this.game[id]) {
            if (chrono.value >= 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 100)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, true, false)
            } else if (chrono.value < 10000 && (!this.posses[id] || Math.abs(this.posses[id] - chrono.value) > 100)) {
              this.posses[id] = chrono.value
              this.possesFormated[id] = this.msToSS(chrono.value, false, true)
            }
            this.broadcast(id, '/posses', this.possesFormated[id])
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
              }
              this.penA[id] = dataGame.nbPenalities
              this.broadcast(id, '/scoreA', dataGame.score)
              this.broadcast(id, '/penA', dataGame.nbPenalities)
            } else {
              if (dataGame.score !== this.scoreB[id]) {
                this.scoreB[id] = dataGame.score
                this.sync(this.game[id].id_match, 'ScoreDetailB', this.scoreB[id])
                this.printLog(id, '-Score B => ' + this.scoreB[id])
              }
              this.penB[id] = dataGame.nbPenalities
              this.broadcast(id, '/scoreB', dataGame.score)
              this.broadcast(id, '/penB', dataGame.nbPenalities)
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
              // console.log('DB Updated')
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
        const logo1 = await this.fetchLogo(game.equipe1.id)
        const logo2 = await this.fetchLogo(game.equipe2.id)
        // const logo1 = { logo: '' }
        // const logo2 = { logo: '' }
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
        // console.log('setTeams', setTeams)
        this.sendMessage(id, JSON.stringify(setTeams), '/api/game/set-teams')
        this.printLog(id, '-setTeams : ' + game.equipe1.nom + ' / ' + game.equipe2.nom)
        this.saveConnection(id)
        return true
      } else {
        this.printLog(id, '-No game to load')
        const players1 = []
        const players2 = []
        const logoTeamA = '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'
        const logoTeamB = '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'
        for (let i = 1; i <= 10; i++) {
          players1.push({
            id: i,
            name: 'Player ' + i,
            shirtNumber: i
          })
          players2.push({
            id: i,
            name: 'Player ' + i,
            shirtNumber: i
          })
        }

        const setTeams = {
          teamHome: {
            name: 'TEAM A',
            displayName: 'TEAM A',
            trigramName: '',
            textColor: '#FFFFFF',
            shirtColor: '#006480',
            strokeColor: '#EF4135',
            coach: [],
            players: players1,
            logoBase64: logoTeamA
          },
          teamGuest: {
            name: 'TEAM B',
            displayName: 'TEAM B',
            trigramName: '',
            textColor: '#FFFFFF',
            shirtColor: '#EF4135',
            strokeColor: '#006480',
            coach: [],
            players: players2,
            logoBase64: logoTeamB
          }
        }
        this.period[id] = 'M1'
        this.tpsJeu[id] = '600000'
        this.posses[id] = '60000'
        this.tpsJeuFormated[id] = '10:00'
        this.possesFormated[id] = '60'
        this.statutChrono[id] = 'waiting'
        this.scoreA[id] = '0'
        this.scoreB[id] = '0'
        this.sendMessage(id, JSON.stringify(setTeams), '/api/game/set-teams')
        this.printLog(id, '-setTeams : TEAM A / TEAM B')
        return false
      }
    }
  },
  created () {
  },
  mounted () {
  }
}
