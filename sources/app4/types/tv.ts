/** Configuration d'un channel TV */
export interface TvChannel {
  voie: number
  url: string
  intervalle: number
}

/** Label personnalisé pour un channel ou scénario */
export interface TvLabel {
  number: number
  label: string
}

/** Labels response from API */
export interface TvLabelsResponse {
  channels: TvLabel[]
  scenarios: TvLabel[]
}

/** Panneau de contrôle (état local UI) */
export interface ControlPanel {
  id: string
  channel: number | null
  presentation: string
  competition: string
  match: number | null
  team: 'A' | 'B'
  teamSelect: number | null
  number: number
  pitch: number
  pitchs: string
  medal: 'BRONZE' | 'SILVER' | 'GOLD'
  zone: 'inter' | 'club'
  mode: 'full' | 'only' | 'event' | 'static'
  round: string
  start: number
  animate: boolean
  speaker: number
  count: number
  lnStart: number
  lnLen: number
  competList: string
  format: 'json' | 'csv'
  option: number
  navGroup: boolean
  generatedUrl: string
}

/** Scène d'un scénario */
export interface ScenarioScene {
  voie: number
  url: string
  intervalle: number
}

/** Scenario response from API */
export interface ScenarioResponse {
  scenario: number
  scenes: ScenarioScene[]
}

/** Événement pour le dropdown */
export interface TvEvent {
  id: number
  libelle: string
  lieu: string
  dateDebut: string
  dateFin: string
}

/** Match pour le dropdown */
export interface TvMatch {
  id: number
  numeroOrdre: number | null
  terrain: string
  heureMatch: string
  dateMatch: string
  equipeA: string
  equipeB: string
  idEquipeA: number
  idEquipeB: number
  phase: string
  codeCompetition: string
  codeSaison: string
}

/** Équipe pour le dropdown */
export interface TvTeam {
  idEquipe: number
  libelleEquipe: string
}

/** Matches endpoint response */
export interface TvMatchesResponse {
  matches: TvMatch[]
  competitions: string[]
  dates: string[]
  teams: TvTeam[]
  season: string
}

/** Options de présentation */
export interface PresentationOption {
  value: string
  label: string
  group: string
  requiredParams: string[]
}

/** Global filters state */
export interface TvGlobalFilters {
  eventId: number | null
  date: string
  css: string
  lang: string
}

// ─────────────────────────────────────────────
// Constants
// ─────────────────────────────────────────────

export const PRESENTATIONS: PresentationOption[] = [
  // Général
  { value: 'empty', label: 'Empty page', group: 'general', requiredParams: [] },
  { value: 'voie', label: 'Channel', group: 'general', requiredParams: [] },
  { value: 'logo', label: 'Logo', group: 'general', requiredParams: [] },

  // Avant match
  { value: 'match', label: 'Game (Category & teams)', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'match2', label: 'Game (Team colors)', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'list_team', label: 'Players list', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'list_coachs', label: 'Coaches list', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'team', label: 'Team name', group: 'before_game', requiredParams: ['competition', 'match', 'team'] },
  { value: 'referee', label: 'Referees', group: 'before_game', requiredParams: ['competition', 'match'] },
  { value: 'player', label: 'Player name', group: 'before_game', requiredParams: ['competition', 'match', 'team', 'number'] },
  { value: 'coach', label: 'Coach name', group: 'before_game', requiredParams: ['competition', 'match', 'team', 'number'] },

  // Match en cours (nations)
  { value: 'score', label: 'Live score (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_o', label: 'Score only (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_e', label: 'Events only (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'score_s', label: 'Static events (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'teams', label: 'Game & score (nations)', group: 'running_nations', requiredParams: ['pitch'] },
  { value: 'next_game', label: 'Next game (nations)', group: 'running_nations', requiredParams: ['pitch'] },

  // Match en cours (clubs)
  { value: 'score_club', label: 'Live score (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_o', label: 'Score only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_e', label: 'Events only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'score_club_s', label: 'Static events (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'teams_club', label: 'Game & score (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'next_game_club', label: 'Next game (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },
  { value: 'liveteams', label: 'Teams only (clubs)', group: 'running_clubs', requiredParams: ['pitch'] },

  // Match en cours (WebSocket)
  { value: 'live', label: 'Live score WebSocket', group: 'running_ws', requiredParams: ['pitch', 'zone', 'mode'] },

  // Présentation match
  { value: 'match_score', label: 'Game & score', group: 'match_presentation', requiredParams: ['competition', 'match', 'animate'] },

  // Après match
  { value: 'final_ranking', label: 'Final ranking', group: 'after_game', requiredParams: ['competition', 'start'] },
  { value: 'podium', label: 'Podium', group: 'after_game', requiredParams: ['competition', 'animate'] },

  // Écrans d'affichage
  { value: 'multi_score', label: 'Multi score', group: 'screen', requiredParams: ['count', 'speaker'] },
  { value: 'frame_categories', label: 'Cat. games', group: 'screen', requiredParams: ['competition', 'pitchs', 'lnStart', 'lnLen'] },
  { value: 'frame_terrains', label: 'Pitch games', group: 'screen', requiredParams: ['competition', 'pitchs'] },
  { value: 'frame_chart', label: 'Progress', group: 'screen', requiredParams: ['competition', 'round'] },
  { value: 'frame_phases', label: 'Phases', group: 'screen', requiredParams: ['competition', 'round'] },
  { value: 'frame_details', label: 'Details', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_team', label: 'Team details', group: 'screen', requiredParams: ['competition', 'teamSelect'] },
  { value: 'frame_stats', label: 'Stats', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_classement', label: 'Ranking', group: 'screen', requiredParams: ['competition'] },
  { value: 'frame_qr', label: 'QrCodes', group: 'screen', requiredParams: ['competition'] },

  // Site/Mobile
  { value: 'frame_matchs', label: 'Games', group: 'web', requiredParams: ['competition', 'navGroup'] },

  // API
  { value: 'api_players', label: 'Players', group: 'api', requiredParams: ['competList', 'format', 'option'] },
  { value: 'api_stats', label: 'Stats', group: 'api', requiredParams: ['competList', 'format', 'option'] },

  // Cache
  { value: 'force_cache_match', label: 'Force cache match', group: 'cache', requiredParams: ['competition', 'match'] },

  // Debug
  { value: 'player_pictures', label: 'Player Pictures', group: 'debug', requiredParams: [] },
]

export const TV_STYLES = [
  { value: 'avranches2025', label: 'Avranches 2025' },
  { value: 'avranches2025b', label: 'Avranches 2025 Magenta' },
  { value: 'deqing2024', label: 'Deqing 2024' },
  { value: 'cna2022', label: 'CNA 2022' },
  { value: 'saintomer2022', label: 'SaintOmer 2022' },
  { value: 'saintomer2022b', label: 'SaintOmer 2022 Magenta' },
  { value: 'welland2018', label: 'Welland 2018' },
  { value: 'saintomer2017', label: 'SaintOmer 2017' },
  { value: 'thury2014', label: 'Thury 2014' },
  { value: 'usnational', label: 'US National' },
  { value: 'cna', label: 'CNA KP' },
  { value: 'simply', label: 'Simple' },
]

export const PRESENTATION_GROUPS = [
  'general',
  'before_game',
  'running_nations',
  'running_clubs',
  'running_ws',
  'match_presentation',
  'after_game',
  'screen',
  'web',
  'api',
  'cache',
  'debug',
] as const

export const CHANNEL_MAX = 99
export const SCENARIO_COUNT = 9
export const SCENARIO_SCENES = 9

export function createDefaultPanel(): ControlPanel {
  return {
    id: crypto.randomUUID(),
    channel: null,
    presentation: 'match',
    competition: '',
    match: null,
    team: 'A',
    teamSelect: null,
    number: 0,
    pitch: 1,
    pitchs: '1,2,3,4',
    medal: 'GOLD',
    zone: 'inter',
    mode: 'full',
    round: '*',
    start: 0,
    animate: false,
    speaker: 0,
    count: 1,
    lnStart: 1,
    lnLen: 10,
    competList: '',
    format: 'json',
    option: 0,
    navGroup: false,
    generatedUrl: '',
  }
}
