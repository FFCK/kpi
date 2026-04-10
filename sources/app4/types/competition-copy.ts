// Types for competition copy page

/** Search filters for schema search */
export interface SchemaSearchFilters {
  nbEquipes: number
  typeCompetition: 'CHPT' | 'CP' | ''
  tri: 'saison' | 'matchs'
}

/** Schema search result */
export interface SchemaSearchResult {
  code: string
  season: string
  codeNiveau: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  titreActif: boolean
  codeTypeclt: 'CHPT' | 'CP'
  codeTour: string | null
  nbEquipes: number
  qualifies: number
  elimines: number
  commentaires: string | null
  nbMatchs: number
  nbTerrains: number
  nbTours: number
  nbPhases: number
  matchsEncodes: boolean
}

/** Competition copy detail (origin) */
export interface CompetitionCopyDetail {
  code: string
  season: string
  codeTypeclt: 'CHPT' | 'CP'
  nbEquipes: number
  qualifies: number
  elimines: number
  nbMatchs: number
  soustitre: string | null
  soustitre2: string | null
  commentaires: string | null
  journees: CompetitionCopyJournee[]
  prefill: CompetitionCopyPrefill
}

/** Journee in copy detail */
export interface CompetitionCopyJournee {
  id: number
  phase: string
  niveau: number
  lieu: string | null
}

/** Prefill data from first journee */
export interface CompetitionCopyPrefill {
  dateDebut: string | null
  dateFin: string | null
  nom: string | null
  libelle: string | null
  lieu: string | null
  planEau: string | null
  departement: string | null
  responsableInsc: string | null
  responsableR1: string | null
  organisateur: string | null
  delegue: string | null
}

/** Competition option for destination dropdown */
export interface CompetitionOption {
  code: string
  libelle: string
  codeTypeclt: string
  nbEquipes: number
  qualifies: number
  elimines: number
}

/** Competition option group (by section) */
export interface CompetitionOptionGroup {
  label: string
  options: CompetitionOption[]
}

/** Copy payload */
export interface CopyCompetitionPayload {
  originSeason: string
  originCompetition: string
  destinationSeason: string
  destinationCompetition: string
  dateDebut: string | null
  dateFin: string | null
  nom: string | null
  libelle: string | null
  lieu: string | null
  planEau: string | null
  departement: string | null
  responsableInsc: string | null
  responsableR1: string | null
  organisateur: string | null
  delegue: string | null
  initPremierTour: boolean
}

/** Copy response */
export interface CopyCompetitionResponse {
  success: boolean
  journeesCreated: number
  matchsCreated: number
}
