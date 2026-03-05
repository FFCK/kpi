<script setup lang="ts">
import type { ControlPanel, TvMatchesResponse, TvGlobalFilters, TvLabel } from '~/types/tv'

const props = defineProps<{
  matchData: TvMatchesResponse | null
  globalFilters: TvGlobalFilters
  channelLabels: TvLabel[]
  scenarioLabels: TvLabel[]
  season: string
}>()

const panel = defineModel<ControlPanel>({ required: true })

const emit = defineEmits<{
  remove: []
}>()

const { t } = useI18n()
const api = useApi()
const toast = useToast()
const { buildUrl, isDirectAction } = useTvUrl()

const activating = ref(false)

const config = useRuntimeConfig()
const backendBaseUrl = config.public.legacyBaseUrl as string

function generateUrl() {
  panel.value.generatedUrl = buildUrl(panel.value, props.globalFilters, props.season)
}

async function activate() {
  if (!panel.value.channel) return

  const url = buildUrl(panel.value, props.globalFilters, props.season)
  panel.value.generatedUrl = url

  // force_cache_match: direct call, no channel activation
  if (isDirectAction(panel.value.presentation)) {
    try {
      activating.value = true
      await api.get(`/${url}`)
      toast.add({ title: `Cache forced`, color: 'success', duration: 3000 })
    }
    catch {}
    finally { activating.value = false }
    return
  }

  try {
    activating.value = true
    await api.post('/admin/tv/activate', { voie: panel.value.channel, url })
    toast.add({
      title: t('tv.messages.activated', { channel: panel.value.channel }),
      color: 'success',
      duration: 3000,
    })
  }
  catch {}
  finally { activating.value = false }
}

async function blank() {
  if (!panel.value.channel) return

  try {
    activating.value = true
    await api.post('/admin/tv/blank', {
      voie: panel.value.channel,
      css: props.globalFilters.css,
    })
    toast.add({
      title: t('tv.messages.blanked', { channel: panel.value.channel }),
      color: 'success',
      duration: 3000,
    })
  }
  catch {}
  finally { activating.value = false }
}

function openControl() {
  if (!panel.value.channel) return
  window.open(`${backendBaseUrl}/live/tv2.php?voie=${panel.value.channel}`, '_blank')
}

function openReport() {
  if (!panel.value.match) return
  window.open(`${backendBaseUrl}/feuille_match_pdf.php?match=${panel.value.match}`, '_blank')
}
</script>

<template>
  <div class="bg-white rounded-lg shadow border border-gray-200 p-4 flex flex-col">
    <!-- Body: left form + right preview -->
    <div class="flex gap-4 flex-1">
      <!-- Left column: form fields -->
      <div class="flex-1 min-w-0 space-y-3">
        <!-- Channel + Presentation -->
        <div class="flex flex-wrap items-end gap-3">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.channel') }}</label>
            <AdminTvChannelSelector
              v-model="panel.channel"
              :labels="channelLabels"
              :scenario-labels="scenarioLabels"
            />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-600">{{ t('tv.panel.presentation') }}</label>
            <AdminTvPresentationSelector v-model="panel.presentation" />
          </div>
        </div>

        <!-- Conditional params -->
        <AdminTvConditionalParams
          v-model="panel"
          :match-data="matchData"
          :global-filters="globalFilters"
        />
      </div>

      <!-- Right column: close button + preview -->
      <div class="flex flex-col items-end gap-2 shrink-0">
        <button
          type="button"
          class="text-gray-400 hover:text-red-500 p-1 transition-colors"
          :title="t('tv.actions.remove_panel')"
          @click="emit('remove')"
        >
          <UIcon name="heroicons:x-mark" class="w-5 h-5" />
        </button>
        <AdminTvPresentationPreview :presentation="panel.presentation" />
      </div>
    </div>

    <!-- Bottom: actions row -->
    <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-gray-100">
      <button
        type="button"
        class="px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
        @click="openControl"
      >
        {{ t('tv.actions.control') }}
      </button>

      <button
        v-if="panel.match"
        type="button"
        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        @click="openReport"
      >
        {{ t('tv.actions.report') }}
      </button>

      <button
        type="button"
        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        @click="generateUrl"
      >
        {{ t('tv.actions.url') }}
      </button>

      <!-- URL display -->
      <input
        v-if="panel.generatedUrl"
        type="text"
        :value="panel.generatedUrl"
        readonly
        class="flex-1 px-3 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-600 min-w-[200px]"
        @click="($event.target as HTMLInputElement).select()"
      >

      <div class="flex-1" />

      <button
        type="button"
        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
        :disabled="!panel.channel || activating"
        @click="blank"
      >
        {{ t('tv.actions.blank') }}
      </button>

      <button
        type="button"
        class="px-4 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
        :disabled="!panel.channel || activating"
        @click="activate"
      >
        {{ activating ? '...' : t('tv.actions.activate') }}
      </button>
    </div>
  </div>
</template>
