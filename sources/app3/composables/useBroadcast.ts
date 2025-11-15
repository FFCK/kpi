import { useMatchStore } from '~/stores/matchStore'

export const useBroadcast = () => {
  const matchStore = useMatchStore()
  let channel: BroadcastChannel | null = null

  const initBroadcast = () => {
    if (typeof window === 'undefined') return

    try {
      channel = new BroadcastChannel('kpi_channel')

      channel.onmessage = (event) => {
        const message = event.data

        switch (message.type) {
          case 'scoreboard':
            if (message.value === 'ready') {
              broadcastAll()
            }
            break
          case 'shotclock':
            if (message.value === 'ready') {
              broadcastTimer()
              broadcastShotclock()
              broadcastTimerStatus()
            }
            break
        }
      }
    } catch (error) {
      console.warn('BroadcastChannel not supported:', error)
    }
  }

  const cleanStr = (str: string | number): string => {
    return String(str).replace(/\s+/g, '')
  }

  const broadcastTimer = () => {
    if (!channel) return

    const minutes = Math.floor(matchStore.timerValue / 60)
    const seconds = matchStore.timerValue % 60
    const timeStr = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`

    channel.postMessage({
      type: 'timer',
      value: timeStr
    })
  }

  const broadcastShotclock = () => {
    if (!channel) return

    channel.postMessage({
      type: 'shotclock',
      value: cleanStr(matchStore.shotclockValue)
    })
  }

  const broadcastTimerStatus = () => {
    if (!channel) return

    channel.postMessage({
      type: 'timer_status',
      value: matchStore.timerRunning ? 'run' : 'stop'
    })
  }

  const broadcastPeriod = () => {
    if (!channel || !matchStore.currentMatch) return

    channel.postMessage({
      type: 'period',
      value: matchStore.currentMatch.period
    })
  }

  const broadcastTeams = () => {
    if (!channel || !matchStore.currentMatch) return

    channel.postMessage({
      type: 'teams',
      value: {
        teamA: matchStore.currentMatch.teamA,
        teamB: matchStore.currentMatch.teamB,
        nationA: 'FRA', // TODO: Get from team data
        nationB: 'FRA'
      }
    })
  }

  const broadcastScores = () => {
    if (!channel || !matchStore.currentMatch) return

    channel.postMessage({
      type: 'scores',
      value: {
        scoreA: matchStore.currentMatch.scoreA,
        scoreB: matchStore.currentMatch.scoreB
      }
    })
  }

  const broadcastPenalty = (team: 'A' | 'B', penalty: any) => {
    if (!channel) return

    channel.postMessage({
      type: `pen${team}`,
      value: penalty
    })
  }

  const broadcastAll = () => {
    broadcastTeams()
    broadcastScores()
    broadcastPeriod()
    broadcastTimer()
    broadcastShotclock()
    broadcastTimerStatus()
  }

  const openScoreboard = () => {
    const config = useRuntimeConfig()
    const url = `${config.public.backendBaseUrl}/admin/scoreboard.php`
    window.open(url, '_blank', 'width=1920,height=1080')
  }

  const openShotclock = () => {
    const config = useRuntimeConfig()
    const url = `${config.public.backendBaseUrl}/admin/shotclock.php`
    window.open(url, '_blank', 'width=800,height=600')
  }

  const closeBroadcast = () => {
    if (channel) {
      channel.close()
      channel = null
    }
  }

  return {
    initBroadcast,
    broadcastTimer,
    broadcastShotclock,
    broadcastTimerStatus,
    broadcastPeriod,
    broadcastTeams,
    broadcastScores,
    broadcastPenalty,
    broadcastAll,
    openScoreboard,
    openShotclock,
    closeBroadcast
  }
}
