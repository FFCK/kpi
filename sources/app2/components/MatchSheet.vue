<template>
  <div v-if="matchData" class="max-w-4xl mx-auto">
    <!-- Match Header -->
    <div class="bg-gray-800 text-white rounded-t-lg p-4">
      <div class="text-center">
        <div class="text-sm text-gray-300">{{ matchData.game.c_label }} - {{ matchData.game.d_phase }}</div>
        <div class="text-xs text-gray-400 mt-1">
          {{ t('MatchSheet.Match') }} #{{ matchData.game.g_number }} |
          {{ matchData.game.d_place }} |
          {{ t('MatchSheet.Pitch') }} {{ matchData.game.g_pitch }}
        </div>
        <div class="text-xs text-gray-400 mt-1">
          <NuxtTime :datetime="matchData.game.g_date" day="numeric" month="long" year="numeric" :locale="locale" />
          - {{ matchData.game.g_time?.substring(0, 5) }}
          <span v-if="matchData.game.g_time_end && matchData.game.g_time_end !== '00:00:00'">
            {{ t('MatchSheet.To') }} {{ matchData.game.g_time_end?.substring(0, 5) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Score Section -->
    <div class="bg-gray-100 p-4 border-x border-gray-300">
      <div class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center">
        <!-- Team A -->
        <div class="text-center">
          <img v-if="matchData.team_a.logo" :src="getLogoUrl(matchData.team_a.logo)" class="h-16 w-16 mx-auto mb-2" alt="" />
          <div class="font-bold text-lg">{{ matchData.team_a.label }}</div>
        </div>

        <!-- Score -->
        <div class="text-center">
          <div class="flex items-center justify-center gap-2">
            <span class="lcd text-4xl font-bold px-4 py-2 bg-gray-800 text-white rounded">{{ matchData.game.g_score_a ?? '-' }}</span>
            <span class="text-2xl text-gray-400">-</span>
            <span class="lcd text-4xl font-bold px-4 py-2 bg-gray-800 text-white rounded">{{ matchData.game.g_score_b ?? '-' }}</span>
          </div>
          <!-- Halftime score -->
          <div v-if="matchData.halftime_score" class="text-sm text-gray-500 mt-2">
            ({{ matchData.halftime_score.team_a }} - {{ matchData.halftime_score.team_b }})
          </div>
          <!-- Status badge -->
          <div :class="statusClass" class="inline-block text-white text-xs px-2 py-1 rounded mt-2">
            {{ statusLabel }}
          </div>
        </div>

        <!-- Team B -->
        <div class="text-center">
          <img v-if="matchData.team_b.logo" :src="getLogoUrl(matchData.team_b.logo)" class="h-16 w-16 mx-auto mb-2" alt="" />
          <div class="font-bold text-lg">{{ matchData.team_b.label }}</div>
        </div>
      </div>
    </div>

    <!-- Referees -->
    <div v-if="matchData.game.g_referee_1 || matchData.game.g_referee_2" class="bg-gray-50 px-4 py-2 border-x border-gray-300 text-center text-sm text-gray-600">
      <span class="font-medium">{{ t('MatchSheet.Referees') }}:</span>
      {{ formatReferee(matchData.game.g_referee_1) }}
      <span v-if="matchData.game.g_referee_2"> / {{ formatReferee(matchData.game.g_referee_2) }}</span>
    </div>

    <!-- Team Compositions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-0 border border-gray-300">
      <!-- Team A Players -->
      <div class="border-b md:border-b-0 md:border-r border-gray-300">
        <div class="bg-gray-200 px-3 py-2 font-bold text-sm">
          {{ matchData.team_a.label }}
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-xs">
            <tr>
              <th class="px-2 py-1 text-left">#</th>
              <th class="px-2 py-1 text-left">{{ t('MatchSheet.Player') }}</th>
              <th class="px-2 py-1 text-center">{{ t('MatchSheet.Goals') }}</th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-green-500 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-yellow-400 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-red-500 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="player in matchData.team_a.players" :key="player.licence" class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-2 py-1 text-gray-500">
                <span v-if="player.captain !== 'E'">#{{ player.number }}</span>
              </td>
              <td class="px-2 py-1">
                {{ player.firstname }} {{ player.name }}
                <span v-if="player.captain === 'C'" class="ml-1 bg-black text-white text-xs font-bold w-4 h-4 inline-flex items-center justify-center rounded-sm">C</span>
                <span v-if="player.captain === 'E'" class="ml-1 text-xs text-gray-500">({{ t('MatchSheet.Coach') }})</span>
              </td>
              <td class="px-2 py-1 text-center font-bold">{{ player.stats.goals || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.green_cards || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.yellow_cards || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.red_cards || '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Team B Players -->
      <div>
        <div class="bg-gray-200 px-3 py-2 font-bold text-sm">
          {{ matchData.team_b.label }}
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-xs">
            <tr>
              <th class="px-2 py-1 text-left">#</th>
              <th class="px-2 py-1 text-left">{{ t('MatchSheet.Player') }}</th>
              <th class="px-2 py-1 text-center">{{ t('MatchSheet.Goals') }}</th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-green-500 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-yellow-400 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
              <th class="px-2 py-1 text-center">
                <div class="inline-block bg-red-500 w-3 h-4 rounded-sm transform -rotate-12"></div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="player in matchData.team_b.players" :key="player.licence" class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-2 py-1 text-gray-500">
                <span v-if="player.captain !== 'E'">#{{ player.number }}</span>
              </td>
              <td class="px-2 py-1">
                {{ player.firstname }} {{ player.name }}
                <span v-if="player.captain === 'C'" class="ml-1 bg-black text-white text-xs font-bold w-4 h-4 inline-flex items-center justify-center rounded-sm">C</span>
                <span v-if="player.captain === 'E'" class="ml-1 text-xs text-gray-500">({{ t('MatchSheet.Coach') }})</span>
              </td>
              <td class="px-2 py-1 text-center font-bold">{{ player.stats.goals || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.green_cards || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.yellow_cards || '' }}</td>
              <td class="px-2 py-1 text-center">{{ player.stats.red_cards || '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Events Timeline -->
    <div v-if="matchData.events && matchData.events.length > 0" class="mt-4 border border-gray-300 rounded-lg overflow-hidden">
      <div class="bg-gray-800 text-white px-3 py-2 font-bold text-sm">
        {{ t('MatchSheet.Timeline') }}
      </div>
      <div class="divide-y divide-gray-200">
        <div v-for="(periodEvents, period) in groupedEvents" :key="period">
          <div class="bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
            {{ getPeriodLabel(period) }}
          </div>
          <div v-for="event in periodEvents" :key="event.e_id" class="px-3 py-2 flex items-center gap-3 hover:bg-gray-50">
            <div class="w-12 text-center text-xs text-gray-500 font-mono">{{ formatTime(event.e_time) }}</div>
            <!-- Event icon -->
            <div v-if="event.e_type === 'B'" class="w-6 h-6 bg-gray-800 rounded-full flex items-center justify-center">
              <div class="w-4 h-4 border-2 border-white rounded-full"></div>
            </div>
            <div v-else-if="event.e_type === 'V'" class="w-5 h-7 bg-green-500 rounded-sm transform -rotate-12"></div>
            <div v-else-if="event.e_type === 'J'" class="w-5 h-7 bg-yellow-400 rounded-sm transform -rotate-12"></div>
            <div v-else-if="event.e_type === 'R'" class="w-5 h-7 bg-red-500 rounded-sm transform -rotate-12"></div>
            <div v-else-if="event.e_type === 'D'" class="w-5 h-7 bg-red-800 rounded-sm transform -rotate-12 flex items-center justify-center text-white text-xs font-bold">E</div>
            <div v-else class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs">?</div>
            <!-- Event details -->
            <div class="flex-1">
              <span :class="event.e_team === 'A' ? 'text-blue-700' : 'text-red-700'" class="font-medium">
                {{ event.e_firstname }} {{ event.e_name }}
              </span>
              <span class="text-gray-500 text-sm ml-2">#{{ event.e_number }}</span>
              <span class="text-gray-400 text-xs ml-2">({{ event.e_team === 'A' ? matchData.team_a.label : matchData.team_b.label }})</span>
              <span v-if="event.e_motif" class="text-gray-500 text-xs ml-2">- {{ event.e_motif }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="mt-4 flex justify-center gap-4">
      <button
        @click="$emit('refresh')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors cursor-pointer"
      >
        <UIcon name="i-heroicons-arrow-path" class="h-5 w-5" />
        {{ t('MatchSheet.Refresh') }}
      </button>
      <a
        :href="getPdfUrl()"
        target="_blank"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
      >
        <UIcon name="i-heroicons-document-arrow-down" class="h-5 w-5" />
        {{ t('MatchSheet.DownloadPdf') }}
      </a>
    </div>
  </div>

  <!-- Loading state -->
  <div v-else-if="loading" class="text-center py-8">
    <div class="animate-spin inline-block w-8 h-8 border-4 border-gray-300 border-t-blue-600 rounded-full"></div>
    <p class="mt-2 text-gray-500">{{ t('MatchSheet.Loading') }}</p>
  </div>

  <!-- Error state -->
  <div v-else-if="error" class="text-center py-8">
    <UIcon name="i-heroicons-exclamation-circle" class="h-12 w-12 text-red-500 mx-auto" />
    <p class="mt-2 text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const { t, locale } = useI18n()
const runtimeConfig = useRuntimeConfig()
const baseUrl = runtimeConfig.public.backendBaseUrl

const props = defineProps({
  matchData: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  error: { type: String, default: null },
  gameId: { type: [Number, String], required: true }
})

defineEmits(['refresh'])

const statusClass = computed(() => {
  if (!props.matchData?.game) return 'bg-gray-500'
  if (props.matchData.game.g_status === 'END') {
    return props.matchData.game.g_validation === 'O' ? 'bg-green-500' : 'bg-orange-500'
  }
  return 'bg-blue-500'
})

const statusLabel = computed(() => {
  if (!props.matchData?.game) return ''
  if (props.matchData.game.g_status === 'ON') {
    return t('Games.Period.' + props.matchData.game.g_period)
  }
  if (props.matchData.game.g_status === 'END') {
    return props.matchData.game.g_validation === 'O' ? t('Games.Status.END') : t('Games.Status.Provisional')
  }
  return t('Games.Status.' + props.matchData.game.g_status)
})

const getLogoUrl = (logo) => {
  return `${baseUrl}/img/${logo}`
}

const getPdfUrl = () => {
  return `${baseUrl}/PdfMatchMulti.php?listMatch=${props.gameId}`
}

// Format referee name - remove level after parenthesis (e.g., "NAME (COUNTRY) INT-B" -> "NAME (COUNTRY)")
const formatReferee = (referee) => {
  if (!referee) return ''
  // Match everything up to and including the closing parenthesis, remove anything after
  return referee.replace(/(\([^)]+\)).*$/, '$1').trim()
}

const groupedEvents = computed(() => {
  if (!props.matchData?.events) return {}

  const groups = {}
  for (const event of props.matchData.events) {
    const period = event.e_period || 'M1'
    if (!groups[period]) groups[period] = []
    groups[period].push(event)
  }
  return groups
})

const getPeriodLabel = (period) => {
  const labels = {
    'M1': t('Games.Period.M1'),
    'M2': t('Games.Period.M2'),
    'P1': t('Games.Period.P1'),
    'P2': t('Games.Period.P2'),
    'TB': t('Games.Period.TB')
  }
  return labels[period] || period
}

const formatTime = (time) => {
  if (!time) return '--:--'
  // Time is in seconds or mm:ss format
  if (typeof time === 'number' || /^\d+$/.test(time)) {
    const totalSeconds = parseInt(time)
    const minutes = Math.floor(totalSeconds / 60)
    const seconds = totalSeconds % 60
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
  }
  // If already in mm:ss or hh:mm:ss format, extract mm:ss
  if (time.includes(':')) {
    const parts = time.split(':')
    if (parts.length >= 2) {
      const minutes = parts.length === 3 ? parts[1] : parts[0]
      const seconds = parts.length === 3 ? parts[2] : parts[1]
      return `${String(parseInt(minutes)).padStart(2, '0')}:${String(parseInt(seconds)).padStart(2, '0')}`
    }
  }
  return time
}
</script>

<style scoped>
@font-face {
  font-family: "LCD";
  src: url('~/assets/fonts/7segments.ttf');
}

.lcd {
  font-family: "LCD", Helvetica, Arial;
}
</style>
