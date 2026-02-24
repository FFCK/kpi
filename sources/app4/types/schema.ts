// Types for competition schema page
import type { CompetitionLevel, CompetitionType } from './competitions'

export interface SchemaCompetition {
  code: string
  season: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  codeTypeclt: CompetitionType
  codeNiveau: CompetitionLevel
  codeRef: string
  titreActif: boolean
  qualifies: number
  elimines: number
}

export interface SchemaPhaseTeam {
  id: number
  libelle: string
  codeClub: string
  clt: number
  pts: number // already divided by 100
  j: number
  diff: number
}

export interface SchemaPoolTeam {
  id: number
  libelle: string
  tirage: number
}

export interface SchemaMatch {
  id: number
  numeroOrdre: number | null
  equipeA: string
  equipeB: string
  scoreA: string | null // null if not validated
  scoreB: string | null
  idEquipeA: number
  idEquipeB: number
}

export interface SchemaPhase {
  idJournee: number
  phase: string
  etape: number
  niveau: number
  type: 'C' | 'E'
  nbequipes: number
  dateDebut: string | null
  dateFin: string | null
  lieu: string | null
  departement: string | null
  nbMatchs: number
  startTime: string | null
  endTime: string | null
  ranking: SchemaPhaseTeam[] | null
  poolTeams: SchemaPoolTeam[] | null
  matches: SchemaMatch[]
}

export interface SchemaResponse {
  competition: SchemaCompetition
  stages: number
  totalMatches: number
  phases: SchemaPhase[]
}
