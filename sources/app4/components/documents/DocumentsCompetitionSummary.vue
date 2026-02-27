<script setup lang="ts">
import type { AdminCompetition } from '~/types/competitions'
import type { SchemaResponse, SchemaPhase } from '~/types/schema'

const props = defineProps<{
  competitionCode: string
  season: string
}>()

const { t } = useI18n()
const api = useApi()
const config = useRuntimeConfig()
const legacyBase = config.public.legacyBaseUrl as string

// State
const competition = ref<AdminCompetition | null>(null)
const schemaData = ref<SchemaResponse | null>(null)
const loading = ref(false)

// Image visibility (hidden on 404)
const showBandeau = ref(true)
const showLogo = ref(true)
const showSponsor = ref(true)

// Computed: stage columns for CP layout (same pattern as schema.vue)
const stageColumns = computed(() => {
  if (!schemaData.value) return []
  const stageMap = new Map<number, SchemaPhase[]>()
  for (const phase of schemaData.value.phases) {
    const arr = stageMap.get(phase.etape) || []
    arr.push(phase)
    stageMap.set(phase.etape, arr)
  }
  return Array.from(stageMap.entries())
    .sort((a, b) => a[0] - b[0])
    .map(([etape, phases]) => ({ etape, phases }))
})

// Computed: is CP type
const isCp = computed(() => competition.value?.codeTypeclt === 'CP')
const isChpt = computed(() => competition.value?.codeTypeclt === 'CHPT')
const isMulti = computed(() => competition.value?.codeTypeclt === 'MULTI')

// Computed: has any visible image
const hasImages = computed(() => {
  if (!competition.value) return false
  return (competition.value.bandeauActif && competition.value.bandeauLink && showBandeau.value)
    || (competition.value.logoActif && competition.value.logoLink && showLogo.value)
    || (competition.value.sponsorActif && competition.value.sponsorLink && showSponsor.value)
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

const getTypeColor = (type: string) => {
  switch (type) {
    case 'CP': return 'bg-green-100 text-green-800'
    case 'CHPT': return 'bg-blue-100 text-blue-800'
    case 'MULTI': return 'bg-amber-100 text-amber-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

// Image URL builder
const imageUrl = (link: string | null) => {
  if (!link) return ''
  return `${legacyBase}${link}`
}

// Load data
const loadSummary = async () => {
  if (!props.competitionCode || !props.season) return

  loading.value = true
  showBandeau.value = true
  showLogo.value = true
  showSponsor.value = true

  try {
    const [compData, schema] = await Promise.all([
      api.get<AdminCompetition>(`/admin/competitions/${props.competitionCode}`, { season: props.season }),
      api.get<SchemaResponse>('/admin/schema', {
        season: props.season,
        competition: props.competitionCode,
      }).catch(() => null),
    ])
    competition.value = compData
    schemaData.value = schema
  }
  catch {
    competition.value = null
    schemaData.value = null
  }
  finally {
    loading.value = false
  }
}

// Watch competition changes
watch(
  () => props.competitionCode,
  (code) => {
    if (code) loadSummary()
    else {
      competition.value = null
      schemaData.value = null
    }
  },
)

// Load on mount
onMounted(() => {
  if (props.competitionCode) loadSummary()
})
</script>

<template>
  <div v-if="loading" class="bg-white rounded-lg shadow p-6 text-center">
    <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin text-gray-400 mx-auto" />
  </div>

  <div v-else-if="competition" class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Zone A: Images -->
    <div
      v-if="hasImages"
      class="flex flex-wrap items-center justify-center gap-6 px-6 py-4 bg-gray-50 border-b"
    >
      <img
        v-if="competition.bandeauActif && competition.bandeauLink && showBandeau"
        :src="imageUrl(competition.bandeauLink)"
        :alt="competition.libelle"
        class="max-h-20 object-contain"
        @error="showBandeau = false"
      >
      <img
        v-if="competition.logoActif && competition.logoLink && showLogo"
        :src="imageUrl(competition.logoLink)"
        :alt="competition.libelle"
        class="max-h-16 object-contain"
        @error="showLogo = false"
      >
      <img
        v-if="competition.sponsorActif && competition.sponsorLink && showSponsor"
        :src="imageUrl(competition.sponsorLink)"
        alt="Sponsor"
        class="max-h-16 object-contain"
        @error="showSponsor = false"
      >
    </div>

    <!-- Zone B: Key Data -->
    <div class="flex flex-wrap items-center gap-3 px-6 py-4 border-b">
      <!-- Title -->
      <div class="mr-auto">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ competition.libelle }}
        </h3>
        <p v-if="competition.soustitre2" class="text-sm text-gray-500">
          {{ competition.soustitre2 }}
        </p>
      </div>

      <!-- Badges -->
      <span class="px-2 py-1 text-xs font-medium rounded" :class="getLevelColor(competition.codeNiveau)">
        {{ competition.codeNiveau }}
      </span>
      <span class="px-2 py-1 text-xs font-medium rounded" :class="getTypeColor(competition.codeTypeclt)">
        {{ competition.codeTypeclt }}
      </span>
      <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
        {{ t('documents.summary.teams_count', { count: competition.nbEquipes }, competition.nbEquipes) }}
      </span>
      <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
        {{ t('documents.summary.phases_count', { count: competition.nbJournees }, competition.nbJournees) }}
      </span>
      <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
        {{ t('documents.summary.matches_count', { count: competition.nbMatchs }, competition.nbMatchs) }}
      </span>
      <span
        v-if="isCp && competition.qualifies > 0"
        class="px-2 py-1 text-xs font-medium rounded bg-emerald-50 text-emerald-700"
      >
        {{ t('documents.summary.qualified', { count: competition.qualifies }) }}
      </span>
      <span
        v-if="isCp && competition.elimines > 0"
        class="px-2 py-1 text-xs font-medium rounded bg-red-50 text-red-700"
      >
        {{ t('documents.summary.eliminated', { count: competition.elimines }) }}
      </span>
    </div>

    <!-- Zone C: Phase Structure -->
    <div v-if="schemaData && schemaData.phases.length > 0" class="px-6 py-4">
      <!-- CP: columns by stage -->
      <div v-if="isCp" class="overflow-x-auto">
        <div class="flex gap-3 min-w-0">
          <div
            v-for="stage in stageColumns"
            :key="stage.etape"
            class="flex-1 min-w-36 bg-gray-50 rounded-lg p-3"
          >
            <div
              v-for="phase in stage.phases"
              :key="phase.idJournee"
              class="mb-2 last:mb-0"
            >
              <div class="flex items-center gap-1.5">
                <span
                  class="inline-block w-5 h-5 text-center text-xs font-bold leading-5 rounded"
                  :class="phase.type === 'C' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ phase.type }}
                </span>
                <span class="text-sm font-medium text-gray-700 truncate">{{ phase.phase }}</span>
              </div>
              <div class="text-xs text-gray-400 ml-6.5">
                {{ phase.nbMatchs }} {{ t('documents.summary.matches_short', { count: phase.nbMatchs }, phase.nbMatchs) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CHPT: vertical list of gamedays -->
      <div v-else-if="isChpt" class="flex flex-wrap gap-2">
        <div
          v-for="phase in schemaData.phases"
          :key="phase.idJournee"
          class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 rounded-lg"
        >
          <span class="text-sm font-medium text-gray-700">{{ phase.phase }}</span>
          <span class="text-xs text-gray-400">
            ({{ phase.nbMatchs }} {{ t('documents.summary.matches_short', { count: phase.nbMatchs }, phase.nbMatchs) }})
          </span>
        </div>
      </div>

      <!-- MULTI -->
      <p v-else-if="isMulti" class="text-sm text-gray-500 italic">
        {{ t('documents.summary.multi_competition') }}
      </p>
    </div>

    <!-- No phases -->
    <div v-else-if="!loading && !isMulti" class="px-6 py-4">
      <p class="text-sm text-gray-400 italic">{{ t('documents.summary.no_phases') }}</p>
    </div>
  </div>
</template>
