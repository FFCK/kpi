// Game from API list
export interface Game {
  id: number
  idJournee: number
  numeroOrdre: number | null
  dateMatch: string | null
  heureMatch: string | null
  libelle: string | null
  terrain: string | null
  publication: string // 'O' | 'N'
  validation: string // 'O' | 'N'
  statut: string // 'ATT' | 'ON' | 'END'
  type: string // 'C' | 'E'
  periode: string | null
  scoreA: string | null
  scoreB: string | null
  scoreDetailA: string | null
  scoreDetailB: string | null
  imprime: string // 'O' | 'N'
  coeffA: number
  coeffB: number
  idEquipeA: number | null
  equipeA: string | null
  idEquipeB: number | null
  equipeB: string | null
  arbitrePrincipal: string | null
  matricArbitrePrincipal: number
  arbitreSecondaire: string | null
  matricArbitreSecondaire: number
  codeCompetition: string
  phase: string | null
  niveau: number | null
  etape: number
  lieu: string | null
  libelleJournee: string | null
  soustitre2: string | null
  codeTypeclt: string | null
  authorized: boolean
}

// Games list API response
export interface GamesListResponse {
  games: Game[]
  total: number
  page: number
  totalPages: number
  phaseLibelle: boolean
  dates: string[]
}

// Form data for create/edit
export interface GameFormData {
  idJournee: number | null
  dateMatch: string
  heureMatch: string
  numeroOrdre: number | null
  terrain: string
  type: string
  intervalle: number
  libelle: string
  idEquipeA: number | null
  idEquipeB: number | null
  coeffA: number
  coeffB: number
  arbitrePrincipal: string
  matricArbitrePrincipal: number
  arbitreSecondaire: string
  matricArbitreSecondaire: number
}

// Journee item for filter/select dropdown
export interface GameJournee {
  id: number
  codeCompetition: string
  phase: string | null
  etape: number
  dateDebut: string | null
  lieu: string | null
  type: string
  codeTypeclt: string | null
}

// Team item for team select
export interface GameTeam {
  id: number
  libelle: string
  codeClub: string | null
}

// Event item for filter dropdown (reuse from gamedays)
export interface GameEvent {
  id: number
  libelle: string
  dateDebut: string | null
  dateFin: string | null
}
