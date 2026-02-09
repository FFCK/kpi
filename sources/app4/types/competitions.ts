// Competition types for admin competitions page

// Competition level
export type CompetitionLevel = 'INT' | 'NAT' | 'REG'

// Competition type (ranking type)
export type CompetitionType = 'CHPT' | 'CP' | 'MULTI'

// Competition status
export type CompetitionStatus = 'ATT' | 'ON' | 'END'

// Ranking structure type for MULTI competitions
export type RankingStructureType = 'team' | 'club' | 'cd' | 'cr' | 'nation'

// Competition entity from API
export interface AdminCompetition {
  code: string
  codeSaison: string
  codeNiveau: CompetitionLevel
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  codeRef: string | null
  groupOrder: number | null
  codeTypeclt: CompetitionType
  codeTour: number
  qualifies: number
  elimines: number
  points: string
  goalaverage: string
  statut: CompetitionStatus
  publication: boolean
  verrou: boolean
  nbEquipes: number
  nbJournees: number
  nbMatchs: number
  hasRc: boolean
  section: number
  sectionLabel: string
  web: string | null
  enActif: boolean
  titreActif: boolean
  bandeauActif: boolean
  logoActif: boolean
  sponsorActif: boolean
  kpiFfckActif: boolean
  bandeauLink: string | null
  logoLink: string | null
  sponsorLink: string | null
  pointsGrid: Record<string, number> | null
  multiCompetitions: string[] | null
  rankingStructureType: RankingStructureType | null
  commentairesCompet: string | null
}

// Form data for create/update
export interface CompetitionFormData {
  code: string
  codeNiveau: CompetitionLevel
  libelle: string
  soustitre: string
  soustitre2: string
  codeRef: string
  groupOrder: number | null
  codeTypeclt: CompetitionType
  codeTour: number
  qualifies: number
  elimines: number
  points: string
  goalaverage: string
  statut: CompetitionStatus
  web: string
  enActif: boolean
  titreActif: boolean
  bandeauActif: boolean
  logoActif: boolean
  sponsorActif: boolean
  kpiFfckActif: boolean
  pointsGrid: Record<string, number> | null
  multiCompetitions: string[]
  rankingStructureType: RankingStructureType | null
  commentairesCompet: string
}

// Group for select dropdown
export interface CompetitionGroup {
  id: number
  groupe: string
  libelle: string
  libelleEn: string | null
  section: number
  ordre: number
  codeNiveau: string | null
}

// Competition for MULTI select (source competitions)
export interface CompetitionForMulti {
  code: string
  libelle: string
  type: string
  tour: number | null
  groupOrder: number | null
  section: number
  sectionLabel: string
}

// Section with competitions for MULTI select
export interface CompetitionSectionForMulti {
  section: number
  sectionLabel: string
  competitions: CompetitionForMulti[]
}

// Search result for autocomplete from previous seasons
export interface CompetitionSearchResult {
  code: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  latestSeasonCode: string
}

// Import mode for competition creation
export interface CompetitionImportMode {
  mode: 'new' | 'import' // 'new' = manual creation, 'import' = from previous season
  selectedCompetition: CompetitionSearchResult | null
}

// Competition data from previous season (for import)
export interface CompetitionFromPreviousSeason {
  code: string
  niveau: CompetitionLevel
  type: CompetitionType
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  groupe: string | null
  groupOrder: number | null
  tour: number
  statut: CompetitionStatus
  qualifies: number
  elimines: number
  points: string
  goalaverage: string
  lienWeb: string | null
  enActif: boolean
  titreActif: boolean
  bandeauActif: boolean
  logoActif: boolean
  sponsorActif: boolean
  kpiFfckActif: boolean
  commentaires: string | null
  pointsGrid: Record<string, number> | null
  multiCompetitions: string[] | null
  rankingStructureType: RankingStructureType | null
  publication: boolean
  importedFromSeason: string
}
