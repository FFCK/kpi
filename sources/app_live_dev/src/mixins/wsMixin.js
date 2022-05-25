import { Stomp } from '@stomp/stompjs'
import idbs from '@/services/idbStorage'

export default {
  data () {
    return {
      pitches: 4,
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
      statutChrono: [],
      period: [],
      scoreA: [],
      scoreB: [],
      game: []
    }
  },
  methods: {
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
          this.printControlMessage(id, 'Stomp disconnected')
          this.startedUrl[id] = false
          this.startedCount--
          this.saveConnection(id)
        })
      } else {
        this.socket[id].close()
        this.printControlMessage(id, 'Websocket closed.')
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
      document.querySelector('#flow-' + id).innerHTML = ''
    },
    isUrlValid (input) {
      // eslint-disable-next-line
      const regex = '^((ws:\/\/)|(wss:\/\/))((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])|([a-z.-]+))(:[0-9]{2,5})?([a-z0-9.-/]*)$'
      const url = new RegExp(regex, 'g')
      return url.test(input)
    },
    urlUsed (id) {
      if (id === 0) {
        return false
      }
      for (const key in this.startedUrl) {
        if (key > 0 && this.startedUrl[key] && this.url[key] === this.url[id]) {
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
          this.statutChrono[connection.id] = connection.statutChrono
          this.period[connection.id] = connection.period
          this.scoreA[connection.id] = connection.scoreA
          this.scoreB[connection.id] = connection.scoreB
          this.game[connection.id] = connection.game
          this.changeUrl(connection.id)
          if (connection.startedUrl) {
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
        period: this.period[id],
        scoreA: this.scoreA[id],
        scoreB: this.scoreB[id],
        game: game,
        startedUrl: this.startedUrl[id]
      }
      idbs.dbPut('connections', connexion)
      console.log('Connection ' + id + ' saved')
    },
    async loadEvent () {
      await idbs.dbGet('event', 1)
        .then(result => {
          this.selectedEvent = result.selectedEvent
        }).catch(_ => {
          console.log('Error fetching selected event from IDB.')
        })
    },
    saveEvent () {
      idbs.dbPut('event', { id: 1, selectedEvent: this.selectedEvent })
      console.log('event saved')
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
      if (id > 0) {
        this.socket[id].subscribe(this.topic[id], (message) => {
          this.printLog(id, message.body)
        })
        this.printLog(id, 'Hello pitch ' + id + ', I\'m ready!')

        // Set-teams
        this.socket[id].subscribe('/game/ready-to-start-game', () => {
          this.setTeams(id)
        })
        // Chrono
        this.socket[id].subscribe('/game/chrono', (message) => {
          const chrono = JSON.parse(message.body)
          if (chrono.chronoName === 'TPS-JEU') {
            this.tpsJeu[id] = this.msToMMSS(chrono.value)
            this.statutChrono[id] = chrono.started
            this.printLog(id, '-Chrono ' + chrono.chronoName + ' => ' + this.tpsJeu[id] + ' (' + chrono.started + ')')
          } else if (chrono.chronoName === 'POSSES') {
            this.posses[id] = this.msToSS(chrono.value)
            this.printLog(id, '-Chrono ' + chrono.chronoName + ' => ' + this.posses[id] + 's')
          }
        })
        // Period
        this.socket[id].subscribe('/game/period', (message) => {
          const period = JSON.parse(message.body)
          if (period.prolongation) {
            this.period[id] = 'OVT'
          } else {
            this.period[id] = period.currentPeriod
          }
          this.printLog(id, '-Period => ' + this.period[id])
        })
        // Score
        this.socket[id].subscribe('/game/data-game', (message) => {
          const dataGame = JSON.parse(message.body)
          if (dataGame.typeTeam === 'HOME') {
            this.scoreA[id] = dataGame.score
            this.printLog(id, '-Score A => ' + this.scoreA[id])
          } else {
            this.scoreB[id] = dataGame.score
            this.printLog(id, '-Score B => ' + this.scoreB[id])
          }
        })
        // Player-info
        this.socket[id].subscribe('/game/player-info', (message) => {
          const playerInfo = JSON.parse(message.body)
          const evt = (playerInfo.score === '1') ? 'Goal' : playerInfo.card
          const team = (playerInfo.type === 'HOME') ? this.game[id].equipe1.nom : this.game[id].equipe2.nom
          this.printLog(id, this.tpsJeu[id] + '-' + evt + ' => #' + playerInfo.idPlayer + ' (' + team + ')')
        })
      } else {
        this.printLog(id, 'Hello Global, I\'m ready!')
      }
    },
    sync (id) {
      this.sendMessage(id, 'Please sync', '/game/sync')
      this.printLog(id, '-Sync request OK')
    },
    async setTeams (id) {
      const game = await this.fetchGame(null, this.selectedEvent, id, true)
      if (game) {
        this.game[id] = game
        const players1 = []
        const coach1 = []
        game.equipe1.joueurs.forEach(joueur => {
          if (joueur.Capitaine === 'E') {
            coach1.push({
              name: joueur.Nom + ' ' + joueur.Prenom
            })
          } else {
            if (joueur.Capitaine === 'C') {
              joueur.Prenom += ' (C)'
            }
            players1.push({
              name: joueur.Nom + ' ' + joueur.Prenom,
              shirtNumber: joueur.Numero
            })
          }
        })
        const players2 = []
        const coach2 = []
        game.equipe2.joueurs.forEach(joueur => {
          if (joueur.Capitaine === 'E') {
            coach2.push({
              name: joueur.Nom + ' ' + joueur.Prenom
            })
          } else {
            players2.push({
              name: joueur.Nom + ' ' + joueur.Prenom,
              shirtNumber: joueur.Numero
            })
          }
        })

        // if (game.equipe1.logo) {
        //   const base64 = await this.imgUrlToBase64(game.equipe1.logo)
        //   console.log('base64:', base64)
        // }

        const setTeams = {
          teamHome: {
            name: game.equipe1.nom,
            displayName: game.equipe1.nom,
            trigramName: '',
            textColor: game.equipe1.colortext,
            shirtColor: game.equipe1.color1,
            strokeColor: game.equipe1.color2,
            coach: coach1,
            players: players1
          },
          teamGuest: {
            name: game.equipe2.nom,
            displayName: game.equipe2.nom,
            trigramName: '',
            textColor: game.equipe2.colortext,
            shirtColor: game.equipe2.color1,
            strokeColor: game.equipe2.color2,
            coach: coach2,
            players: players2
          }
        }
        this.sendMessage(id, JSON.stringify(setTeams), '/game/set-teams')
        this.printLog(id, '-setTeams OK (' + game.equipe1.nom + ' / ' + game.equipe2.nom + ')')
        this.saveConnection(id)
      } else {
        this.printLog(id, '-No game to load')
      }
    }
  },
  created () {
  },
  mounted () {
  }
}
