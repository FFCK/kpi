import { defineStore } from 'pinia'
import type {
  ScoringMatch,
  ScoringPlayer,
  ScoringEvent,
  Penalty,
  Period,
  PeriodDurations
} from '~/types/scoring'

/**
 * Scoring store — live match console state.
 *
 * Phase 0: scaffold only (state + getters). Loading and the api2-backed mutating
 * actions (gameParam / gameEvent / gameTimer via ScoringController) are implemented
 * in Phase 1. See DOC/specs/PAGE_SCORING.md.
 */

/** Default period durations in seconds */
const DEFAULT_PERIOD_DURATIONS: PeriodDurations = {
  M1: 600,
  M2: 600,
  P1: 180,
  P2: 180,
  TB: 180
}

interface ScoringState {
  matchId: number | null
  match: ScoringMatch | null

  // Composition (both teams)
  playersA: ScoringPlayer[]
  playersB: ScoringPlayer[]

  // Live state
  events: ScoringEvent[]
  penalties: Penalty[]
  periodDurations: PeriodDurations

  // UI state
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
    }

    // Sorted events list, active penalties, etc. → added in Phase 1/2.
  }

  // actions (load, setPeriod, setStatus, addEvent, removeEvent, timer, penalties,
  // validate/lock) → implemented in Phase 1+.
})
