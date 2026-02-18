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
