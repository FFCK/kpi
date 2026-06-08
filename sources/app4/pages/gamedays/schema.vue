<script setup lang="ts">
import type { SchemaResponse, SchemaPhase } from '~/types/schema'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t, locale } = useI18n()
const api = useApi()
const route = useRoute()
const toast = useToast()
const config = useRuntimeConfig()
const legacyBase = config.public.legacyBaseUrl as string

// Read params from URL (no workContext filters)
const competitionCode = computed(() => (route.query.competition as string) || '')
const season = computed(() => (route.query.season as string) || '')

// State
const loading = ref(false)
const data = ref<SchemaResponse | null>(null)

// Display toggles (persisted in localStorage)
const PREFS_KEY = 'schema-prefs'
function loadPref(key: string, fallback: boolean): boolean {
  try {
    const raw = localStorage.getItem(PREFS_KEY)
    if (!raw) return fallback
    const parsed = JSON.parse(raw)
    return typeof parsed[key] === 'boolean' ? parsed[key] : fallback
  }
  catch { return fallback }
}
function savePref(key: string, value: boolean) {
  try {
    const raw = localStorage.getItem(PREFS_KEY)
    const parsed = raw ? JSON.parse(raw) : {}
    parsed[key] = value
    localStorage.setItem(PREFS_KEY, JSON.stringify(parsed))
  }
  catch { /* ignore */ }
}

const showMatchCount = ref(loadPref('showMatchCount', true))
const showTimeSlots = ref(loadPref('showTimeSlots', true))

watch(showMatchCount, v => savePref('showMatchCount', v))
watch(showTimeSlots, v => savePref('showTimeSlots', v))

// Image visibility (hidden on 404)
const showBandeau = ref(true)
const showSponsor = ref(true)

const imageUrl = (link: string | null) => {
  if (!link) return ''
  return `${legacyBase}${link}`
}

const hasBandeau = computed(() => data.value?.competition.bandeauActif && data.value?.competition.bandeauLink && showBandeau.value)
const hasSponsor = computed(() => data.value?.competition.sponsorActif && data.value?.competition.sponsorLink && showSponsor.value)

// Hover highlight
const hoveredTeam = ref<string | null>(null)

// Computed
const isCp = computed(() => data.value?.competition.codeTypeclt === 'CP')

const stageColumns = computed(() => {
  if (!data.value || !isCp.value) return []
  const stageMap = new Map<number, SchemaPhase[]>()
  for (const phase of data.value.phases) {
    const arr = stageMap.get(phase.etape) || []
    arr.push(phase)
    stageMap.set(phase.etape, arr)
  }
  return Array.from(stageMap.entries())
    .sort((a, b) => a[0] - b[0])
    .map(([etape, phases]) => ({ etape, phases }))
})

// Badge color by level
function getLevelBadgeClass(level: string) {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-primary-100 text-primary-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-header-100 text-header-800'
  }
}

// Close button: close the tab/window
function closeWindow() {
  window.close()
}

// PDF export: set title for filename then print
function printPdf() {
  const competition = data.value?.competition
  const group = competition?.codeRef?.trim().replace(/\s+/g, '_') || ''
  const category = competition?.soustitre2?.trim().replace(/\s+/g, '_') || ''
  const parts = [group, 'Draw_progression', category].filter(Boolean)
  const prevTitle = document.title
  document.title = parts.join('_')
  window.print()
  document.title = prevTitle
}

// Load data
const loadSchema = async () => {
  if (!competitionCode.value || !season.value) return

  loading.value = true
  try {
    const params: Record<string, string> = {
      season: season.value,
      competition: competitionCode.value,
      lang: locale.value,
    }
    data.value = await api.get<SchemaResponse>('/admin/schema', params)
  }
  catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('schema.no_data')
    toast.add({ title: t('common.error'), description: message, color: 'error', duration: 3000 })
  }
  finally {
    loading.value = false
  }
}

// Load on mount
onMounted(async () => {
  if (competitionCode.value && season.value) {
    await loadSchema()
  }
})
</script>

<template>
  <div>
    <!-- Toolbar (non-printable) -->
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 print:hidden">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-header-900">{{ t('schema.title') }}</h1>
      </div>

      <!-- Right side: toggles + close -->
      <div class="flex items-center gap-3">
        <!-- Level + type badges -->
        <template v-if="data">
          <span
            class="px-2 py-1 text-xs font-medium rounded uppercase"
            :class="getLevelBadgeClass(data.competition.codeNiveau)"
          >
            {{ data.competition.codeNiveau }}
          </span>
          <span class="px-2 py-1 text-xs font-medium rounded uppercase bg-header-100 text-header-800">
            {{ data.competition.codeTypeclt }}
          </span>
        </template>

        <!-- Toggles (CP only) -->
        <template v-if="isCp">
          <label class="inline-flex items-center gap-1.5 text-xs text-header-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showMatchCount"
              class="rounded border-header-300 text-primary-600 focus:ring-primary-500"
              @change="showMatchCount = !showMatchCount"
            >
            {{ t('schema.show_game_count') }}
          </label>
          <label class="inline-flex items-center gap-1.5 text-xs text-header-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showTimeSlots"
              class="rounded border-header-300 text-primary-600 focus:ring-primary-500"
              @change="showTimeSlots = !showTimeSlots"
            >
            {{ t('schema.show_time_slots') }}
          </label>
        </template>

        <!-- PDF button -->
        <button
          v-if="data"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50 transition-colors"
          @click="printPdf"
        >
          <UIcon name="heroicons:arrow-down-tray" class="w-4 h-4" />
          {{ t('schema.export_pdf') }}
        </button>

        <!-- Close button -->
        <button
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-header-700 bg-white border border-header-300 rounded-lg hover:bg-header-50 transition-colors"
          @click="closeWindow"
        >
          <UIcon name="heroicons:x-mark" class="w-4 h-4" />
          {{ t('schema.close') }}
        </button>
      </div>
    </div>

    <!-- No params provided -->
    <div v-if="!competitionCode || !season" class="bg-white rounded-lg shadow p-8 text-center text-header-500">
      {{ t('schema.no_competition') }}
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin text-header-400 mx-auto" />
    </div>

    <!-- Content -->
    <div v-else-if="data" id="schema-print-area">
      <!-- Bandeau: screen (above header) + print fixed header -->
      <div v-if="hasBandeau" class="flex justify-center py-3 schema-bandeau-screen">
        <img
          :src="imageUrl(data.competition.bandeauLink)"
          :alt="data.competition.libelle"
          class="max-h-20 object-contain"
          @error="showBandeau = false"
        >
      </div>
      <div v-if="hasBandeau" class="schema-bandeau-print" aria-hidden="true">
        <img :src="imageUrl(data.competition.bandeauLink)" :alt="data.competition.libelle">
      </div>

      <!-- Header -->
      <SchemaHeader
        :competition="data.competition"
        :total-matches="data.totalMatches"
        :show-match-count="showMatchCount"
        :show-time-slots="showTimeSlots"
        :is-cp="isCp"
      />

      <!-- Schema (vertically centered in print via flex wrapper) -->
      <div class="schema-content-wrapper">
        <!-- CP Layout -->
        <SchemaCpLayout
          v-if="isCp"
          :stages="stageColumns"
          :show-match-count="showMatchCount"
          :show-time-slots="showTimeSlots"
          :hovered-team="hoveredTeam"
          :qualifies="data.competition.qualifies"
          :elimines="data.competition.elimines"
          @hover-team="hoveredTeam = $event"
        />

        <!-- CHPT Layout -->
        <SchemaChptLayout
          v-else
          :phases="data.phases"
          :hovered-team="hoveredTeam"
          @hover-team="hoveredTeam = $event"
        />
      </div>

      <!-- Sponsor: screen (below schema) + print fixed footer -->
      <div v-if="hasSponsor" class="flex justify-center py-3 schema-sponsor-screen">
        <img
          :src="imageUrl(data.competition.sponsorLink)"
          alt="Sponsor"
          class="max-h-16 object-contain"
          @error="showSponsor = false"
        >
      </div>
      <div v-if="hasSponsor" class="schema-sponsor-print" aria-hidden="true">
        <img :src="imageUrl(data.competition.sponsorLink)" alt="Sponsor">
      </div>
    </div>

    <!-- No data -->
    <div v-else class="bg-white rounded-lg shadow p-8 text-center text-header-500">
      {{ t('schema.no_data') }}
    </div>
  </div>
</template>

<style>
/* Screen: hide print-only duplicates */
.schema-bandeau-print,
.schema-sponsor-print {
  display: none;
}

@media print {
  @page {
    size: A4 landscape;
    margin: 10mm 10mm;
  }

  /* Hide everything, reveal only the print zone */
  body * {
    visibility: hidden;
  }

  #schema-print-area,
  #schema-print-area * {
    visibility: visible;
  }

  /* Print area: full page, flex column, content vertically centered */
  #schema-print-area {
    position: fixed;
    inset: 0;
    display: flex;
    flex-direction: column;
    /* top: bandeau height (25mm) + page margin (10mm) + gap (4mm) */
    /* bottom: sponsor height (20mm) + page margin (10mm) + gap (4mm) */
    padding: 39mm 0 34mm;
    box-sizing: border-box;
  }

  /* Hide screen duplicates */
  .schema-bandeau-screen,
  .schema-sponsor-screen {
    display: none !important;
  }

  /* Schema vertically centered in remaining space */
  .schema-content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  /* Bandeau: fixed at top */
  .schema-bandeau-print {
    display: flex !important;
    justify-content: center;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 25mm;
    background: white;
    visibility: visible;
  }

  .schema-bandeau-print img {
    max-height: 22mm;
    max-width: 100%;
    object-fit: contain;
  }

  /* Sponsor: fixed at bottom */
  .schema-sponsor-print {
    display: flex !important;
    justify-content: center;
    align-items: center;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 20mm;
    background: white;
    visibility: visible;
  }

  .schema-sponsor-print img {
    max-height: 16mm;
    max-width: 100%;
    object-fit: contain;
  }
}
</style>
