// Types for rankings page
import type { CompetitionLevel, CompetitionType, CompetitionStatus, RankingStructureType } from './competitions'

// Competition info returned by ranking endpoints
export interface RankingCompetitionInfo {
  code: string
  codeSaison: string
  libelle: string
  codeTypeclt: CompetitionType
  codeNiveau: CompetitionLevel
  statut: CompetitionStatus
  qualifies: number
  elimines: number
  points: string // ex: "4-2-1-0"
  goalaverage: string // 'gen' = général, 'part' = particulier
  rankingStructureType: RankingStructureType | null
  dateCalcul: string | null
  modeCalcul: string | null // 'tous' | 'verr' | null
  codeUtiCalcul: string
  userNameCalcul: string
  datePublication: string | null
  datePublicationCalcul: string | null
  codeUtiPublication: string
  userNamePublication: string
  modePublicationCalcul: string | null
}

// Ranking type selector option
export interface RankingTypeOption {
  code: CompetitionType
  label: string
  selected: boolean
}

// Team in the general ranking table
export interface RankingTeam {
  id: number
  libelle: string
  codeClub: string
  logo: string
  codeComiteDep: string
  // Computed ranking
  clt: number
  pts: number // × 100
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
  ptsNiveau: number
  cltNiveau: number
  // Published ranking
  cltPubli: number
  ptsPubli: number
  jPubli: number
  gPubli: number
  nPubli: number
  pPubli: number
  fPubli: number
  plusPubli: number
  moinsPubli: number
  diffPubli: number
  ptsNiveauPubli: number
  cltNiveauPubli: number
}

// Team in a phase ranking table
export interface RankingPhaseTeam {
  id: number
  libelle: string
  // Computed
  clt: number
  pts: number
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
  // Published
  cltPubli: number
  ptsPubli: number
  jPubli: number
  gPubli: number
  nPubli: number
  pPubli: number
  fPubli: number
  plusPubli: number
  moinsPubli: number
  diffPubli: number
}

// Match in an elimination phase
export interface RankingPhaseMatch {
  id: number
  equipeA: string
  equipeB: string
  idEquipeA: number
  idEquipeB: number
  scoreA: number | null
  scoreB: number | null
}

// Phase in the déroulement (CP only)
export interface RankingPhase {
  idJournee: number
  phase: string
  lieu: string
  type: 'C' | 'E' // C = classement, E = élimination
  niveau: number
  consolidation: boolean
  teams: RankingPhaseTeam[]
  matches?: RankingPhaseMatch[] // Only for type 'E'
}

// Full ranking API response
export interface RankingResponse {
  competition: RankingCompetitionInfo
  types: RankingTypeOption[]
  ranking: RankingTeam[]
  phases: RankingPhase[]
}

// Initial ranking team
export interface InitialRankingTeam {
  id: number
  libelle: string
  clt: number
  pts: number // real value (not × 100)
  j: number
  g: number
  n: number
  p: number
  f: number
  plus: number
  moins: number
  diff: number
}

// Initial ranking response
export interface InitialRankingResponse {
  competition: string
  season: string
  teams: InitialRankingTeam[]
}

// Transfer request
export interface TransferRequest {
  teamIds: number[]
  targetSeason: string
  targetCompetition: string
}

// Transfer result
export interface TransferResult {
  transferred: number
  skipped: number
  details: TransferDetail[]
}

export interface TransferDetail {
  teamId: number
  libelle: string
  status: 'created' | 'skipped'
  newId?: number
}

// Transfer competition option
export interface TransferCompetition {
  code: string
  libelle: string
}
