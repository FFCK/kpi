// User type
export interface User {
  id: string
  name: string
  firstname: string
  profile: number
  token: string
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
