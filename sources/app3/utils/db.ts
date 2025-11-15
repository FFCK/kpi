import Dexie, { type EntityTable } from 'dexie'

// Types
export interface Match {
  id?: number
  matchId?: number // ID du match dans la BDD backend
  teamA: string
  teamB: string
  scoreA: number
  scoreB: number
  status: 'ATT' | 'ON' | 'END' // Waiting, Ongoing, Ended
  period: 'M1' | 'M2' | 'P1' | 'P2' | 'TB' // Half1, Half2, Overtime1, Overtime2, TieBreak
  type: 'C' | 'E' // Classement, Elimination
  date: string
  time: string
  field: string
  playersA: Player[]
  playersB: Player[]
  officials?: Officials
  events: MatchEvent[]
  locked: boolean
  published: boolean
  endTime?: string
  comments?: string
  timestamp: number
  // WebSocket config
  eventId?: number
  websocketConfig?: {
    enabled: boolean
    eventId: number
    terrain: string
  }
}

export interface Player {
  id: string // UUID or Matric
  matric?: number
  number: number
  firstName: string
  lastName: string
  status: 'J' | 'C' | 'E' // Joueur, Capitaine, Entraîneur/Coach
  license?: string
  birthdate?: string
  category?: string
}

export interface Officials {
  secretary?: string
  timekeeper?: string
  timeshoot?: string
  referee1?: string
  referee2?: string
  linesman1?: string
  linesman2?: string
  organizer?: string
  delegate?: string
  chiefReferee?: string
}

export interface MatchEvent {
  id?: number
  idMatch?: number
  period: 'M1' | 'M2' | 'P1' | 'P2' | 'TB'
  time: string // Format MM:SS
  eventType: 'B' | 'V' | 'J' | 'R' | 'D' // But, Vert, Jaune, Rouge, Rouge Définitif
  team: 'A' | 'B'
  playerId?: string
  playerNumber?: number
  playerName?: string
  reason?: string // Motif pour les cartons
  timestamp?: number
}

export interface Penalty {
  id: string
  team: 'A' | 'B'
  playerId?: string
  type: 'V' | 'J' | 'R' | 'D'
  startTime: number // Timestamp
  duration: number // Seconds
  remaining?: number
}

const db = new Dexie('app3_matchsheet') as Dexie & {
  matches: EntityTable<Match, 'id'>
  preferences: EntityTable<{ id: string; value: any }, 'id'>
}

// Schema version 1
db.version(1).stores({
  matches: '++id, matchId, timestamp, status',
  preferences: '&id'
})

export default db
