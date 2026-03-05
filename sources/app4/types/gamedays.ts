// Gameday from API list
export interface Gameday {
  id: number
  codeCompetition: string
  codeSaison: string
  phase: string | null
  niveau: number | null
  etape: number
  nbEquipes: number
  type: 'C' | 'E'
  dateDebut: string | null
  dateFin: string | null
  nom: string | null
  libelle: string | null
  lieu: string | null
  departement: string | null
  planEau: string | null
  organisateur: string | null
  responsableInsc: string | null
  responsableR1: string | null
  delegue: string | null
  chefArbitre: string | null
  repAthletes: string | null
  arbNj1: string | null
  arbNj2: string | null
  arbNj3: string | null
  arbNj4: string | null
  arbNj5: string | null
  publication: boolean
  matchCount: number
  authorized: boolean
  competitionLibelle: string | null
  competitionTypeClt: string | null
}

// Form data for create/edit
export interface GamedayFormData {
  codeCompetition: string
  codeSaison: string
  phase: string
  niveau: number
  etape: number
  nbEquipes: number
  type: 'C' | 'E'
  dateDebut: string
  dateFin: string
  nom: string
  libelle: string
  lieu: string
  departement: string
  planEau: string
  organisateur: string
  codeOrganisateur: string
  responsableInsc: string
  responsableR1: string
  delegue: string
  chefArbitre: string
  repAthletes: string
  arbNj1: string
  arbNj2: string
  arbNj3: string
  arbNj4: string
  arbNj5: string
}

// Bulk calendar update form
export interface GamedayBulkCalendarData {
  ids: number[]
  nom: string
  dateDebut: string
  dateFin: string
  lieu: string
  departement: string
}

// Bulk officials + calendar copy
export interface GamedayBulkOfficialsData {
  sourceId: number
  ids: number[]
}

// Event item for filter dropdown
export interface GamedayEvent {
  id: number
  libelle: string
  dateDebut: string | null
  dateFin: string | null
}
