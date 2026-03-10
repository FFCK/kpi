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

// Computed: CHPT phases sorted by dateDebut then lieu
const chptPhases = computed(() => {
  if (!schemaData.value) return []
  return [...schemaData.value.phases].sort((a, b) => {
    const da = a.dateDebut ?? ''
    const db = b.dateDebut ?? ''
    if (da !== db) return da.localeCompare(db)
    const la = a.lieu ?? ''
    const lb = b.lieu ?? ''
    return la.localeCompare(lb)
  })
})

// Count distinct teams from matches in a phase
const countTeamsFromMatches = (phase: SchemaPhase): number => {
  const teamIds = new Set<number>()
  for (const m of phase.matches) {
    if (m.idEquipeA) teamIds.add(m.idEquipeA)
    if (m.idEquipeB) teamIds.add(m.idEquipeB)
  }
  return teamIds.size
}

// Computed: image visibility
const hasLogo = computed(() => competition.value?.logoActif && competition.value?.logoLink && showLogo.value)
const hasBandeau = computed(() => competition.value?.bandeauActif && competition.value?.bandeauLink && showBandeau.value)
const hasSponsor = computed(() => competition.value?.sponsorActif && competition.value?.sponsorLink && showSponsor.value)

// Badge helpers
const getLevelColor = (level: string) => {
  switch (level) {
    case 'INT': return 'bg-purple-100 text-purple-800'
    case 'NAT': return 'bg-primary-100 text-primary-800'
    case 'REG': return 'bg-orange-100 text-orange-800'
    default: return 'bg-header-100 text-header-800'
  }
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'CP': return 'bg-success-100 text-success-800'
    case 'CHPT': return 'bg-primary-100 text-primary-800'
    case 'MULTI': return 'bg-amber-100 text-amber-800'
    default: return 'bg-header-100 text-header-800'
  }
}

// Image URL builder
const imageUrl = (link: string | null) => {
  if (!link) return ''
  return `${legacyBase}${link}`
}

// Format date for display (YYYY-MM-DD → DD/MM/YYYY or locale format)
const formatDate = (date: string | null): string => {
  if (!date) return ''
  const d = new Date(date)
  if (isNaN(d.getTime())) return ''
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// Format date range
const formatDateRange = (debut: string | null, fin: string | null): string => {
  const d = formatDate(debut)
  const f = formatDate(fin)
  if (!d && !f) return ''
  if (d === f) return d
  if (!f) return d
  if (!d) return f
  return `${d} → ${f}`
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
    <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin text-header-400 mx-auto" />
  </div>

  <div v-else-if="competition" class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Zone B: Key Data -->
    <div class="flex flex-wrap items-center gap-3 px-6 py-4 border-b">
      <!-- Title -->
      <div class="mr-auto">
        <div class="flex items-center gap-3">
          <!-- Logo -->
          <img
            v-if="hasLogo"
            :src="imageUrl(competition.logoLink)"
            :alt="competition.libelle"
            class="max-h-16 object-contain inline mr-3"
            @error="showLogo = false"
          >
          <div>
            <h2 class="text-lg font-semibold text-header-900">
              {{ competition.libelle }}
              <!-- Season badge -->
              <span class="px-2 py-1 text-xs font-medium rounded bg-primary-50 text-primary-700">
                {{ competition.codeSaison }}
              </span>
            </h2>
            <p v-if="competition.soustitre2" class="text-sm text-header-700">
              {{ competition.soustitre2 }}
            </p>
          </div>
        </div>
      </div>

      <!-- Badges -->
      <div class="flex flex-wrap items-center gap-2 ml-auto">
        <span class="px-2 py-1 text-xs font-medium rounded" :class="getLevelColor(competition.codeNiveau)">
          {{ competition.codeNiveau }}
        </span>
        <span class="px-2 py-1 text-xs font-medium rounded" :class="getTypeColor(competition.codeTypeclt)">
          {{ competition.codeTypeclt }}
        </span>
        <span class="px-2 py-1 text-xs font-medium rounded bg-header-100 text-header-700">
          {{ t('documents.summary.teams_count', { count: competition.nbEquipes }, competition.nbEquipes) }}
        </span>
        <span class="px-2 py-1 text-xs font-medium rounded bg-header-100 text-header-700">
          {{ t('documents.summary.phases_count', { count: competition.nbJournees }, competition.nbJournees) }}
        </span>
        <span class="px-2 py-1 text-xs font-medium rounded bg-header-100 text-header-700">
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
          class="px-2 py-1 text-xs font-medium rounded bg-danger-50 text-danger-700"
        >
          {{ t('documents.summary.eliminated', { count: competition.elimines }) }}
        </span>
      </div>
    </div>

    <!-- Bandeau (above phases) -->
    <div v-if="hasBandeau" class="flex justify-center px-6 pt-4">
      <img
        :src="imageUrl(competition.bandeauLink)"
        :alt="competition.libelle"
        class="max-h-20 object-contain"
        @error="showBandeau = false"
      >
    </div>

    <!-- Zone C: Phase Structure -->
    <div v-if="schemaData && schemaData.phases.length > 0" class="px-6 py-4">
      <!-- CP: columns by stage -->
      <div v-if="isCp" class="overflow-x-auto">
        <div class="flex gap-3 min-w-0">
          <div
            v-for="stage in stageColumns"
            :key="stage.etape"
            class="flex-1 min-w-36 rounded-lg p-3 border border-header-200"
          >
            <NuxtLink
              v-for="phase in stage.phases"
              :key="phase.idJournee"
              :to="`/games?phase=${phase.idJournee}`"
              class="block mb-2 last:mb-0 hover:bg-header-100 rounded px-1 py-0.5 transition-colors"
            >
                <div class="flex items-center justify-center gap-1.5">
                <span
                  class="inline-block w-5 h-5 text-center text-xs font-bold leading-5 rounded"
                  :class="phase.type === 'C' ? 'bg-primary-100 text-primary-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ phase.type }}
                </span>
                <span class="text-sm font-medium text-header-700 truncate">{{ phase.phase }}</span>
                </div>
              <div class="flex items-center justify-center text-xs text-header-400">
                <span v-if="phase.nbequipes">{{ phase.nbequipes }} {{ t('documents.summary.teams_short', { count: phase.nbequipes }, phase.nbequipes) }} - </span>{{ phase.nbMatchs }} {{ t('documents.summary.matches_short', { count: phase.nbMatchs }, phase.nbMatchs) }}
              </div>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- CHPT: vertical list of gamedays with details, sorted by date then lieu -->
      <div v-else-if="isChpt" class="flex flex-wrap gap-2">
        <NuxtLink
          v-for="phase in chptPhases"
          :key="phase.idJournee"
          :to="`/games?phase=${phase.idJournee}`"
          class="px-3 py-2 bg-header-50 rounded-lg hover:bg-header-100 transition-colors"
        >
          <div class="text-sm font-medium text-header-800">{{ phase.phase }}</div>
          <div class="text-xs text-header-500 mt-0.5 space-y-0.5">
            <div v-if="formatDateRange(phase.dateDebut, phase.dateFin)" class="flex items-center gap-1">
              <UIcon name="heroicons:calendar" class="w-3 h-3 text-header-400" />
              {{ formatDateRange(phase.dateDebut, phase.dateFin) }}
            </div>
            <div v-if="phase.lieu || phase.departement" class="flex items-center gap-1">
              <UIcon name="heroicons:map-pin" class="w-3 h-3 text-header-400" />
              <span v-if="phase.lieu">{{ phase.lieu }}</span>
              <span v-if="phase.lieu && phase.departement"> · </span>
              <span v-if="phase.departement">{{ phase.departement }}</span>
            </div>
            <div class="flex items-center gap-1">
              <UIcon name="heroicons:user-group" class="w-3 h-3 text-header-400" />
              {{ countTeamsFromMatches(phase) }} {{ t('documents.summary.teams_short', { count: countTeamsFromMatches(phase) }, countTeamsFromMatches(phase)) }}
              -
              {{ phase.nbMatchs }} {{ t('documents.summary.matches_short', { count: phase.nbMatchs }, phase.nbMatchs) }}
            </div>
          </div>
        </NuxtLink>
      </div>

      <!-- MULTI -->
      <p v-else-if="isMulti" class="text-sm text-header-500 italic">
        {{ t('documents.summary.multi_competition') }}
      </p>
    </div>

    <!-- No phases -->
    <div v-else-if="!loading && !isMulti" class="px-6 py-4">
      <p class="text-sm text-header-400 italic">{{ t('documents.summary.no_phases') }}</p>
    </div>

    <!-- Sponsor (below phases) -->
    <div v-if="hasSponsor" class="flex justify-center px-6 pb-4">
      <img
        :src="imageUrl(competition.sponsorLink)"
        alt="Sponsor"
        class="max-h-16 object-contain"
        @error="showSponsor = false"
      >
    </div>
  </div>
</template>
