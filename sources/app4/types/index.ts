// User filters (from JWT)
export interface UserFilters {
  seasons: string[] | null
  competitions: string[] | null
  events: number[] | null
  journees: number[] | null
  clubs: string[] | null
}

// User type
export interface User {
  id: string
  name: string
  firstname: string
  profile: number
  token: string
  filters?: UserFilters
}

// Season from filters API
export interface Season {
  code: string
  active: boolean
}

// Competition from filters API
export interface Competition {
  code: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  titreActif: boolean
  enActif: boolean
  codeTypeclt: string | null
  codeRef: string | null
}

// Competition group (by section)
export interface CompetitionGroup {
  section: number
  sectionLabel: string
  competitions: Competition[]
}

// Filter event
export interface FilterEvent {
  id: number
  libelle: string
  dateDebut: string | null
  dateFin: string | null
  publication: boolean
}

// Auth response from API
export interface AuthResponse {
  token: string
  user: User
}

// Event type (from API)
export interface Event {
  id: number
  libelle: string
  lieu: string | null
  dateDebut: string | null
  dateFin: string | null
  publication: boolean
  app: boolean
}

// Event form data (for create/update)
export interface EventFormData {
  libelle: string
  lieu: string
  dateDebut: string
  dateFin: string
}

// Paginated response
export interface PaginatedResponse<T> {
  items: T[]
  total: number
  page: number
  limit: number
  totalPages: number
}

// API error response
export interface ApiError {
  message: string
  code?: string
  errors?: Record<string, string[]>
}

// Player autocomplete result (from /admin/operations/autocomplete/players)
export interface PlayerAutocomplete {
  matric: number
  nom: string
  prenom: string
  naissance: string | null
  numeroClub: string | null
  club: string | null
  label: string // Format: "12345 - DUPONT Jean (3512 - Club Name)"
}
