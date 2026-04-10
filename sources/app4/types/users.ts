export interface UserListItem {
  code: string
  identite: string
  mail: string
  tel: string
  fonction: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  idEvenement: string
  filtreJournee: string
  limitClubs: string
  mandateCount: number
}

export interface UserDetail extends UserListItem {
  club: string | null
  clubLabel: string | null
  dateDebut: string | null
  dateFin: string | null
}

export interface UserForm {
  code: string
  identite: string
  mail: string
  tel: string
  fonction: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  idEvenement: string
  filtreJournee: string
  limitClubs: string
  sendResetEmail: boolean
  includeDocLink: boolean
  complementaryMessage: string
}

export interface UsersResponse {
  items: UserListItem[]
  total: number
  page: number
  limit: number
  totalPages: number
}

// --- Mandats ---

export interface Mandate {
  id: number
  libelle: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  limitClubs: string
  filtreJournee: string
  idEvenement: string
}

export interface MandateForm {
  libelle: string
  niveau: number
  filtreSaison: string
  filtreCompetition: string
  limitClubs: string
  filtreJournee: string
  idEvenement: string
}

export interface MandateFilters {
  seasons: string[] | null
  competitions: string[] | null
  clubs: string[] | null
  journees: number[] | null
  events: number[] | null
}

export interface MandateSummary {
  id: number
  libelle: string
  niveau: number
  filters: MandateFilters
}

export interface AuthUser {
  id: string
  name: string
  firstname: string
  profile: number
  filters: MandateFilters
  mandates: MandateSummary[]
  activeMandate: { id: number; libelle: string } | null
  effectiveProfile: number
  effectiveFilters: MandateFilters
}
