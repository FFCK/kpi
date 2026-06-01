/**
 * Types for Scoring (live match console)
 *
 * Replaces the legacy "feuille de marque" (FeuilleMarque2/3.php) and the standalone
 * app3 prototype. See DOC/specs/PAGE_SCORING.md.
 *
 * Naming convention:
 * - "Scoring"          = manual KPI console (this module)
 * - "Hardware Scoring" = live data captured from hardware (BODET or equivalent), Phase 3
 */

/** Match period */
export type Period = 'M1' | 'M2' | 'P1' | 'P2' | 'TB'

/** Match status */
export type MatchStatus = 'ATT' | 'ON' | 'END'

/** Match type: C = classement (draw allowed), E = elimination (winner required) */
export type MatchType = 'C' | 'E'

/** Team side */
export type TeamSide = 'A' | 'B'

/**
 * Event code stored in kp_match_detail.Id_evt_match
 * B = but (goal), V/J/R = green/yellow/red card, D = red card "définitif"
 */
export type ScoringEventCode = 'B' | 'V' | 'J' | 'R' | 'D'

/**
 * Match header — shape returned by GET /admin/games/{id} (AdminGamesController::get).
 * Mirrors the camelCase payload; only the fields used by the scoring console are typed here.
 */
export interface ScoringMatch {
  id: number
  idJournee: number
  numeroOrdre: number | null
  dateMatch: string
  heureMatch: string
  libelle: string | null
  terrain: string
  validation: string // 'O' = locked, else unlocked
  statut: MatchStatus
  type: MatchType
  periode: Period | null
  scoreA: string | null
  scoreB: string | null
  scoreDetailA: string | null
  scoreDetailB: string | null
  idEquipeA: number | null
  equipeA: string | null
  idEquipeB: number | null
  equipeB: string | null
  codeCompetition: string | null
  phase: string | null
}

/**
 * Player in the match composition.
 * Sourced from GET /admin/matches/{id}/players?teamCode=A|B (same endpoint as presence).
 */
export interface ScoringPlayer {
  matric: number
  nom: string
  prenom: string
  numero: number
  capitaine: '-' | 'C' | 'E' // - = player, C = captain, E = coach
  team: TeamSide
}

/**
 * Match event (goal or card) stored in kp_match_detail.
 */
export interface ScoringEvent {
  uid?: string // unique id (auto-generated server-side if absent)
  code: ScoringEventCode
  period: Period
  tpsJeu: string // game time "MM:SS"
  team: TeamSide
  player: string // licence number ("0" for a team-level event)
  number: number | null
  reason: string // card reason code (motif), '' if none
}

/**
 * Penalty (exclusion) with countdown — UI/timer logic implemented in Phase 2.
 */
export interface Penalty {
  id: number
  team: TeamSide
  type: string // card type triggering the exclusion
  startTime: number // seconds, game-clock based
  duration: number // seconds
}

/** Period durations in seconds (defaults; M1/M2 = 10 min, P1/P2/TB = 3 min) */
export type PeriodDurations = Record<Period, number>
