<script setup lang="ts">
import type { FilterEvent } from '~/types'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const config = useRuntimeConfig()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()
const toast = useToast()

// Legacy backend base URL (e.g. https://kpi.localhost)
const legacyBase = config.public.legacyBaseUrl as string

// State
const loading = ref(true)
const events = ref<FilterEvent[]>([])
const selectedEventId = ref<number | null>(null)
const matchIds = ref<number[]>([])
const loadingMatchIds = ref(false)

// Computed: user profile
const profile = computed(() => authStore.user?.profile ?? 99)

// Computed: competition type for ranking documents
const competitionType = computed(() => workContext.pageCompetition?.codeTypeclt ?? null)

// Load events
const loadEvents = async () => {
  try {
    const response = await api.get<{ events: FilterEvent[] }>('/admin/filters/events')
    events.value = response.events
    // Auto-select first event
    const firstEvent = events.value[0] as FilterEvent | undefined
    if (firstEvent && !selectedEventId.value) {
      selectedEventId.value = firstEvent.id
    }
  } catch {
    // Silently fail - events are only needed for profile <= 2
  }
}

// Load match IDs for selected competition
const loadMatchIds = async () => {
  if (!workContext.season || !workContext.pageCompetitionCode) {
    matchIds.value = []
    return
  }
  loadingMatchIds.value = true
  try {
    const response = await api.get<{ matchIds: number[] }>('/admin/filters/match-ids', {
      season: workContext.season,
      competition: workContext.pageCompetitionCode
    })
    matchIds.value = response.matchIds
  } catch {
    matchIds.value = []
  } finally {
    loadingMatchIds.value = false
  }
}

// Competition change handler from selector component
function onCompetitionChange() {
  loadMatchIds()
}

// Watch page competition changes to reload match IDs
watch(
  () => workContext.pageCompetitionCode,
  (code) => {
    if (code) {
      loadMatchIds()
    }
    else {
      matchIds.value = []
    }
  },
)

// Build legacy PDF URL with season + competition params
// Uses full legacy base URL (different host from app4)
const pdfUrl = (file: string, extra?: Record<string, string | number>): string => {
  const params = new URLSearchParams()
  params.set('S', workContext.season)
  params.set('Compet', workContext.pageCompetitionCode)
  if (extra) {
    Object.entries(extra).forEach(([k, v]) => params.set(k, String(v)))
  }
  return `${legacyBase}/admin/${file}?${params.toString()}`
}

// Build event PDF URL
const eventPdfUrl = (file: string, paramName: string): string => {
  if (!selectedEventId.value) return '#'
  const params = new URLSearchParams()
  params.set(paramName, String(selectedEventId.value))
  // Public PDFs (PdfXxx.php) are at root, admin PDFs at /admin/
  if (file.startsWith('Pdf')) {
    return `${legacyBase}/${file}?${params.toString()}`
  }
  return `${legacyBase}/admin/${file}?${params.toString()}`
}

// Build app4 stats route
const statsRoute = (type: string): string => {
  return `/stats/${type}/${workContext.season}/${workContext.pageCompetitionCode}`
}

// Match sheets URL (using match IDs)
const matchSheetsUrl = computed(() => {
  if (matchIds.value.length === 0) return null
  const listMatch = matchIds.value.join(',')
  return pdfUrl('FeuilleMatchMulti.php', { listMatch })
})

// Cumulated cards URL
const cardsUrl = computed(() => {
  return pdfUrl('FeuilleCards.php')
})

// Has valid competition selected
const hasCompetition = computed(() => !!workContext.pageCompetitionCode && !!workContext.season)

// Has valid event selected
const hasEvent = computed(() => !!selectedEventId.value)

// Init
onMounted(async () => {
  loading.value = true
  await workContext.initContext()
  if (profile.value <= 2) {
    await loadEvents()
  }
  loading.value = false
})
</script>

<template>
  <div>
    <!-- Work Context Summary -->
    <AdminWorkContextSummary />

    <!-- Page header -->
    <div class="mb-2">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('documents.title') }}
      </h1>
    </div>

    <!-- Competition selector -->
    <div class="bg-white rounded-lg shadow p-4 mb-2">
      <div class="flex flex-wrap items-end gap-4">
        <div class="w-full sm:w-auto flex-1 min-w-0">
          <AdminCompetitionSingleSelect @change="onCompetitionChange" />
        </div>

        <!-- Competition info badges -->
        <div v-if="workContext.pageCompetition" class="flex items-center gap-2">
          <span
            v-if="workContext.pageCompetition.enActif"
            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
          >
            EN
          </span>
          <span
            v-if="workContext.pageCompetition.codeTypeclt"
            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700"
          >
            {{ workContext.pageCompetition.codeTypeclt }}
          </span>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center py-12 text-gray-500">
      <UIcon name="heroicons:arrow-path" class="w-8 h-8 animate-spin mx-auto mb-3" />
      {{ t('common.loading') }}
    </div>

    <!-- Document categories grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

      <!-- ÉQUIPES -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-blue-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:user-group" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.teams') }}</h2>
        </div>
        <div class="p-4 space-y-2">
          <a
            :href="pdfUrl('FeuilleGroups.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.teams.registered') }}
          </a>
          <a
            :href="pdfUrl('FeuillePresence.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.teams.presence_fr') }}
          </a>
          <a
            :href="pdfUrl('FeuillePresenceEN.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.teams.presence_en') }}
          </a>
          <a
            :href="pdfUrl('FeuillePresenceVisa.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.teams.presence_visa') }}
          </a>
          <a
            :href="pdfUrl('FeuillePresencePhoto.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.teams.presence_photo') }}
          </a>
        </div>
      </div>

      <!-- MATCHS -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-green-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:trophy" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.matches') }}</h2>
        </div>
        <div class="p-4 space-y-2">
          <a
            :href="pdfUrl('FeuilleListeMatchs.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.matches.list_fr') }}
          </a>
          <a
            :href="pdfUrl('FeuilleListeMatchsEN.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.matches.list_en') }}
          </a>
          <a
            :href="pdfUrl('tableau_openspout.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:table-cells" class="w-4 h-4 text-gray-400" />
            {{ t('documents.matches.export_spreadsheet') }}
          </a>
          <a
            v-if="matchSheetsUrl"
            :href="matchSheetsUrl"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
          >
            <UIcon name="heroicons:document-duplicate" class="w-4 h-4 text-gray-400" />
            {{ t('documents.matches.match_sheets') }}
            <span class="text-xs text-gray-400 ml-auto">{{ matchIds.length }} {{ t('documents.matches.matches_count') }}</span>
          </a>
          <div
            v-else-if="loadingMatchIds"
            class="flex items-center gap-2 px-3 py-2 text-gray-400"
          >
            <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
            {{ t('common.loading') }}
          </div>
          <div
            v-else
            class="flex items-center gap-2 px-3 py-2 text-gray-400"
          >
            <UIcon name="heroicons:document-duplicate" class="w-4 h-4" />
            {{ t('documents.matches.match_sheets') }}
            <span class="text-xs ml-auto">{{ t('documents.matches.no_matches') }}</span>
          </div>
        </div>
      </div>

      <!-- CLASSEMENTS -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-amber-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:chart-bar" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.rankings') }}</h2>
        </div>
        <div class="p-4 space-y-2">
          <!-- CHPT type -->
          <template v-if="competitionType === 'CHPT'">
            <a
              :href="pdfUrl('FeuilleCltChpt.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.general') }}
            </a>
            <a
              :href="pdfUrl('FeuilleCltChptDetail.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.detail_team') }}
            </a>
            <a
              :href="pdfUrl('FeuilleCltNiveauJournee.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.detail_gameday') }}
            </a>
          </template>

          <!-- CP type -->
          <template v-else-if="competitionType === 'CP'">
            <a
              :href="pdfUrl('FeuilleCltNiveau.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.general') }}
            </a>
            <a
              :href="pdfUrl('FeuilleCltNiveauPhase.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.detail_phase') }}
            </a>
            <a
              :href="pdfUrl('FeuilleCltNiveauDetail.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.detail_team') }}
            </a>
          </template>

          <!-- MULTI type -->
          <template v-else-if="competitionType === 'MULTI'">
            <a
              :href="pdfUrl('FeuilleCltMulti.php')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.rankings.multi') }}
            </a>
          </template>

          <!-- No type / no competition selected -->
          <template v-else>
            <p class="px-3 py-2 text-sm text-gray-400 italic">
              {{ hasCompetition ? t('documents.rankings.no_type') : t('documents.select_competition') }}
            </p>
          </template>
        </div>
      </div>

      <!-- STATISTIQUES -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-purple-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:chart-bar-square" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.statistics') }}</h2>
        </div>
        <div class="p-4 space-y-2">
          <NuxtLink
            v-for="stat in [
              { type: 'Buteurs', label: t('documents.stats.scorers') },
              { type: 'Attaque', label: t('documents.stats.attack') },
              { type: 'Defense', label: t('documents.stats.defense') },
              { type: 'Cartons', label: t('documents.stats.cards_players') },
              { type: 'CartonsEquipe', label: t('documents.stats.cards_teams') },
              { type: 'Fairplay', label: t('documents.stats.fairplay_players') },
              { type: 'FairplayEquipe', label: t('documents.stats.fairplay_teams') },
              { type: 'Arbitrage', label: t('documents.stats.refereeing_refs') },
              { type: 'ArbitrageEquipe', label: t('documents.stats.refereeing_teams') }
            ]"
            :key="stat.type"
            :to="hasCompetition ? statsRoute(stat.type) : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-purple-400" />
            {{ stat.label }}
          </NuxtLink>
        </div>
      </div>

      <!-- ÉVÉNEMENT (profile <= 2 only) -->
      <div v-if="profile <= 2" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-teal-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:calendar" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.event') }}</h2>
        </div>
        <div class="p-4 space-y-3">
          <!-- Event selector -->
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">
              {{ t('documents.event.select') }}
            </label>
            <select
              v-model="selectedEventId"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option v-for="evt in events" :key="evt.id" :value="evt.id">
                {{ evt.libelle }}
              </option>
            </select>
          </div>

          <div class="space-y-2">
            <a
              :href="eventPdfUrl('FeuilleListeMatchs.php', 'idEvenement')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasEvent }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.event.matches_fr') }}
            </a>
            <a
              :href="eventPdfUrl('FeuilleListeMatchsEN.php', 'idEvenement')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasEvent }"
            >
              <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
              {{ t('documents.event.matches_en') }}
            </a>
            <a
              :href="eventPdfUrl('PdfQrCodes.php', 'Evt')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasEvent }"
            >
              <UIcon name="heroicons:qr-code" class="w-4 h-4 text-gray-400" />
              {{ t('documents.event.qr_codes') }}
            </a>
            <a
              :href="eventPdfUrl('PdfQrCodeApp.php', 'Evt')"
              target="_blank"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
              :class="{ 'opacity-40 pointer-events-none': !hasEvent }"
            >
              <UIcon name="heroicons:qr-code" class="w-4 h-4 text-gray-400" />
              {{ t('documents.event.qr_code_app') }}
            </a>
          </div>
        </div>
      </div>

      <!-- CONTRÔLE (profile <= 6 only) -->
      <div v-if="profile <= 6" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-red-600 px-4 py-3 flex items-center gap-2">
          <UIcon name="heroicons:shield-check" class="w-5 h-5 text-white" />
          <h2 class="text-white font-semibold">{{ t('documents.categories.control') }}</h2>
        </div>
        <div class="p-4 space-y-2">
          <a
            :href="pdfUrl('FeuillePresenceCat.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.control.presence_category') }}
          </a>
          <a
            :href="pdfUrl('FeuillePresenceU21.php')"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.control.presence_u21') }}
          </a>
          <NuxtLink
            :to="hasCompetition ? statsRoute('CJouees') : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-red-400" />
            {{ t('documents.control.competitions_played_club') }}
          </NuxtLink>
          <NuxtLink
            :to="hasCompetition ? statsRoute('CJouees2') : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-red-400" />
            {{ t('documents.control.competitions_played_team') }}
          </NuxtLink>
          <NuxtLink
            :to="hasCompetition ? statsRoute('CJouees3') : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-red-400" />
            {{ t('documents.control.irregularities') }}
          </NuxtLink>
          <NuxtLink
            :to="hasCompetition ? statsRoute('LicenciesNationaux') : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-red-400" />
            {{ t('documents.control.national_licensees') }}
          </NuxtLink>
          <NuxtLink
            :to="hasCompetition ? statsRoute('CoherenceMatchs') : undefined"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4 text-red-400" />
            {{ t('documents.control.match_consistency') }}
          </NuxtLink>
          <a
            :href="cardsUrl"
            target="_blank"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors"
            :class="{ 'opacity-40 pointer-events-none': !hasCompetition }"
          >
            <UIcon name="heroicons:document-text" class="w-4 h-4 text-gray-400" />
            {{ t('documents.control.cumulated_cards') }}
          </a>
        </div>
      </div>
    </div>

    <!-- Scroll to top button -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
