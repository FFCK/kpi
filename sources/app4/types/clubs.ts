export interface ClubMapItem {
  code: string
  libelle: string
  coord: string
  postal: string
  www: string
  email: string
}

export interface ClubDetail {
  code: string
  libelle: string
  codeComiteDep: string
  libelleComiteDep: string
  coord: string
  coord2: string
  postal: string
  www: string
  email: string
}

export interface RegionalCommittee {
  code: string
  libelle: string
}

export interface DepartmentalCommittee {
  code: string
  libelle: string
  codeComiteReg: string
}

export interface ClubSearchResult {
  code: string
  libelle: string
  codeComiteDep: string
}

export interface ClubTeam {
  numero: number
  libelle: string
  logo: string
  derniereSaison: string | null
  nbCompetitions: number
}

export interface TeamDetail {
  numero: number
  libelle: string
  codeClub: string
  libelleClub: string
  logo: string
  color1: string
  color2: string
  colortext: string
  competitions: TeamCompetition[]
}

export interface TeamCompetition {
  codeCompet: string
  codeSaison: string
  libelleEquipe: string
  libelleCompet: string
}
