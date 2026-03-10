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

// Display toggles
const showMatchCount = ref(true)
const showTimeSlots = ref(true)

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

// Close button: close the tab/window
function closeWindow() {
  window.close()
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
    <div v-else-if="data">
      <!-- Header (with images, printable) -->
      <SchemaHeader
        :competition="data.competition"
        :total-matches="data.totalMatches"
        :show-match-count="showMatchCount"
        :show-time-slots="showTimeSlots"
        :is-cp="isCp"
      />

      <!-- Bandeau (above schema) -->
      <div v-if="hasBandeau" class="flex justify-center py-4">
        <img
          :src="imageUrl(data.competition.bandeauLink)"
          :alt="data.competition.libelle"
          class="max-h-20 object-contain"
          @error="showBandeau = false"
        >
      </div>

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

      <!-- Sponsor (below schema) -->
      <div v-if="hasSponsor" class="flex justify-center py-4">
        <img
          :src="imageUrl(data.competition.sponsorLink)"
          alt="Sponsor"
          class="max-h-16 object-contain"
          @error="showSponsor = false"
        >
      </div>
    </div>

    <!-- No data -->
    <div v-else class="bg-white rounded-lg shadow p-8 text-center text-header-500">
      {{ t('schema.no_data') }}
    </div>
  </div>
</template>

<style scoped>
@media print {
  @page {
    size: A4 landscape;
    margin: 10mm;
  }
}
</style>
