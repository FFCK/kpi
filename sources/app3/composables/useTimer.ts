import { ref, computed, watch } from 'vue'
import { useMatchStore } from '~/stores/matchStore'
import { useBroadcast } from './useBroadcast'
import { useWebSocket } from './useWebSocket'
// @ts-ignore
import Timer from 'easytimer.js'

export const useTimer = () => {
  const matchStore = useMatchStore()
  const broadcast = useBroadcast()
  const websocket = useWebSocket()

  const mainTimer = ref<any>(null)
  const shotclockTimer = ref<any>(null)
  const penaltyTimers = ref<Map<string, any>>(new Map())

  const buzzerAudio = ref<HTMLAudioElement | null>(null)

  const initTimers = () => {
    if (typeof window === 'undefined') return

    // Initialize buzzer audio (optional - will fail silently if file not found)
    try {
      buzzerAudio.value = new Audio('/img/buzzeer-180942.mp3')
      // Preload audio silently
      buzzerAudio.value.load()
    } catch (e) {
      console.warn('Buzzer audio file not available:', e)
    }

    // Main timer
    mainTimer.value = new Timer({
      countdown: true,
      precision: 'secondTenths',
      startValues: { seconds: matchStore.timerValue }
    })

    mainTimer.value.addEventListener('secondsUpdated', () => {
      matchStore.timerValue = mainTimer.value.getTotalTimeValues().seconds
      broadcast.broadcastTimer()

      const minutes = Math.floor(matchStore.timerValue / 60)
      const seconds = matchStore.timerValue % 60
      const timeStr = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`

      if (websocket.isConnected.value) {
        websocket.sendTimer(timeStr, true)
      }
    })

    mainTimer.value.addEventListener('targetAchieved', () => {
      playBuzzer()
      pauseTimer()
    })

    // Shot clock timer
    shotclockTimer.value = new Timer({
      countdown: true,
      precision: 'secondTenths',
      startValues: { seconds: matchStore.shotclockValue }
    })

    shotclockTimer.value.addEventListener('secondsUpdated', () => {
      matchStore.shotclockValue = shotclockTimer.value.getTotalTimeValues().seconds
      broadcast.broadcastShotclock()

      if (websocket.isConnected.value) {
        websocket.sendShotclock(matchStore.shotclockValue)
      }
    })

    shotclockTimer.value.addEventListener('targetAchieved', () => {
      playBuzzer()
    })
  }

  const startTimer = () => {
    if (!mainTimer.value) return

    mainTimer.value.start()
    matchStore.startTimer()
    broadcast.broadcastTimerStatus()

    if (shotclockTimer.value) {
      shotclockTimer.value.start()
    }
  }

  const pauseTimer = () => {
    if (!mainTimer.value) return

    mainTimer.value.pause()
    matchStore.pauseTimer()
    broadcast.broadcastTimerStatus()

    if (shotclockTimer.value) {
      shotclockTimer.value.pause()
    }
  }

  const resetTimer = () => {
    if (!mainTimer.value) return

    matchStore.resetTimer()
    mainTimer.value.stop()
    mainTimer.value.start({ countdown: true, startValues: { seconds: matchStore.timerValue } })
    mainTimer.value.pause()
    broadcast.broadcastTimer()
  }

  const adjustTimer = (seconds: number) => {
    if (!mainTimer.value) return

    const currentSeconds = mainTimer.value.getTotalTimeValues().seconds
    const newSeconds = Math.max(0, currentSeconds + seconds)

    matchStore.timerValue = newSeconds
    mainTimer.value.stop()
    mainTimer.value.start({ countdown: true, startValues: { seconds: newSeconds } })

    if (matchStore.timerRunning) {
      mainTimer.value.start()
    } else {
      mainTimer.value.pause()
    }

    broadcast.broadcastTimer()
  }

  const resetShotclock = () => {
    if (!shotclockTimer.value) return

    matchStore.resetShotclock()
    shotclockTimer.value.stop()
    shotclockTimer.value.start({ countdown: true, startValues: { seconds: 60 } })

    if (matchStore.shotclockRunning) {
      shotclockTimer.value.start()
    } else {
      shotclockTimer.value.pause()
    }

    broadcast.broadcastShotclock()
  }

  const adjustShotclock = (seconds: number) => {
    if (!shotclockTimer.value) return

    const currentSeconds = shotclockTimer.value.getTotalTimeValues().seconds
    const newSeconds = Math.max(0, currentSeconds + seconds)

    matchStore.shotclockValue = newSeconds
    shotclockTimer.value.stop()
    shotclockTimer.value.start({ countdown: true, startValues: { seconds: newSeconds } })

    if (matchStore.shotclockRunning) {
      shotclockTimer.value.start()
    } else {
      shotclockTimer.value.pause()
    }

    broadcast.broadcastShotclock()
  }

  const playBuzzer = () => {
    if (buzzerAudio.value) {
      buzzerAudio.value.play().catch(e => console.warn('Could not play buzzer:', e))
    }
  }

  const formatTime = (seconds: number): string => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  const formatTimeWithTenths = (seconds: number): string => {
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    const tenths = Math.floor((seconds % 1) * 10)
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}.${tenths}`
  }

  const destroyTimers = () => {
    if (mainTimer.value) {
      mainTimer.value.stop()
      mainTimer.value = null
    }

    if (shotclockTimer.value) {
      shotclockTimer.value.stop()
      shotclockTimer.value = null
    }

    penaltyTimers.value.forEach(timer => timer.stop())
    penaltyTimers.value.clear()
  }

  return {
    initTimers,
    startTimer,
    pauseTimer,
    resetTimer,
    adjustTimer,
    resetShotclock,
    adjustShotclock,
    playBuzzer,
    formatTime,
    formatTimeWithTenths,
    destroyTimers
  }
}
