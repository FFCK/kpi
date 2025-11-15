import { ref, computed } from 'vue'
import { useMatchStore } from '~/stores/matchStore'

export const useWebSocket = () => {
  const matchStore = useMatchStore()
  const socket = ref<WebSocket | null>(null)
  const isConnected = ref(false)
  const reconnectInterval = 5000
  let reconnectTimer: NodeJS.Timeout | null = null

  const socketTarget = computed(() => {
    if (!matchStore.currentMatch?.websocketConfig) return ''
    const { eventId, terrain } = matchStore.currentMatch.websocketConfig
    return `${eventId}_${terrain}`
  })

  const canUseWebSocket = computed(() => {
    return matchStore.currentMatch?.websocketConfig?.enabled ?? false
  })

  const connect = () => {
    if (!canUseWebSocket.value) {
      console.warn('WebSocket not configured for this match')
      return
    }

    try {
      // TODO: Get WebSocket URL from config
      const wsUrl = 'wss://your-websocket-server.com'
      socket.value = new WebSocket(wsUrl)

      socket.value.onopen = () => {
        console.log('WebSocket connected')
        isConnected.value = true

        if (reconnectTimer) {
          clearTimeout(reconnectTimer)
          reconnectTimer = null
        }
      }

      socket.value.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data)
          handleWebSocketMessage(data)
        } catch (error) {
          console.error('WebSocket message parse error:', error)
        }
      }

      socket.value.onerror = (error) => {
        console.error('WebSocket error:', error)
      }

      socket.value.onclose = () => {
        console.log('WebSocket disconnected')
        isConnected.value = false

        // Auto-reconnect
        if (canUseWebSocket.value) {
          reconnectTimer = setTimeout(() => {
            console.log('Attempting to reconnect...')
            connect()
          }, reconnectInterval)
        }
      }
    } catch (error) {
      console.error('WebSocket connection error:', error)
    }
  }

  const disconnect = () => {
    if (reconnectTimer) {
      clearTimeout(reconnectTimer)
      reconnectTimer = null
    }

    if (socket.value) {
      socket.value.close()
      socket.value = null
    }

    isConnected.value = false
  }

  const send = (type: string, value: any) => {
    if (!socket.value || !isConnected.value) {
      console.warn('WebSocket not connected')
      return
    }

    const message = {
      p: socketTarget.value,
      t: type,
      v: value
    }

    socket.value.send(JSON.stringify(message))
  }

  const sendTimer = (time: string, running: boolean) => {
    send('chrono', { time, run: running })
  }

  const sendShotclock = (value: number) => {
    send('posses', value)
  }

  const sendPeriod = (period: string) => {
    send('period', period)
  }

  const sendScore = (team: 'A' | 'B', score: number) => {
    send(`score${team}`, score)
  }

  const sendPenalty = (team: 'A' | 'B', count: number) => {
    send(`pen${team}`, count)
  }

  const handleWebSocketMessage = (data: any) => {
    // Handle incoming WebSocket messages
    console.log('WebSocket message received:', data)

    // TODO: Implement message handling based on message type
    switch (data.t) {
      case 'sync_request':
        // Send current match state
        sendAllData()
        break
      // Add more message handlers as needed
    }
  }

  const sendAllData = () => {
    if (!matchStore.currentMatch) return

    const match = matchStore.currentMatch

    // Send all match data
    sendScore('A', match.scoreA)
    sendScore('B', match.scoreB)
    sendPeriod(match.period)

    const minutes = Math.floor(matchStore.timerValue / 60)
    const seconds = matchStore.timerValue % 60
    const timeStr = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
    sendTimer(timeStr, matchStore.timerRunning)

    sendShotclock(matchStore.shotclockValue)
  }

  return {
    socket,
    isConnected,
    canUseWebSocket,
    connect,
    disconnect,
    send,
    sendTimer,
    sendShotclock,
    sendPeriod,
    sendScore,
    sendPenalty,
    sendAllData
  }
}
