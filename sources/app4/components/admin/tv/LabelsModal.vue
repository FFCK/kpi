<script setup lang="ts">
import type { TvLabel } from '~/types/tv'
import { CHANNEL_MAX, SCENARIO_COUNT } from '~/types/tv'

const props = defineProps<{
  open: boolean
  channelLabels: TvLabel[]
  scenarioLabels: TvLabel[]
}>()

const emit = defineEmits<{
  close: []
  save: [payload: { channels: TvLabel[]; scenarios: TvLabel[] }]
}>()

const { t } = useI18n()

// Local editable copies
const localChannels = ref<Record<number, string>>({})
const localScenarios = ref<Record<number, string>>({})

// Sync from props when modal opens
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    const ch: Record<number, string> = {}
    for (let i = 1; i <= CHANNEL_MAX; i++) {
      const found = props.channelLabels.find(l => l.number === i)
      ch[i] = found?.label ?? (i <= 4 ? `Pitch ${i}` : '')
    }
    localChannels.value = ch

    const sc: Record<number, string> = {}
    for (let i = 1; i <= SCENARIO_COUNT; i++) {
      const found = props.scenarioLabels.find(l => l.number === i)
      sc[i] = found?.label ?? ''
    }
    localScenarios.value = sc
  }
})

function save() {
  const channels: TvLabel[] = []
  for (let i = 1; i <= CHANNEL_MAX; i++) {
    const label = localChannels.value[i]?.trim() ?? ''
    if (label) {
      channels.push({ number: i, label })
    }
  }

  const scenarios: TvLabel[] = []
  for (let i = 1; i <= SCENARIO_COUNT; i++) {
    const label = localScenarios.value[i]?.trim() ?? ''
    if (label) {
      scenarios.push({ number: i, label })
    }
  }

  emit('save', { channels, scenarios })
}
</script>

<template>
  <AdminModal :open="open" :title="t('tv.labels.title')" max-width="xl" @close="emit('close')">
    <div class="space-y-6 max-h-[60vh] overflow-y-auto">
      <!-- Channels -->
      <div>
        <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('tv.labels.channels') }}</h4>
        <div class="space-y-1">
          <div
            v-for="n in CHANNEL_MAX"
            :key="n"
            class="flex items-center gap-2"
          >
            <span class="text-xs text-gray-500 w-12 text-right">Ch. {{ n }}</span>
            <input
              v-model="localChannels[n]"
              type="text"
              maxlength="100"
              class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded bg-white"
              :placeholder="n <= 4 ? `Pitch ${n}` : ''"
            >
          </div>
        </div>
      </div>

      <!-- Scenarios -->
      <div>
        <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ t('tv.labels.scenarios') }}</h4>
        <div class="space-y-1">
          <div
            v-for="n in SCENARIO_COUNT"
            :key="n"
            class="flex items-center gap-2"
          >
            <span class="text-xs text-gray-500 w-12 text-right">Sc. {{ n }}</span>
            <input
              v-model="localScenarios[n]"
              type="text"
              maxlength="100"
              class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded bg-white"
            >
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
        @click="emit('close')"
      >
        {{ t('tv.labels.cancel') }}
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        @click="save"
      >
        {{ t('tv.labels.save') }}
      </button>
    </template>
  </AdminModal>
</template>
