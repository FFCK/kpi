/**
 * Types for Presence Sheet Management (Unified Team & Match modes)
 */

/**
 * Player in team composition or match composition
 */
export interface Player {
  // Identity
  matric: number
  nom: string
  prenom: string
  sexe: 'M' | 'F'
  categ: string
  naissance: string | null

  // Team/Match composition
  numero: number
  capitaine: '-' | 'C' | 'E' | 'A' | 'X' // Team: all | Match: -, C, E only

  // License info
  origine: string // season of license
  numeroClub: string
  clubLibelle: string

  // Pagaie (paddle)
  pagaieECA: string // PAGR, PAGN, PAGBL, PAGV, PAGJ, PAGB
  pagaieEVI: string
  pagaieMER: string
  pagaieLabel: string // Combined: "Rouge", "Noire", etc.
  pagaieValide: number // 0=invalid, 1=ECA, 2=EVI, 3=MER

  // Certificates
  certifCK: 'OUI' | 'NON'
  certifAPS: 'OUI' | 'NON'
  dateCertifCK: string | null
  dateCertifAPS: string | null

  // Referee
  arbitre: string // '', 'REG', 'IR', 'NAT', 'INT', 'OTM', 'JO'
  niveau: string // referee level

  // Surclassement (age overclassing)
  dateSurclassement: string | null

  // ICF number (international)
  icf: number | null
}

/**
 * Team information
 */
export interface TeamInfo {
  id: number
  libelle: string
  numero: number
  codeCompet: string
  codeSaison: string
  codeClub: string
  clubLibelle: string
  poule: string
  tirage: number
  logo: string | null
}

/**
 * Competition information
 */
export interface CompetitionInfo {
  code: string
  libelle: string
  verrou: boolean
  codeNiveau: string
  statut: string
}

/**
 * Match information
 */
export interface MatchInfo {
  id: number
  idJournee: number
  dateMatch: string
  heureMatch: string
  terrain: string
  numeroOrdre: number
  validation: boolean
  libelle: string
}

/**
 * Last update info
 */
export interface LastUpdateInfo {
  date: string
  user: string
  action: string
}

/**
 * Team Mode: List players response
 */
export interface TeamPlayersResponse {
  team: TeamInfo
  competition: CompetitionInfo
  players: Player[]
  lastUpdate?: LastUpdateInfo
}

/**
 * Match Mode: List players response
 */
export interface MatchPlayersResponse {
  match: MatchInfo
  team: TeamInfo
  competition: CompetitionInfo
  players: Player[]
}

/**
 * Add player form (Team Mode)
 */
export interface AddPlayerFormData {
  mode: 'existing' | 'create'

  // Existing player
  matric?: number

  // Create new (Matric >= 2000000)
  nom?: string
  prenom?: string
  sexe?: 'M' | 'F'
  naissance?: string // YYYY-MM-DD

  // Common
  numero?: number
  capitaine?: '-' | 'C' | 'E' | 'A' | 'X'

  // Optional referee
  arbitre?: '' | 'REG' | 'IR' | 'NAT' | 'INT' | 'OTM' | 'JO'
  niveau?: string

  // Optional ICF
  numicf?: number
}

/**
 * Match Mode: Add player form
 */
export interface MatchAddPlayerFormData {
  matric: number
  numero?: number
  capitaine?: '-' | 'C' | 'E'
}

/**
 * Copy composition form (Team Mode)
 */
export interface CopyCompositionFormData {
  sourceCompetition: string
  sourceSeason: string
}

/**
 * Available composition for copy
 */
export interface AvailableComposition {
  competitionCode: string
  competitionLibelle: string
  season: string
  teamId: number
  playerCount: number
}

/**
 * Copy to matches form (Match Mode)
 */
export interface CopyToMatchesFormData {
  scope: 'day' | 'competition'
  selectedMatchIds?: number[]
}

/**
 * Copyable match info
 */
export interface CopyableMatch {
  id: number
  dateMatch: string
  heureMatch: string
  terrain: string
  numeroOrdre: number
  equipeA: string
  equipeB: string
  playerCount: number
}

/**
 * Page mode: team or match
 */
export type PresenceMode = 'team' | 'match'

/**
 * Page state
 */
export interface PresencePageState {
  mode: PresenceMode

  // Team mode
  teamId?: number

  // Match mode
  matchId?: number
  teamCode?: 'A' | 'B'

  // Context info
  team: TeamInfo | null
  competition: CompetitionInfo | null
  match?: MatchInfo

  // Lock status (Verrou for team, Validation for match)
  isLocked: boolean

  // Players
  players: Player[]

  // UI state
  loading: boolean
  selectedPlayerIds: number[]
  editingCell: { matric: number; field: 'numero' | 'capitaine' } | null
  editingValue: string
}

/**
 * Inline edit cell data
 */
export interface InlineEditCell {
  matric: number
  field: 'numero' | 'capitaine'
}
