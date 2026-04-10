/**
 * Types for Responsables de Compétition (RC) Management
 */

/**
 * Responsable de Compétition
 */
export interface Rc {
  id: number
  competitionCode: string | null // null = RC national sans compétition
  competitionLabel: string // "National (sans compétition)" when competitionCode is null
  season: string
  ordre: number
  matric: number
  nom: string
  prenom: string
  club: string
  email: string | null
}

/**
 * Liste des RC avec métadonnées
 */
export interface RcListResponse {
  items: Rc[]
  total: number
}

/**
 * Formulaire d'ajout/modification d'un RC
 */
export interface RcFormData {
  season: string
  competitionCode: string | null
  matric: number
  ordre: number
}

/**
 * Réponse de création d'un RC
 */
export interface RcCreateResponse {
  id: number
  message: string
}

/**
 * Réponse de mise à jour d'un RC
 */
export interface RcUpdateResponse {
  message: string
}

/**
 * Réponse de suppression de RC
 */
export interface RcDeleteResponse {
  deleted: number
  message: string
}

/**
 * Requête de suppression en masse
 */
export interface RcDeleteRequest {
  ids: number[]
}

/**
 * Paramètres de requête pour la liste des RC
 */
export interface RcListParams {
  season: string
  competitions?: string // Codes compétitions séparés par virgule
}

/**
 * Formulaire de copie des RC entre saisons
 */
export interface RcCopyFormData {
  sourceCode: string
  targetCode: string
}

/**
 * Réponse de copie des RC
 */
export interface RcCopyResponse {
  message: string
  copied: number
  skipped: number
}
