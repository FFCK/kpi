export interface WorkerEvent {
  id: number
  libelle: string
  lieu: string
  dateDebut: string
  dateFin: string
}

export interface WorkerDate {
  dateMatch: string
  heureMatch: string
}

export interface WorkerConfig {
  id: number
  idEvent: number
  dateEvent: string
  hourEvent: string
  hourEventInitial: string
  offsetEvent: number
  pitchEvent: number
  delayEvent: number
  status: 'running' | 'paused' | 'stopped'
  lastExecution: string | null
  createdAt: string
  updatedAt: string
  executionCount: number
  errorMessage: string | null
  secondsSinceLastExecution: number | null
  currentSimulatedTime: string
  isRunning: boolean
  isPaused: boolean
  isStopped: boolean
  isHealthy: boolean
}

export interface WorkerForm {
  idEvent: number | null
  dateEvent: string
  hourEvent: string
  offsetEvent: number
  pitchEvent: number
  delayEvent: number
}

export interface WorkerMonitorPitch {
  pitch: string
  game: number | null
  num: number | null
  time: string | null
  next: { id: number | null; time: string | null; num: number | null }
}

export interface WorkerMonitor {
  pitches: WorkerMonitorPitch[]
  time: { currentTime: string; workingTime: string }
}
