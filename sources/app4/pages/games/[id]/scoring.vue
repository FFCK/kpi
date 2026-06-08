<script setup lang="ts">
import type { Period, MatchStatus, ScoringPlayer, ScoringEvent, ScoringEventCode, TeamSide } from '~/types/scoring'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const route = useRoute()
const { t } = useI18n()
const toast = useToast()
const scoringStore = useScoringStore()

const matchId = computed(() => parseInt(route.params.id as string))

// Permissions (experimentation phase: profile <= 2)
const { canView, canScore: canScoreBase, canValidate } = useScoringPermissions(
  computed(() => scoringStore.isLocked)
)
const canScore = computed(() => canScoreBase.value && !scoringStore.isCompetitionEnded)

// Periods available depend on match type (C = no overtime unless needed)
const periods: Period[] = ['M1', 'M2', 'P1', 'P2', 'TB']
const statuses: MatchStatus[] = ['ATT', 'ON', 'END']
const eventCodes: { code: ScoringEventCode; labelKey: string; color: string }[] = [
  { code: 'B', labelKey: 'scoring.event.goal', color: 'primary' },
  { code: 'V', labelKey: 'scoring.event.card_green', color: 'success' },
  { code: 'J', labelKey: 'scoring.event.card_yellow', color: 'warning' },
  { code: 'R', labelKey: 'scoring.event.card_red', color: 'error' },
  { code: 'D', labelKey: 'scoring.event.card_red_def', color: 'error' }
]

// Selected player for the next event
const selected = ref<{ team: TeamSide; player: ScoringPlayer } | null>(null)

const match = computed(() => scoringStore.match)
const loading = computed(() => scoringStore.loading)

// ─── Game clock (easytimer) ───
const { display: clockDisplay, gameTime, elapsed, isRunning, setPeriod: timerSetPeriod, start: timerStart, stop: timerStop, reset: timerReset, restoreFromServer } =
  useTimer({
    onTargetReached: () => {
      // Buzzer at period end; persist the stop server-side
      void scoringStore.setTimer('stop', { startTime: elapsed.value, runTime: 0, maxTime: scoringStore.currentPeriodDuration })
    }
  })

onMounted(async () => {
  if (!canView.value) return
  try {
    await scoringStore.load(matchId.value)
    // Restore the clock from kp_chrono if a state was persisted, else start fresh.
    const state = await scoringStore.loadTimerState()
    if (state && state.action) {
      restoreFromServer({
        action: state.action,
        maxTime: state.maxTime || scoringStore.currentPeriodDuration,
        // We persist the elapsed seconds in start_time (see store.setTimer)
        elapsed: state.startTime ?? 0,
        startTimeServer: state.startTimeServer ?? undefined,
        nowServer: state.nowServer
      })
    } else {
      timerSetPeriod(scoringStore.currentPeriodDuration)
    }
  } catch {
    // useApi already shows a toast
  }
})

const selectPlayer = (team: TeamSide, player: ScoringPlayer) => {
  if (!canScore.value) return
  selected.value = { team, player }
}

const addEvent = async (code: ScoringEventCode) => {
  if (!canScore.value || !match.value) return
  if (!selected.value) {
    toast.add({ title: t('common.error'), description: t('scoring.select_player_first'), color: 'warning' })
    return
  }
  const { team, player } = selected.value
  const event: ScoringEvent = {
    code,
    period: (match.value.periode ?? 'M1') as Period,
    tpsJeu: gameTime.value, // current game clock
    team,
    player: String(player.matric),
    number: player.numero,
    reason: ''
  }
  try {
    await scoringStore.addEvent(event)
    selected.value = null
  } catch { /* toast handled */ }
}

const setPeriod = (p: Period) => {
  if (!canScore.value) return
  scoringStore.setPeriod(p)
  // Reconfigure the clock to the new period duration (fresh countdown)
  timerSetPeriod(scoringStore.periodDurations[p])
}
const setStatus = (s: MatchStatus) => { if (canScore.value) scoringStore.setStatus(s) }

// ─── Timer controls (UI + server persistence to kp_chrono) ───
const timer = (action: 'run' | 'stop' | 'RAZ') => {
  if (!canScore.value) return
  const maxTime = scoringStore.currentPeriodDuration
  if (action === 'run') {
    timerStart()
  } else if (action === 'stop') {
    timerStop()
  } else {
    timerReset()
  }
  // Persist: elapsed seconds in the current period + period duration
  void scoringStore.setTimer(action, {
    startTime: action === 'RAZ' ? 0 : elapsed.value,
    runTime: 0,
    maxTime
  })
}

const toggleLock = async () => {
  if (!canValidate.value) return
  try {
    await scoringStore.toggleValidation()
  } catch { /* toast handled */ }
}
</script>

<template>
  <div class="p-4">
    <div v-if="!canView" class="text-center text-header-600 py-12">
      {{ t('scoring.no_access') }}
    </div>

    <div v-else-if="loading" class="text-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
    </div>

    <div v-else-if="match" class="space-y-4">
      <!-- Competition ended banner -->
      <UAlert
        v-if="scoringStore.isCompetitionEnded"
        icon="i-heroicons-lock-closed"
        color="warning"
        variant="soft"
        :title="t('competition.ended_title')"
        :description="t('competition.ended_description')"
      />

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold">{{ t('scoring.title') }} — #{{ match.id }}</h1>
          <p class="text-sm text-header-600">
            {{ match.codeCompetition }} · {{ match.phase }} · {{ t('scoring.field') }} {{ match.terrain }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <UBadge :color="scoringStore.isLocked ? 'error' : 'success'">
            {{ scoringStore.isLocked ? t('scoring.locked') : t('scoring.status.' + match.statut) }}
          </UBadge>
          <UButton
            v-if="canValidate"
            :icon="scoringStore.isLocked ? 'i-heroicons-lock-closed' : 'i-heroicons-lock-open'"
            :color="scoringStore.isLocked ? 'error' : 'neutral'"
            variant="soft"
            @click="toggleLock"
          />
        </div>
      </div>

      <!-- Score -->
      <div class="flex items-center justify-center gap-6 py-4 bg-header-50 rounded-lg">
        <div class="text-right flex-1">
          <div class="font-semibold">{{ match.equipeA }}</div>
        </div>
        <div class="text-4xl font-mono font-bold tabular-nums">
          {{ scoringStore.scoreA }} - {{ scoringStore.scoreB }}
        </div>
        <div class="text-left flex-1">
          <div class="font-semibold">{{ match.equipeB }}</div>
        </div>
      </div>

      <!-- Game clock -->
      <div class="flex items-center justify-center">
        <div
          class="text-5xl font-mono font-bold tabular-nums px-6 py-2 rounded-lg"
          :class="isRunning ? 'text-success-600' : 'text-header-700'"
        >
          {{ clockDisplay }}
        </div>
      </div>

      <!-- Status + Period + Timer -->
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex gap-1">
          <UButton
            v-for="s in statuses" :key="s"
            size="xs"
            :variant="match.statut === s ? 'solid' : 'outline'"
            :disabled="!canScore"
            @click="setStatus(s)"
          >{{ t('scoring.status.' + s) }}</UButton>
        </div>
        <div class="flex gap-1">
          <UButton
            v-for="p in periods" :key="p"
            size="xs"
            :variant="match.periode === p ? 'solid' : 'outline'"
            :disabled="!canScore"
            @click="setPeriod(p)"
          >{{ p }}</UButton>
        </div>
        <div class="flex gap-1">
          <UButton size="xs" icon="i-heroicons-play" :disabled="!canScore" @click="timer('run')">{{ t('scoring.timer.start') }}</UButton>
          <UButton size="xs" icon="i-heroicons-pause" :disabled="!canScore" @click="timer('stop')">{{ t('scoring.timer.pause') }}</UButton>
          <UButton size="xs" icon="i-heroicons-arrow-uturn-left" variant="outline" :disabled="!canScore" @click="timer('RAZ')">{{ t('scoring.timer.reset') }}</UButton>
        </div>
      </div>

      <!-- Teams + events -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(players, team) in { A: scoringStore.playersA, B: scoringStore.playersB }" :key="team">
          <h2 class="font-semibold mb-2">{{ team === 'A' ? match.equipeA : match.equipeB }}</h2>
          <div class="space-y-1">
            <button
              v-for="p in players" :key="p.matric"
              type="button"
              class="w-full flex items-center gap-2 px-2 py-1 rounded text-left text-sm border"
              :class="selected?.player.matric === p.matric
                ? 'border-primary-500 bg-primary-50'
                : 'border-header-200 hover:bg-header-50'"
              :disabled="!canScore"
              @click="selectPlayer(team as TeamSide, p)"
            >
              <span class="font-mono w-6 text-center">{{ p.numero }}</span>
              <span class="flex-1">{{ p.nom.toUpperCase() }} {{ p.prenom }}</span>
              <UBadge v-if="p.capitaine === 'C'" size="xs" variant="soft">Cap.</UBadge>
              <UBadge v-else-if="p.capitaine === 'E'" size="xs" variant="soft" color="neutral">Coach</UBadge>
            </button>
          </div>
        </div>
      </div>

      <!-- Event buttons -->
      <div class="flex flex-wrap gap-2 justify-center border-t pt-4">
        <UButton
          v-for="evt in eventCodes" :key="evt.code"
          :color="evt.color as any"
          :disabled="!canScore || !selected"
          @click="addEvent(evt.code)"
        >{{ t(evt.labelKey) }}</UButton>
      </div>

      <!-- Events list -->
      <div v-if="scoringStore.events.length" class="border-t pt-4">
        <h3 class="font-semibold mb-2">{{ t('scoring.history') }}</h3>
        <ul class="text-sm space-y-1">
          <li v-for="(e, i) in scoringStore.events" :key="i" class="flex gap-2">
            <span class="font-mono">{{ e.period }} {{ e.tpsJeu }}</span>
            <span>{{ e.team }}</span>
            <span class="flex-1">#{{ e.number }} · {{ e.code }}</span>
          </li>
        </ul>
      </div>
    </div>

    <div v-else class="text-center text-header-600 py-12">
      {{ t('scoring.not_found') }}
    </div>
  </div>
</template>
