import { defineStore } from 'pinia'
import type {
  ScoringMatch,
  ScoringPlayer,
  ScoringEvent,
  Penalty,
  Period,
  MatchStatus,
  TeamSide,
  PeriodDurations
} from '~/types/scoring'

/**
 * Scoring store — live match console state (Phase 1: online, api2-backed).
 *
 * Loads a match via GET /admin/games/{id} and its players via
 * GET /admin/matches/{id}/players?teamCode=A|B (same endpoint as presence).
 * Mutating actions POST to api2 ScoringController (/admin/scoring/*) with optimistic
 * update + rollback on error. See DOC/specs/PAGE_SCORING.md.
 */

/** Default period durations in seconds */
const DEFAULT_PERIOD_DURATIONS: PeriodDurations = {
  M1: 600,
  M2: 600,
  P1: 180,
  P2: 180,
  TB: 180
}

/** Response shape of GET /admin/matches/{id}/players (subset we use) */
interface MatchPlayersResponse {
  players: Array<{
    matric: number
    nom: string
    prenom: string
    numero: number
    capitaine: '-' | 'C' | 'E'
  }>
}

interface ScoringState {
  matchId: number | null
  match: ScoringMatch | null
  playersA: ScoringPlayer[]
  playersB: ScoringPlayer[]
  events: ScoringEvent[]
  penalties: Penalty[]
  periodDurations: PeriodDurations
  loading: boolean
  initialized: boolean
}

export const useScoringStore = defineStore('scoring', {
  state: (): ScoringState => ({
    matchId: null,
    match: null,
    playersA: [],
    playersB: [],
    events: [],
    penalties: [],
    periodDurations: { ...DEFAULT_PERIOD_DURATIONS },
    loading: false,
    initialized: false
  }),

  getters: {
    hasMatch: (state): boolean => state.match !== null,

    /** Locked when the match is validated (Validation === 'O') */
    isLocked: (state): boolean => state.match?.validation === 'O',

    /** Duration (seconds) of the currently selected period */
    currentPeriodDuration: (state): number => {
      const p = state.match?.periode as Period | null | undefined
      return p ? state.periodDurations[p] : state.periodDurations.M1
    },

    scoreA: (state): number => Number(state.match?.scoreA ?? 0),
    scoreB: (state): number => Number(state.match?.scoreB ?? 0)
  },

  actions: {
    /** Load match header + both team rosters */
    async load(matchId: number, apiInstance?: ReturnType<typeof useApi>) {
      const api = apiInstance ?? useApi()
      this.matchId = matchId
      this.loading = true
      this.initialized = false

      try {
        const match = await api.get<ScoringMatch>(`/admin/games/${matchId}`)
        const [resA, resB] = await Promise.all([
          api.get<MatchPlayersResponse>(`/admin/matches/${matchId}/players`, { teamCode: 'A' }),
          api.get<MatchPlayersResponse>(`/admin/matches/${matchId}/players`, { teamCode: 'B' })
        ])

        this.match = match
        this.playersA = resA.players.map(p => ({ ...p, team: 'A' as TeamSide }))
        this.playersB = resB.players.map(p => ({ ...p, team: 'B' as TeamSide }))
        this.initialized = true
      } catch (error) {
        console.error('Failed to load scoring match:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /** Update a match parameter (score/status/period) via api2, optimistic + rollback */
    async setParam(param: string, value: string, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.match) return
      const api = apiInstance ?? useApi()
      const previous = (this.match as Record<string, unknown>)[
        param.charAt(0).toLowerCase() + param.slice(1)
      ]
      // Optimistic local update for the mapped field
      this.applyParamLocally(param, value)
      try {
        await api.put(`/admin/scoring/gameParam/${this.match.id}`, { param, value })
      } catch (error) {
        // rollback
        if (previous !== undefined) this.applyParamLocally(param, String(previous))
        throw error
      }
    },

    /** Maps a backend param name to the local ScoringMatch field and assigns it */
    applyParamLocally(param: string, value: string) {
      if (!this.match) return
      switch (param) {
        case 'ScoreA': this.match.scoreA = value; break
        case 'ScoreB': this.match.scoreB = value; break
        case 'Statut': this.match.statut = value as MatchStatus; break
        case 'Periode': this.match.periode = value as Period; break
      }
    },

    setStatus(status: MatchStatus, api?: ReturnType<typeof useApi>) {
      return this.setParam('Statut', status, api)
    },

    setPeriod(period: Period, api?: ReturnType<typeof useApi>) {
      return this.setParam('Periode', period, api)
    },

    /** Add a match event (goal/card); score auto-incremented for goals */
    async addEvent(event: ScoringEvent, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.match) return
      const api = apiInstance ?? useApi()
      this.events.push(event)
      const wasGoal = event.code === 'B'
      const scoreField = event.team === 'A' ? 'scoreA' : 'scoreB'
      const prevScore = this.match[scoreField]
      if (wasGoal) {
        this.match[scoreField] = String(Number(prevScore ?? 0) + 1)
      }
      try {
        await api.put(`/admin/scoring/gameEvent/${this.match.id}`, {
          params: {
            action: 'add',
            uid: event.uid,
            period: event.period,
            tpsJeu: event.tpsJeu,
            code: event.code,
            player: event.player,
            number: event.number,
            team: event.team,
            reason: event.reason
          }
        })
        // Persist score increment server-side too
        if (wasGoal) {
          await this.setParam(event.team === 'A' ? 'ScoreA' : 'ScoreB', this.match[scoreField] ?? '0', api)
        }
      } catch (error) {
        // rollback
        this.events.pop()
        if (wasGoal) this.match[scoreField] = prevScore
        throw error
      }
    },

    /** Remove the last matching event (period/player/code) */
    async removeEvent(event: ScoringEvent, apiInstance?: ReturnType<typeof useApi>) {
      if (!this.match) return
      const api = apiInstance ?? useApi()
      const idx = this.events.findIndex(
        e => e.period === event.period && e.player === event.player && e.code === event.code
      )
      const removed = idx >= 0 ? this.events.splice(idx, 1)[0] : null
      const wasGoal = event.code === 'B'
      const scoreField = event.team === 'A' ? 'scoreA' : 'scoreB'
      const prevScore = this.match[scoreField]
      if (wasGoal) {
        this.match[scoreField] = String(Math.max(0, Number(prevScore ?? 0) - 1))
      }
      try {
        await api.put(`/admin/scoring/gameEvent/${this.match.id}`, {
          params: {
            action: 'remove',
            period: event.period,
            player: event.player,
            code: event.code
          }
        })
        if (wasGoal) {
          await this.setParam(event.team === 'A' ? 'ScoreA' : 'ScoreB', this.match[scoreField] ?? '0', api)
        }
      } catch (error) {
        if (removed) this.events.splice(idx, 0, removed)
        if (wasGoal) this.match[scoreField] = prevScore
        throw error
      }
    },

    /** Control the match timer (run/stop/RAZ) — persisted to kp_chrono */
    async setTimer(
      action: 'run' | 'stop' | 'RAZ',
      params: { startTime?: number; runTime?: number; maxTime?: number } = {},
      apiInstance?: ReturnType<typeof useApi>
    ) {
      if (!this.match) return
      const api = apiInstance ?? useApi()
      await api.put(`/admin/scoring/gameTimer/${this.match.id}`, { params: { action, ...params } })
    },

    /** Validate / lock toggle (reuses AdminGames endpoint) */
    async toggleValidation(apiInstance?: ReturnType<typeof useApi>) {
      if (!this.match) return
      const api = apiInstance ?? useApi()
      const res = await api.patch<{ validation: string }>(
        `/admin/games/${this.match.id}/validation`
      )
      this.match.validation = res.validation
    }
  }
})
