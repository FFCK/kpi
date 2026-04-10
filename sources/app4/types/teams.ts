// Teams types for admin teams page

import type { CompetitionLevel, CompetitionType, CompetitionStatus } from './competitions'

// Team in a competition (from GET /admin/competition-teams)
export interface CompetitionTeam {
  id: number
  libelle: string
  codeClub: string
  clubLibelle: string
  numero: number
  poule: string
  tirage: number
  logo: string | null
  color1: string | null
  color2: string | null
  colortext: string | null
  nbMatchs: number
}

// Competition info returned with teams list
export interface CompetitionTeamInfo {
  code: string
  libelle: string
  codeNiveau: CompetitionLevel
  codeTypeclt: CompetitionType
  statut: CompetitionStatus
  verrou: boolean
}

// Response from GET /admin/competition-teams
export interface CompetitionTeamsResponse {
  teams: CompetitionTeam[]
  competition: CompetitionTeamInfo
  total: number
}

// Historical team (from GET /admin/teams/search)
export interface HistoricalTeam {
  numero: number
  libelle: string
  codeClub: string
  clubLibelle: string
  international: boolean
}

// Team composition for copy (from GET /admin/teams/{numero}/compositions)
export interface TeamComposition {
  season: string
  competition: string
  competitionLibelle: string
  playerCount: number
}

// Form data for adding teams
export interface TeamAddFormData {
  mode: 'manual' | 'history'
  // Manual mode
  libelle: string
  codeClub: string
  // History mode
  teamNumbers: number[]
  // Common
  poule: string
  tirage: number
  // Copy composition
  copyComposition: {
    season: string
    competition: string
  } | null
}

// Form data for editing team colors/logo
export interface TeamColorsFormData {
  logo: string
  color1: string
  color2: string
  colortext: string
  propagateNext: boolean
  propagatePrevious: boolean
  propagateClub: boolean
}

// Form data for duplication
export interface DuplicateFormData {
  sourceCompetition: string
  sourceSeason: string
  mode: 'append' | 'replace'
  copyPlayers: boolean
}

// Regional committee (for club filters)
export interface RegionalCommittee {
  code: string
  libelle: string
}

// Departmental committee (for club filters)
export interface DepartmentalCommittee {
  code: string
  libelle: string
  codeComiteReg: string
}

// Club (for club selection)
export interface Club {
  code: string
  libelle: string
  codeComiteDep: string
}
