<script setup lang="ts">
import type { SchemaResponse, SchemaPhase } from '~/types/schema'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const { t, locale } = useI18n()
const api = useApi()
const workContext = useWorkContextStore()
const toast = useToast()

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

// Load data
const loadSchema = async () => {
  if (!workContext.initialized || !workContext.season || !workContext.pageCompetitionCode) return

  loading.value = true
  try {
    const params: Record<string, string> = {
      season: workContext.season,
      competition: workContext.pageCompetitionCode,
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

// Watch competition changes
watch(
  () => workContext.pageCompetitionCode,
  (code) => {
    if (code) {
      loadSchema()
    }
    else {
      data.value = null
    }
  },
)

// Load on mount
onMounted(async () => {
  await workContext.initContext()
  if (workContext.pageCompetitionCode) {
    await loadSchema()
  }
})
</script>

<template>
  <div>
    <!-- Page header -->
    <AdminPageHeader
      :title="t('schema.title')"
      :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
    >
      <template #badges>
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
      </template>
    </AdminPageHeader>

    <!-- No competition selected -->
    <div v-if="!workContext.pageCompetitionCode" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
      {{ t('schema.no_competition') }}
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin text-gray-400 mx-auto" />
    </div>

    <!-- Content -->
    <div v-else-if="data">
      <!-- Header -->
      <SchemaHeader
        :competition="data.competition"
        :total-matches="data.totalMatches"
        :show-match-count="showMatchCount"
        :show-time-slots="showTimeSlots"
        :is-cp="isCp"
        @toggle-match-count="showMatchCount = !showMatchCount"
        @toggle-time-slots="showTimeSlots = !showTimeSlots"
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
