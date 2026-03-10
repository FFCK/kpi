<script setup lang="ts">
import type { TvLabel } from '~/types/tv'
import { CHANNEL_MAX, SCENARIO_COUNT, SCENARIO_SCENES } from '~/types/tv'

const props = defineProps<{
  labels: TvLabel[]
  scenarioLabels?: TvLabel[]
}>()

const modelValue = defineModel<number | null>({ required: true })

const { t } = useI18n()

function channelLabel(n: number): string {
  const found = props.labels.find(l => l.number === n)
  if (found?.label) return `${n} - ${found.label}`
  if (n <= 4) return `${n} - Pitch ${n}`
  return `${n}`
}

function scenarioLabel(scenarioNum: number): string {
  const found = props.scenarioLabels?.find(l => l.number === scenarioNum)
  if (found?.label) return found.label
  return `${t('tv.scenario.title')} ${scenarioNum}`
}
</script>

<template>
  <select
    :value="modelValue"
    class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[180px]"
    @change="modelValue = ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null"
  >
    <option :value="null">{{ t('tv.messages.select_channel') }}</option>

    <!-- Pitches 1-4 -->
    <optgroup label="Pitches">
      <option v-for="n in 4" :key="n" :value="n">{{ channelLabel(n) }}</option>
    </optgroup>

    <!-- Channels 5-40 -->
    <optgroup label="Channels">
      <option v-for="n in 36" :key="n + 4" :value="n + 4">{{ channelLabel(n + 4) }}</option>
    </optgroup>

    <!-- Test 41-50 -->
    <optgroup label="Tests">
      <option v-for="n in 10" :key="n + 40" :value="n + 40">{{ channelLabel(n + 40) }}</option>
    </optgroup>

    <!-- Extra 51-99 -->
    <optgroup label="Extra">
      <option v-for="n in 49" :key="n + 50" :value="n + 50">{{ channelLabel(n + 50) }}</option>
    </optgroup>

    <!-- Scenarios -->
    <optgroup
      v-for="s in SCENARIO_COUNT"
      :key="'s' + s"
      :label="scenarioLabel(s)"
    >
      <option v-for="scene in SCENARIO_SCENES" :key="s * 100 + scene" :value="s * 100 + scene">
        {{ s * 100 + scene }}
      </option>
    </optgroup>
  </select>
</template>
