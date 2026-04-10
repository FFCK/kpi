<script setup lang="ts">
import type { TvLabel, ScenarioScene } from '~/types/tv'
import { SCENARIO_COUNT } from '~/types/tv'

const props = defineProps<{
  scenarioLabels: TvLabel[]
}>()

const { t } = useI18n()
const api = useApi()
const toast = useToast()

const selectedScenario = ref(1)
const scenes = ref<ScenarioScene[]>([])
const loading = ref(false)
const saving = ref(false)

function scenarioLabel(n: number): string {
  const found = props.scenarioLabels.find(l => l.number === n)
  return found?.label ? `${t('tv.scenario.title')} ${n} - ${found.label}` : `${t('tv.scenario.title')} ${n}`
}

async function loadScenario() {
  loading.value = true
  try {
    const data = await api.get<{ scenario: number; scenes: ScenarioScene[] }>(
      `/admin/tv/scenario/${selectedScenario.value}`
    )
    scenes.value = data.scenes
  }
  catch {}
  finally { loading.value = false }
}

async function updateScenario() {
  saving.value = true
  try {
    await api.put(`/admin/tv/scenario/${selectedScenario.value}`, { scenes: scenes.value })
    toast.add({
      title: t('tv.scenario.updated', { scenario: selectedScenario.value }),
      color: 'success',
      duration: 3000,
    })
  }
  catch {}
  finally { saving.value = false }
}

async function testScenario() {
  // Activate the first scene of the scenario to trigger the rotation display
  const firstScene = scenes.value[0]
  if (!firstScene) return

  try {
    await api.post('/admin/tv/activate', {
      voie: selectedScenario.value * 100,
      url: firstScene.url || 'live/tv2.php?show=empty',
    })
    toast.add({
      title: `${t('tv.scenario.title')} ${selectedScenario.value} activated`,
      color: 'success',
      duration: 3000,
    })
  }
  catch {}
}

watch(selectedScenario, () => loadScenario())
onMounted(() => loadScenario())
</script>

<template>
  <div>
    <!-- Scenario selector + refresh -->
    <div class="flex items-end gap-3 mb-4">
      <div class="flex flex-col gap-1">
        <label class="text-xs font-medium text-header-600">{{ t('tv.scenario.title') }}</label>
        <select
          v-model.number="selectedScenario"
          class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[250px]"
        >
          <option v-for="n in SCENARIO_COUNT" :key="n" :value="n">
            {{ scenarioLabel(n) }}
          </option>
        </select>
      </div>

      <button
        type="button"
        class="px-3 py-2 text-sm font-medium text-header-700 bg-header-100 rounded-lg hover:bg-header-200 transition-colors"
        :disabled="loading"
        @click="loadScenario"
      >
        {{ t('tv.scenario.refresh') }}
      </button>
    </div>

    <!-- Scenes table -->
    <div class="bg-white rounded-lg shadow border border-header-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-header-50 border-b border-header-200">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-header-600 w-24">{{ t('tv.scenario.channel') }}</th>
            <th class="px-4 py-2 text-left font-medium text-header-600">{{ t('tv.scenario.url') }}</th>
            <th class="px-4 py-2 text-left font-medium text-header-600 w-32">{{ t('tv.scenario.delay') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="scene in scenes"
            :key="scene.voie"
            class="border-b border-header-100 last:border-0"
          >
            <td class="px-4 py-2 text-header-500 font-mono">{{ scene.voie }}</td>
            <td class="px-4 py-2">
              <input
                v-model="scene.url"
                type="text"
                class="w-full px-2 py-1 text-sm border border-header-300 rounded bg-white"
              >
            </td>
            <td class="px-4 py-2">
              <input
                v-model.number="scene.intervalle"
                type="number"
                min="1000"
                step="1000"
                class="w-full px-2 py-1 text-sm border border-header-300 rounded bg-white"
              >
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 mt-4">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50"
        :disabled="saving"
        @click="updateScenario"
      >
        {{ saving ? '...' : t('tv.scenario.update') }}
      </button>

      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-header-700 bg-header-100 rounded-lg hover:bg-header-200 transition-colors"
        @click="testScenario"
      >
        {{ t('tv.scenario.test') }}
      </button>
    </div>
  </div>
</template>
