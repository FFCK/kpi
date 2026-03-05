<script setup lang="ts">
import type { TvEvent, TvMatchesResponse, TvGlobalFilters, TvLabel, ControlPanel } from '~/types/tv'
import { createDefaultPanel } from '~/types/tv'
import { useAuthStore } from '~/stores/authStore'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()

const config = useRuntimeConfig()
const backendBaseUrl = config.public.legacyBaseUrl as string

// Profile guard: admin only (profile ≤ 2)
if (authStore.profile > 2) {
  navigateTo('/')
}

// ─── State ───

const activeTab = ref<'channels' | 'scenarios'>('channels')

const globalFilters = ref<TvGlobalFilters>({
  eventId: null,
  date: '',
  css: 'avranches2025',
  lang: 'en',
})

const events = ref<TvEvent[]>([])
const matchData = ref<TvMatchesResponse | null>(null)
const channelLabels = ref<TvLabel[]>([])
const scenarioLabels = ref<TvLabel[]>([])
const labelsModalOpen = ref(false)

// Dynamic panels
const panels = ref<ControlPanel[]>([createDefaultPanel()])

const PANELS_STORAGE_KEY = 'tv_panels'

// Season from match data
const season = computed(() => matchData.value?.season ?? '')

// ─── Data loading ───

async function loadEvents() {
  try {
    events.value = await api.get<TvEvent[]>('/admin/tv/events')
  }
  catch {}
}

async function loadMatches() {
  if (!globalFilters.value.eventId) {
    matchData.value = null
    return
  }

  try {
    const params: Record<string, string | number> = {
      eventId: globalFilters.value.eventId,
    }
    if (globalFilters.value.date) {
      params.date = globalFilters.value.date
    }
    matchData.value = await api.get<TvMatchesResponse>('/admin/tv/matches', params)
  }
  catch {}
}

async function loadLabels() {
  try {
    const data = await api.get<{ channels: TvLabel[]; scenarios: TvLabel[] }>('/admin/tv/labels')
    channelLabels.value = data.channels
    scenarioLabels.value = data.scenarios
  }
  catch {}
}

async function saveLabels(payload: { channels: TvLabel[]; scenarios: TvLabel[] }) {
  try {
    await api.put('/admin/tv/labels', payload)
    toast.add({ title: t('tv.labels.saved'), color: 'success', duration: 3000 })
    labelsModalOpen.value = false
    await loadLabels()
  }
  catch {}
}

// ─── Panel persistence ───

function restorePanelState() {
  const saved = localStorage.getItem(PANELS_STORAGE_KEY)
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      if (Array.isArray(parsed) && parsed.length > 0) {
        panels.value = parsed
      }
    }
    catch {}
  }
}

let saveTimeout: ReturnType<typeof setTimeout> | null = null
watch(panels, () => {
  if (saveTimeout) clearTimeout(saveTimeout)
  saveTimeout = setTimeout(() => {
    localStorage.setItem(PANELS_STORAGE_KEY, JSON.stringify(panels.value))
  }, 500)
}, { deep: true })

function addPanel() {
  panels.value.push(createDefaultPanel())
}

function removePanel(index: number) {
  if (panels.value.length > 1) {
    panels.value.splice(index, 1)
  }
}

// ─── Watchers ───

watch(() => globalFilters.value.eventId, () => {
  globalFilters.value.date = ''
  loadMatches()
})

// ─── Init ───

onMounted(async () => {
  await Promise.all([loadEvents(), loadLabels()])
  restorePanelState()
  // Load matches if an event was restored from localStorage
  if (globalFilters.value.eventId) {
    await loadMatches()
  }
})
</script>

<template>
  <div class="px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ t('tv.title') }}</h1>
      <button
        type="button"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-1"
        @click="labelsModalOpen = true"
      >
        <UIcon name="heroicons:cog-6-tooth" class="w-4 h-4" />
        {{ t('tv.labels.manage') }}
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-4 border-b border-gray-200">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'channels'
          ? 'border-blue-600 text-blue-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
        @click="activeTab = 'channels'"
      >
        {{ t('tv.tabs.channels') }}
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === 'scenarios'
          ? 'border-blue-600 text-blue-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
        @click="activeTab = 'scenarios'"
      >
        {{ t('tv.tabs.scenarios') }}
      </button>
    </div>

    <!-- Channels tab -->
    <div v-if="activeTab === 'channels'">
      <!-- Global bar -->
      <AdminTvGlobalBar
        v-model="globalFilters"
        :events="events"
        :match-data="matchData"
      />

      <div v-if="!globalFilters.eventId" class="mt-6 text-center text-gray-500 py-8">
        {{ t('tv.messages.select_event') }}
      </div>

      <template v-else>
        <!-- Panels -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-4">
          <AdminTvChannelPanel
            v-for="(panel, i) in panels"
            :key="panel.id"
            v-model="panels[i]"
            :match-data="matchData"
            :global-filters="globalFilters"
            :channel-labels="channelLabels"
            :scenario-labels="scenarioLabels"
            :season="season"
            @remove="removePanel(i)"
          />
        </div>

        <!-- Add panel button -->
        <div class="mt-4 text-center">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
            @click="addPanel"
          >
            + {{ t('tv.actions.add_panel') }}
          </button>
        </div>
      </template>

      <!-- Quick links -->
      <div class="mt-8 pt-4 border-t border-gray-200 flex flex-wrap gap-4 text-sm">
        <a
          :href="`${backendBaseUrl}/live/event.php`"
          target="_blank"
          class="text-blue-600 hover:text-blue-800 hover:underline"
        >
          {{ t('tv.links.event_cache') }}
        </a>
        <a
          :href="`${backendBaseUrl}/live/spliturl.php`"
          target="_blank"
          class="text-blue-600 hover:text-blue-800 hover:underline"
        >
          {{ t('tv.links.split_url') }}
        </a>
        <!-- <a
          :href="`${backendBaseUrl}/live/scenario.php`"
          target="_blank"
          class="text-blue-600 hover:text-blue-800 hover:underline"
        >
          {{ t('tv.links.scenario_live') }}
        </a> -->
      </div>
    </div>

    <!-- Scenarios tab -->
    <div v-if="activeTab === 'scenarios'">
      <AdminTvScenarioEditor :scenario-labels="scenarioLabels" />
    </div>

    <!-- Labels modal -->
    <AdminTvLabelsModal
      :open="labelsModalOpen"
      :channel-labels="channelLabels"
      :scenario-labels="scenarioLabels"
      @close="labelsModalOpen = false"
      @save="saveLabels"
    />
  </div>
</template>
