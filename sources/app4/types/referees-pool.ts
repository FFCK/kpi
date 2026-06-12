/**
 * Types for the global referees pool management
 * (kp_competition_equipe Code_compet='POOL' / Code_saison='1000')
 */

/** Arbitration code as stored / sent to the API. */
export type ArbitrationCode = '' | 'REG' | 'IR' | 'NAT' | 'INT' | 'OTM' | 'JO'

/** Pool membership status (stored in Capitaine): active referee or inactive. */
export type PoolStatus = 'A' | 'X'

export interface PoolReferee {
  matric: number
  nom: string
  prenom: string
  sexe: string
  categ: string
  /** true when matric < 2_000_000 (federation licence) → read-only here. */
  licensed: boolean
  /** 'A' = active referee, 'X' = inactive. */
  status: PoolStatus
  arbitre: ArbitrationCode
  niveau: string
  /** Display label, e.g. "NAT-A". */
  arbitreLabel: string
}

export interface PoolGroup {
  id: number
  libelle: string
  codeClub: string
  numero: number
  logo: string | null
  referees: PoolReferee[]
  refereeCount: number
}

export interface PoolListResponse {
  groups: PoolGroup[]
}

/** A licensed athlete returned by the search-licence endpoint. */
export interface LicenceSearchResult extends PoolReferee {
  clubLibelle: string
  numeroClub: string
}

export interface AddRefereeLicencePayload {
  mode: 'licence'
  matric: number
}

export interface AddRefereeManualPayload {
  mode: 'manual'
  nom: string
  prenom: string
  sexe: string
  naissance: string
  arbitre: ArbitrationCode
  niveau: string
}

export type AddRefereePayload = AddRefereeLicencePayload | AddRefereeManualPayload

export interface UpdateRefereePayload {
  nom: string
  prenom: string
  sexe: string
  arbitre: ArbitrationCode
  niveau: string
}
