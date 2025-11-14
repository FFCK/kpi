import { defineStore } from 'pinia'
import type { Match, Player, MatchEvent, Penalty } from '~/utils/db'
import db from '~/utils/db'
import { v4 as uuidv4 } from 'uuid'

export const useMatchStore = defineStore('match', {
  state: () => ({
    currentMatch: null as Match | null,
    matches: [] as Match[],
    penalties: [] as Penalty[],
    timerRunning: false,
    timerValue: 600, // 10 minutes in seconds
    shotclockValue: 60, // 60 seconds
    shotclockRunning: false,
    periodDurations: {
      M1: 600, // 10 minutes
      M2: 600, // 10 minutes
      P1: 180, // 3 minutes
      P2: 180, // 3 minutes
      TB: 180  // 3 minutes
    } as Record<string, number>
  }),

  getters: {
    hasMatch: (state) => state.currentMatch !== null,

    isLocked: (state) => state.currentMatch?.locked ?? false,

    teamAPlayers: (state) => state.currentMatch?.playersA.filter(p => p.status !== 'E') ?? [],

    teamBPlayers: (state) => state.currentMatch?.playersB.filter(p => p.status !== 'E') ?? [],

    teamACoaches: (state) => state.currentMatch?.playersA.filter(p => p.status === 'E') ?? [],

    teamBCoaches: (state) => state.currentMatch?.playersB.filter(p => p.status === 'E') ?? [],

    matchEvents: (state) => {
      if (!state.currentMatch) return []
      return [...state.currentMatch.events].sort((a, b) => {
        // Sort by period (reverse), then by time
        if (a.period !== b.period) {
          const periods = ['M1', 'M2', 'P1', 'P2', 'TB']
          return periods.indexOf(b.period) - periods.indexOf(a.period)
        }
        // Convert time to seconds for comparison
        const timeToSeconds = (time: string) => {
          const [min, sec] = time.split(':').map(Number)
          return min * 60 + sec
        }
        return timeToSeconds(a.time) - timeToSeconds(b.time)
      })
    },

    activePenalties: (state) => {
      const now = Date.now()
      return state.penalties.filter(p => {
        const elapsed = (now - p.startTime) / 1000
        return elapsed < p.duration
      })
    },

    currentPeriodDuration: (state) => {
      if (!state.currentMatch) return 600
      return state.periodDurations[state.currentMatch.period] || 600
    }
  },

  actions: {
    // Create new match
    createMatch(data: Partial<Match>) {
      this.currentMatch = {
        teamA: data.teamA || '',
        teamB: data.teamB || '',
        scoreA: 0,
        scoreB: 0,
        status: 'ATT',
        period: 'M1',
        type: data.type || 'C',
        date: data.date || new Date().toISOString().split('T')[0],
        time: data.time || new Date().toTimeString().split(' ')[0].substring(0, 5),
        field: data.field || '',
        playersA: [],
        playersB: [],
        officials: data.officials || {},
        events: [],
        locked: false,
        published: false,
        timestamp: Date.now(),
        ...data
      }
      this.timerValue = this.currentPeriodDuration
    },

    // Load match from backend or local DB
    async loadMatch(matchId: number) {
      try {
        // Try to load from local DB first
        const localMatch = await db.matches.where('matchId').equals(matchId).first()
        if (localMatch) {
          this.currentMatch = localMatch
          this.timerValue = this.currentPeriodDuration
          return localMatch
        }

        // If not found locally, load from API
        const config = useRuntimeConfig()
        const response = await fetch(`${config.public.apiBaseUrl}/match/${matchId}`)

        if (!response.ok) {
          throw new Error('Match not found')
        }

        const data = await response.json()
        this.currentMatch = this.parseBackendMatch(data)
        await this.saveMatchToLocal()

        return this.currentMatch
      } catch (error) {
        console.error('Error loading match:', error)
        throw error
      }
    },

    // Parse backend match data to local format
    parseBackendMatch(data: any): Match {
      return {
        matchId: data.Id,
        teamA: data.equipeA || '',
        teamB: data.equipeB || '',
        scoreA: parseInt(data.ScoreA) || 0,
        scoreB: parseInt(data.ScoreB) || 0,
        status: data.Statut || 'ATT',
        period: data.Periode || 'M1',
        type: data.Type || 'C',
        date: data.Date_match || '',
        time: data.Heure_match || '',
        field: data.Terrain || '',
        playersA: data.playersA || [],
        playersB: data.playersB || [],
        officials: data.officials || {},
        events: data.events || [],
        locked: data.Validation === 'O',
        published: data.Publication === 'O',
        endTime: data.Heure_fin || '',
        comments: data.Commentaires_officiels || '',
        timestamp: Date.now(),
        eventId: data.eventId,
        websocketConfig: data.websocketConfig
      }
    },

    // Save match to local DB
    async saveMatchToLocal() {
      if (!this.currentMatch) return

      try {
        if (this.currentMatch.id) {
          await db.matches.update(this.currentMatch.id, this.currentMatch)
        } else {
          const id = await db.matches.add(this.currentMatch)
          this.currentMatch.id = id as number
        }
      } catch (error) {
        console.error('Error saving match:', error)
      }
    },

    // Add player to team
    addPlayer(team: 'A' | 'B', player: Partial<Player>) {
      if (!this.currentMatch) return

      const newPlayer: Player = {
        id: player.id || uuidv4(),
        number: player.number || 0,
        firstName: player.firstName || '',
        lastName: player.lastName || '',
        status: player.status || 'J',
        matric: player.matric,
        license: player.license,
        birthdate: player.birthdate,
        category: player.category
      }

      const players = team === 'A' ? this.currentMatch.playersA : this.currentMatch.playersB
      const isCoach = newPlayer.status === 'E'
      const currentCount = players.filter(p => p.status === newPlayer.status).length

      if (isCoach && currentCount >= 3) {
        throw new Error('Maximum 3 coaches per team')
      }
      if (!isCoach && currentCount >= 10) {
        throw new Error('Maximum 10 players per team')
      }

      players.push(newPlayer)
      this.saveMatchToLocal()
    },

    // Remove player
    removePlayer(team: 'A' | 'B', playerId: string) {
      if (!this.currentMatch) return

      const players = team === 'A' ? this.currentMatch.playersA : this.currentMatch.playersB
      const index = players.findIndex(p => p.id === playerId)

      if (index !== -1) {
        players.splice(index, 1)
        this.saveMatchToLocal()
      }
    },

    // Update match status
    setStatus(status: 'ATT' | 'ON' | 'END') {
      if (!this.currentMatch || this.currentMatch.locked) return

      this.currentMatch.status = status
      this.saveMatchToLocal()
    },

    // Update period
    setPeriod(period: 'M1' | 'M2' | 'P1' | 'P2' | 'TB') {
      if (!this.currentMatch || this.currentMatch.locked) return

      this.currentMatch.period = period
      this.timerValue = this.currentPeriodDuration
      this.saveMatchToLocal()
    },

    // Add event
    addEvent(event: Omit<MatchEvent, 'id' | 'timestamp'>) {
      if (!this.currentMatch || this.currentMatch.locked) return

      const newEvent: MatchEvent = {
        ...event,
        timestamp: Date.now()
      }

      this.currentMatch.events.push(newEvent)

      // Update score if it's a goal
      if (event.eventType === 'B') {
        if (event.team === 'A') {
          this.currentMatch.scoreA++
        } else {
          this.currentMatch.scoreB++
        }
      }

      this.saveMatchToLocal()
      return newEvent
    },

    // Remove event
    removeEvent(eventIndex: number) {
      if (!this.currentMatch || this.currentMatch.locked) return

      const event = this.currentMatch.events[eventIndex]
      if (event && event.eventType === 'B') {
        // Recalculate score
        if (event.team === 'A') {
          this.currentMatch.scoreA--
        } else {
          this.currentMatch.scoreB--
        }
      }

      this.currentMatch.events.splice(eventIndex, 1)
      this.saveMatchToLocal()
    },

    // Lock/unlock match
    toggleLock() {
      if (!this.currentMatch) return
      this.currentMatch.locked = !this.currentMatch.locked
      this.saveMatchToLocal()
    },

    // Toggle publish
    togglePublish() {
      if (!this.currentMatch) return
      this.currentMatch.published = !this.currentMatch.published
      this.saveMatchToLocal()
    },

    // Timer control
    startTimer() {
      this.timerRunning = true
      this.shotclockRunning = true
    },

    pauseTimer() {
      this.timerRunning = false
      this.shotclockRunning = false
    },

    resetTimer() {
      this.timerValue = this.currentPeriodDuration
      this.timerRunning = false
    },

    resetShotclock() {
      this.shotclockValue = 60
    },

    // Penalty management
    addPenalty(penalty: Omit<Penalty, 'id' | 'startTime'>) {
      const newPenalty: Penalty = {
        id: uuidv4(),
        startTime: Date.now(),
        ...penalty
      }
      this.penalties.push(newPenalty)
    },

    removePenalty(penaltyId: string) {
      const index = this.penalties.findIndex(p => p.id === penaltyId)
      if (index !== -1) {
        this.penalties.splice(index, 1)
      }
    },

    // Clear current match
    clearMatch() {
      this.currentMatch = null
      this.penalties = []
      this.timerRunning = false
      this.shotclockRunning = false
      this.timerValue = 600
      this.shotclockValue = 60
    }
  }
})
