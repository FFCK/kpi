export interface AthleteSearchResult {
  matric: number
  nom: string
  prenom: string
  sexe: string
  naissance: string | null
  club: string
  codeClub: string
  label: string
}

export interface AthleteDetail {
  matric: number
  nom: string
  prenom: string
  sexe: string
  naissance: string | null
  icf: number | null
  origine: string
  club: {
    code: string
    libelle: string
  }
  comiteDep: {
    code: string
    libelle: string
  }
  comiteReg: {
    code: string
    libelle: string
  }
  pagaie: {
    eauVive: string
    mer: string
    eauCalme: string
  }
  certificats: {
    aps: string
    ck: string
  }
  arbitrage: {
    qualification: string
    niveau: string
    saison: string
    livret: string
  }
  typeLicence: string | null
  categorieAge: { code: string; libelle: string } | null
  surclassement: { date: string; cat: string } | null
  editable: boolean
}

export interface AthletePresence {
  competition: string
  equipe: string
  numero: number | null
  capitaine: string
  categorie: string
}

export interface AthleteOfficiel {
  date: string | null
  heure: string
  competition: string
  matchId: number
  matchNumero: number | null
  arbitrePrincipal: boolean
  arbitreSecondaire: boolean
  secretaire: boolean
  chronometreur: boolean
  timekeeper: boolean
  ligne: boolean
  scoreValide: boolean
}

export interface AthleteMatch {
  date: string | null
  competition: string
  matchId: number
  matchNumero: number | null
  equipeA: string
  equipeB: string
  scoreA: string | null
  scoreB: string | null
  equipe: string
  numero: number | null
  capitaine: string
  buts: number
  verts: number
  jaunes: number
  rouges: number
  rougesDefinitifs: number
  tirs: number
  arrets: number
  scoreValide: boolean
}

export interface AthleteParticipations {
  season: string
  presences: AthletePresence[]
  officiels: AthleteOfficiel[]
  matchs: AthleteMatch[]
}

export interface AthleteUpdatePayload {
  nom: string
  prenom: string
  sexe: string
  naissance: string
  origine: string
  icf: number | null
  arbitrage: {
    qualification: string
    niveau: string
  }
  codeClub?: string
}
