// Operations types for admin operations page

// Tab identifiers
export type OperationsTab =
  | 'images'
  | 'players'
  | 'teams'
  | 'codes'
  | 'import-export'
  | 'seasons'
  | 'system'

// Season type
export interface OperationsSeason {
  code: string
  active: boolean
  natDebut: string | null
  natFin: string | null
  interDebut: string | null
  interFin: string | null
}

// Competition for copy
export interface OperationsCompetition {
  code: string
  libelle: string
  typeClassement: string | null
  statut: string
}

// Player autocomplete result
export interface PlayerAutocomplete {
  matric: number
  nom: string
  prenom: string
  naissance: string | null
  numeroClub: string | null
  club: string | null
  label: string
}

// Team autocomplete result
export interface TeamAutocomplete {
  numero: number
  libelle: string
  codeClub: string | null
  club: string | null
  label: string
}

// Club autocomplete result
export interface ClubAutocomplete {
  numero: string
  nom: string
  departement: string | null
  label: string
}

// Image types for upload/rename
export type ImageType =
  | 'logo_competition'
  | 'bandeau_competition'
  | 'sponsor_competition'
  | 'logo_club'
  | 'logo_nation'

// Image upload params
export interface ImageUploadParams {
  imageType: ImageType
  codeCompetition?: string
  saison?: string
  numeroClub?: string
  codeNation?: string
}

// Image upload result
export interface ImageUploadResult {
  message: string
  filename: string
  originalSize?: string
  newSize?: string
  resized: boolean
}

// Player merge request
export interface PlayerMergeRequest {
  sourceMatric: number
  targetMatric: number
}

// Auto-merge result
export interface AutoMergeResult {
  count: number
  message: string
  details: AutoMergeDetail[]
}

export interface AutoMergeDetail {
  source: number
  target: number
  name?: string
  club?: string
  error?: string
}

// Team operations
export interface TeamRenameRequest {
  teamId: number
  newName: string
}

export interface TeamMergeRequest {
  sourceId: number
  targetId: number
}

export interface TeamMoveRequest {
  teamId: number
  clubCode: string
}

// Code change request
export interface CodeChangeRequest {
  sourceCode: string
  targetCode: string
  allSeasons: boolean
  targetExists: boolean
}

// Season add request
export interface SeasonAddRequest {
  code: string
  natDebut?: string
  natFin?: string
  interDebut?: string
  interFin?: string
}

// Copy RC result
export interface CopyRcResult {
  message: string
  copied: number
  skipped: number
}

// Copy competitions request
export interface CopyCompetitionsRequest {
  sourceCode: string
  targetCode: string
  competitionCodes: string[]
  copyMatches: boolean
}

// Copy competitions result
export interface CopyCompetitionsResult {
  message: string
  copied: number
  skipped: number
  journeesCopied: number
  matchesCopied: number
  details: CopyCompetitionDetail[]
}

export interface CopyCompetitionDetail {
  code: string
  status: 'copied' | 'skipped'
  reason?: string
  journees?: number
  matches?: number
}

// Cache purge result
export interface CachePurgeResult {
  message: string
  filesRead: number
  matchFilesDeleted: number
  eventFilesDeleted: number
}

// Operation result (generic for UI feedback)
export interface OperationResult {
  success: boolean
  message: string
  details?: string[]
  data?: unknown
}

// Event for export dropdown
export interface OperationsEvent {
  id: number
  libelle: string
  lieu: string | null
  dateDebut: string | null
  dateFin: string | null
}
