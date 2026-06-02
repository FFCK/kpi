import { Timer } from 'easytimer.js'
import { ref, computed, onUnmounted, type Ref } from 'vue'

/**
 * Match countdown timer (game clock) built on easytimer.js.
 *
 * Semantics (simpler than the legacy fm3 encoding — see DOC/specs/PAGE_SCORING.md):
 * - `maxTime`  = period duration in seconds (the countdown starts from here)
 * - `elapsed`  = seconds already played
 * - the timer counts DOWN from (maxTime - elapsed)
 *
 * Server persistence (kp_chrono via /admin/scoring/gameTimer):
 * - on run/stop we send { action, startTime: elapsed, runTime: <unused here>, maxTime }
 * - on reload, restoreFromServer() rebuilds the running clock from the persisted state:
 *   if running, realElapsed = elapsed + (nowServer - startTimeServer)
 *
 * The composable is display-oriented; persistence calls live in the page/store so the
 * timer stays a pure UI concern.
 */

export interface TimerOptions {
  /** Called whenever the displayed time changes (for broadcast/scoreboard in Phase 2) */
  onTick?: (display: string) => void
  /** Called once when the countdown reaches zero */
  onTargetReached?: () => void
}

const pad = (n: number): string => (n < 10 ? '0' + n : String(n))

export function useTimer(options: TimerOptions = {}) {
  const timer = new Timer()

  const isRunning = ref(false)
  // remaining time, in tenths of a second, for reactive display
  const remainingTenths = ref(0)
  // total period duration in seconds
  const maxTime = ref(0)

  /** "MM:SS.d" display string */
  const display = computed(() => {
    const totalTenths = remainingTenths.value
    const minutes = Math.floor(totalTenths / 600)
    const seconds = Math.floor((totalTenths % 600) / 10)
    const tenths = totalTenths % 10
    return `${pad(minutes)}:${pad(seconds)}.${tenths}`
  })

  /** "MM:SS" — used to timestamp events */
  const gameTime = computed(() => {
    const totalSeconds = Math.ceil(remainingTenths.value / 10)
    const minutes = Math.floor(totalSeconds / 60)
    const seconds = totalSeconds % 60
    return `${pad(minutes)}:${pad(seconds)}`
  })

  /** seconds already elapsed in the current period */
  const elapsed = computed(() => maxTime.value - Math.ceil(remainingTenths.value / 10))

  const syncFromTimer = () => {
    const v = timer.getTotalTimeValues()
    remainingTenths.value = v.secondTenths // easytimer total in tenths
    options.onTick?.(display.value)
  }

  timer.addEventListener('secondTenthsUpdated', syncFromTimer)
  timer.addEventListener('targetAchieved', () => {
    isRunning.value = false
    remainingTenths.value = 0
    options.onTick?.(display.value)
    options.onTargetReached?.()
  })

  /** Configure the countdown for a period without starting it */
  const setPeriod = (durationSeconds: number, elapsedSeconds = 0) => {
    maxTime.value = durationSeconds
    const remaining = Math.max(0, durationSeconds - elapsedSeconds)
    timer.stop()
    isRunning.value = false
    remainingTenths.value = remaining * 10
    // prime easytimer's internal values so a later start() resumes from here
    timer.start({
      countdown: true,
      precision: 'secondTenths',
      startValues: { seconds: remaining }
    })
    timer.pause()
  }

  const start = () => {
    if (isRunning.value || remainingTenths.value <= 0) return
    const remaining = Math.ceil(remainingTenths.value / 10)
    timer.start({
      countdown: true,
      precision: 'secondTenths',
      startValues: { seconds: remaining }
    })
    isRunning.value = true
  }

  const stop = () => {
    timer.pause()
    isRunning.value = false
    syncFromTimer()
  }

  const reset = () => {
    timer.stop()
    isRunning.value = false
    remainingTenths.value = maxTime.value * 10
    options.onTick?.(display.value)
  }

  /**
   * Rebuild the clock from the persisted kp_chrono state.
   * @param state.action       'run' | 'stop'
   * @param state.maxTime      period duration (seconds)
   * @param state.elapsed      elapsed seconds at the last run/stop
   * @param state.startTimeServer  server time (seconds % 86400) at the last 'run'
   * @param state.nowServer    current server time (seconds % 86400)
   */
  const restoreFromServer = (state: {
    action: 'run' | 'stop'
    maxTime: number
    elapsed: number
    startTimeServer?: number
    nowServer?: number
  }) => {
    let realElapsed = state.elapsed
    if (state.action === 'run' && state.startTimeServer != null && state.nowServer != null) {
      let delta = state.nowServer - state.startTimeServer
      if (delta < 0) delta += 86400 // crossed midnight
      realElapsed = state.elapsed + delta
    }
    setPeriod(state.maxTime, realElapsed)
    if (state.action === 'run') start()
  }

  onUnmounted(() => {
    timer.stop()
  })

  return {
    isRunning: isRunning as Ref<boolean>,
    display,
    gameTime,
    elapsed,
    maxTime,
    setPeriod,
    start,
    stop,
    reset,
    restoreFromServer
  }
}
