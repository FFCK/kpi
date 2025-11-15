<template>
  <div v-if="!matchStore.currentMatch" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <p class="text-xl mb-4">{{ t('common.error') }}: No match loaded</p>
      <button @click="router.push('/')" class="btn-primary">
        {{ t('common.back') }}
      </button>
    </div>
  </div>

  <div v-else class="min-h-screen bg-gray-900 text-white">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 p-4">
      <div class="container mx-auto flex justify-between items-center">
        <button @click="router.push('/')" class="btn-secondary text-sm">
          ← {{ t('common.back') }}
        </button>

        <div class="text-center flex-1">
          <h1 class="text-2xl font-bold">
            {{ matchStore.currentMatch.teamA }} vs {{ matchStore.currentMatch.teamB }}
          </h1>
          <p class="text-sm text-gray-400">
            {{ matchStore.currentMatch.date }} - {{ matchStore.currentMatch.time }} - {{ t('match.field') }} {{ matchStore.currentMatch.field }}
          </p>
        </div>

        <div class="flex gap-2">
          <button
            @click="matchStore.toggleLock()"
            :class="matchStore.isLocked ? 'btn-danger' : 'btn-success'"
            class="text-sm"
          >
            {{ matchStore.isLocked ? '🔒 ' + t('match.locked') : '🔓 ' + t('match.unlocked') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto p-4">
      <!-- Match Status & Period -->
      <div class="flex justify-between items-center mb-4">
        <div class="flex gap-2">
          <button
            v-for="status in ['ATT', 'ON', 'END']"
            :key="status"
            @click="!matchStore.isLocked && matchStore.setStatus(status)"
            :class="[
              'px-4 py-2 rounded-lg font-semibold transition-colors',
              matchStore.currentMatch.status === status
                ? 'bg-blue-600'
                : 'bg-gray-700 hover:bg-gray-600',
              matchStore.isLocked && 'opacity-50 cursor-not-allowed'
            ]"
            :disabled="matchStore.isLocked"
          >
            {{ t(`match.status${status.charAt(0) + status.slice(1).toLowerCase()}`) }}
          </button>
        </div>

        <div class="flex gap-2">
          <button
            v-for="period in ['M1', 'M2', 'P1', 'P2', 'TB']"
            :key="period"
            @click="!matchStore.isLocked && changePeriod(period)"
            :class="[
              'px-4 py-2 rounded-lg font-semibold transition-colors',
              matchStore.currentMatch.period === period
                ? 'bg-green-600'
                : 'bg-gray-700 hover:bg-gray-600',
              matchStore.isLocked && 'opacity-50 cursor-not-allowed',
              (matchStore.currentMatch.type === 'C' && ['P1', 'P2', 'TB'].includes(period)) && 'hidden'
            ]"
            :disabled="matchStore.isLocked"
          >
            {{ t(`match.period${period}`) }}
          </button>
        </div>
      </div>

      <!-- Game Area -->
      <div class="grid lg:grid-cols-3 gap-4">
        <!-- Team A -->
        <div class="card bg-gray-800">
          <div class="text-center mb-4">
            <h2 class="text-2xl font-bold mb-2">{{ t('match.teamA') }}</h2>
            <div class="text-6xl font-digital text-blue-400">{{ matchStore.currentMatch.scoreA }}</div>
            <h3 class="text-xl mt-2">{{ matchStore.currentMatch.teamA }}</h3>
          </div>

          <PlayerList team="A" :locked="matchStore.isLocked" @playerClick="selectPlayer" />
        </div>

        <!-- Center Panel - Timer & Controls -->
        <div class="space-y-4">
          <!-- Timer -->
          <div class="card bg-gray-800 text-center">
            <h3 class="text-lg font-semibold mb-2">{{ t('timer.timer') }}</h3>
            <div class="timer-display mb-4">
              {{ formattedTimer }}
            </div>

            <div class="flex justify-center gap-2 mb-4">
              <button @click="timer.adjustTimer(-60)" class="btn-icon">-60</button>
              <button @click="timer.adjustTimer(-10)" class="btn-icon">-10</button>
              <button @click="timer.adjustTimer(-1)" class="btn-icon">-1</button>
              <button @click="timer.adjustTimer(1)" class="btn-icon">+1</button>
              <button @click="timer.adjustTimer(10)" class="btn-icon">+10</button>
              <button @click="timer.adjustTimer(60)" class="btn-icon">+60</button>
            </div>

            <div class="flex justify-center gap-2">
              <button @click="timer.startTimer()" class="btn-success">{{ t('timer.start') }}</button>
              <button @click="timer.pauseTimer()" class="btn-danger">{{ t('timer.pause') }}</button>
              <button @click="timer.resetTimer()" class="btn-secondary">{{ t('timer.reset') }}</button>
            </div>
          </div>

          <!-- Shot Clock -->
          <div class="card bg-gray-800 text-center">
            <h3 class="text-lg font-semibold mb-2">{{ t('timer.shotclock') }}</h3>
            <div class="font-digital text-4xl text-yellow-400 mb-4">
              {{ matchStore.shotclockValue }}
            </div>

            <div class="flex justify-center gap-2 mb-2">
              <button @click="timer.adjustShotclock(-10)" class="btn-icon">-10</button>
              <button @click="timer.adjustShotclock(-1)" class="btn-icon">-1</button>
              <button @click="timer.adjustShotclock(1)" class="btn-icon">+1</button>
              <button @click="timer.adjustShotclock(10)" class="btn-icon">+10</button>
            </div>

            <button @click="timer.resetShotclock()" class="btn-secondary w-full">
              {{ t('timer.resetShotclock') }}
            </button>
          </div>

          <!-- Event Buttons -->
          <div v-if="!matchStore.isLocked" class="card bg-gray-800">
            <h3 class="text-lg font-semibold mb-3">{{ t('events.events') }}</h3>

            <div class="grid grid-cols-2 gap-2">
              <button
                @click="addEvent('B')"
                class="btn-success py-4 text-lg font-bold"
                :disabled="!selectedPlayer"
              >
                {{ t('events.goal') }}
              </button>

              <button
                @click="addEvent('V')"
                class="bg-green-600 hover:bg-green-700 py-4 rounded-lg font-bold"
                :disabled="!selectedPlayer"
              >
                {{ t('events.greenCard') }}
              </button>

              <button
                @click="addEvent('J')"
                class="bg-yellow-600 hover:bg-yellow-700 py-4 rounded-lg font-bold"
                :disabled="!selectedPlayer"
              >
                {{ t('events.yellowCard') }}
              </button>

              <button
                @click="addEvent('R')"
                class="bg-red-600 hover:bg-red-700 py-4 rounded-lg font-bold"
                :disabled="!selectedPlayer"
              >
                {{ t('events.redCard') }}
              </button>

              <button
                @click="addEvent('D')"
                class="bg-red-800 hover:bg-red-900 py-4 rounded-lg font-bold col-span-2"
                :disabled="!selectedPlayer"
              >
                {{ t('events.redCardD') }}
              </button>
            </div>

            <div v-if="selectedPlayer" class="mt-3 p-2 bg-blue-900 rounded">
              <div class="text-sm">Selected: {{ selectedPlayer.team }} - {{ selectedPlayer.number }} {{ selectedPlayer.name }}</div>
              <button @click="selectedPlayer = null" class="text-xs text-blue-300 hover:text-blue-100">
                {{ t('common.cancel') }}
              </button>
            </div>
          </div>

          <!-- Broadcast Controls -->
          <div class="card bg-gray-800">
            <h3 class="text-lg font-semibold mb-3">{{ t('broadcast.broadcast') }}</h3>

            <div class="space-y-2">
              <button @click="broadcast.openScoreboard()" class="btn-primary w-full">
                {{ t('broadcast.openScoreboard') }}
              </button>

              <button @click="broadcast.openShotclock()" class="btn-primary w-full">
                {{ t('broadcast.openShotclock') }}
              </button>

              <button @click="broadcast.broadcastAll()" class="btn-secondary w-full">
                {{ t('broadcast.updateBroadcast') }}
              </button>

              <div v-if="websocket.canUseWebSocket" class="pt-2 border-t border-gray-700">
                <button
                  v-if="!websocket.isConnected.value"
                  @click="websocket.connect()"
                  class="btn-success w-full"
                >
                  Connect WebSocket
                </button>
                <button
                  v-else
                  @click="websocket.disconnect()"
                  class="btn-danger w-full"
                >
                  Disconnect WebSocket
                </button>

                <div class="text-xs text-center mt-2" :class="websocket.isConnected.value ? 'text-green-400' : 'text-gray-400'">
                  {{ websocket.isConnected.value ? t('broadcast.websocketConnected') : t('broadcast.websocketDisconnected') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Team B -->
        <div class="card bg-gray-800">
          <div class="text-center mb-4">
            <h2 class="text-2xl font-bold mb-2">{{ t('match.teamB') }}</h2>
            <div class="text-6xl font-digital text-red-400">{{ matchStore.currentMatch.scoreB }}</div>
            <h3 class="text-xl mt-2">{{ matchStore.currentMatch.teamB }}</h3>
          </div>

          <PlayerList team="B" :locked="matchStore.isLocked" @playerClick="selectPlayer" />
        </div>
      </div>

      <!-- Events List -->
      <div class="card bg-gray-800 mt-4">
        <h3 class="text-xl font-semibold mb-4">{{ t('events.eventList') }}</h3>
        <EventList :events="matchStore.matchEvents" @deleteEvent="deleteEvent" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useMatchStore } from '~/stores/matchStore'
import { useTimer } from '~/composables/useTimer'
import { useBroadcast } from '~/composables/useBroadcast'
import { useWebSocket } from '~/composables/useWebSocket'

const { t } = useI18n()
const router = useRouter()
const matchStore = useMatchStore()
const timer = useTimer()
const broadcast = useBroadcast()
const websocket = useWebSocket()

const selectedPlayer = ref<{
  team: 'A' | 'B'
  playerId: string
  number: number
  name: string
} | null>(null)

const formattedTimer = computed(() => {
  return timer.formatTime(matchStore.timerValue)
})

const selectPlayer = (data: any) => {
  if (matchStore.isLocked) return

  selectedPlayer.value = {
    team: data.team,
    playerId: data.playerId,
    number: data.number,
    name: data.name
  }
}

const changePeriod = (period: any) => {
  matchStore.setPeriod(period)
  timer.resetTimer()
  broadcast.broadcastPeriod()
  broadcast.broadcastTimer()
}

const addEvent = (eventType: 'B' | 'V' | 'J' | 'R' | 'D') => {
  if (!selectedPlayer.value || matchStore.isLocked) return

  const minutes = Math.floor(matchStore.timerValue / 60)
  const seconds = matchStore.timerValue % 60
  const timeStr = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`

  matchStore.addEvent({
    period: matchStore.currentMatch!.period,
    time: timeStr,
    eventType,
    team: selectedPlayer.value.team,
    playerId: selectedPlayer.value.playerId,
    playerNumber: selectedPlayer.value.number,
    playerName: selectedPlayer.value.name
  })

  broadcast.broadcastScores()

  if (websocket.isConnected.value) {
    websocket.sendScore('A', matchStore.currentMatch!.scoreA)
    websocket.sendScore('B', matchStore.currentMatch!.scoreB)
  }

  selectedPlayer.value = null
}

const deleteEvent = (index: number) => {
  if (matchStore.isLocked) return

  matchStore.removeEvent(index)
  broadcast.broadcastScores()

  if (websocket.isConnected.value) {
    websocket.sendScore('A', matchStore.currentMatch!.scoreA)
    websocket.sendScore('B', matchStore.currentMatch!.scoreB)
  }
}

onMounted(() => {
  if (!matchStore.currentMatch) {
    router.push('/')
    return
  }

  timer.initTimers()
  broadcast.initBroadcast()

  if (matchStore.currentMatch.websocketConfig?.enabled) {
    websocket.connect()
  }
})

onUnmounted(() => {
  timer.destroyTimers()
  broadcast.closeBroadcast()
  websocket.disconnect()
})
</script>
