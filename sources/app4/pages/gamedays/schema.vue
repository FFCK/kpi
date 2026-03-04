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

// Read params from URL (no workContext filters)
const competitionCode = computed(() => (route.query.competition as string) || '')
const season = computed(() => (route.query.season as string) || '')

// State
const loading = ref(false)
const data = ref<SchemaResponse | null>(null)

// Display toggles
const showMatchCount = ref(true)
const showTimeSlots = ref(true)

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

// Badge helpers
const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-blue-100 text-blue-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

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
        <h1 class="text-2xl font-bold text-gray-900">{{ t('schema.title') }}</h1>
        <div v-if="data?.competition" class="flex items-center gap-2 flex-wrap">
          <span
            class="px-2 py-1 text-xs font-medium rounded uppercase"
            :class="getLevelColor(data.competition.codeNiveau)"
          >
            {{ data.competition.codeNiveau }}
          </span>
          <span class="px-2 py-1 text-xs font-medium rounded uppercase bg-gray-100 text-gray-800">
            {{ data.competition.codeTypeclt }}
          </span>
        </div>
      </div>

      <!-- Right side: toggles + close -->
      <div class="flex items-center gap-3">
        <!-- Toggles (CP only) -->
        <template v-if="isCp">
          <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showMatchCount"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @change="showMatchCount = !showMatchCount"
            >
            {{ t('schema.show_game_count') }}
          </label>
          <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
            <input
              type="checkbox"
              :checked="showTimeSlots"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              @change="showTimeSlots = !showTimeSlots"
            >
            {{ t('schema.show_time_slots') }}
          </label>
        </template>

        <!-- Close button -->
        <button
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          @click="closeWindow"
        >
          <UIcon name="heroicons:x-mark" class="w-4 h-4" />
          {{ t('schema.close') }}
        </button>
      </div>
    </div>

    <!-- No params provided -->
    <div v-if="!competitionCode || !season" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ t('schema.no_competition') }}
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin text-gray-400 mx-auto" />
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

    <!-- No data -->
    <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
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
