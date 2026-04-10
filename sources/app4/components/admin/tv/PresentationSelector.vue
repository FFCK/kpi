<script setup lang="ts">
import { PRESENTATIONS, PRESENTATION_GROUPS } from '~/types/tv'

const modelValue = defineModel<string>({ required: true })

const { t } = useI18n()

const groupedPresentations = computed(() => {
  return PRESENTATION_GROUPS.map(group => ({
    group,
    label: t(`tv.presentations.groups.${group}`),
    items: PRESENTATIONS.filter(p => p.group === group),
  })).filter(g => g.items.length > 0)
})
</script>

<template>
  <select
    v-model="modelValue"
    class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[220px]"
  >
    <option value="">{{ t('tv.messages.select_presentation') }}</option>
    <optgroup
      v-for="g in groupedPresentations"
      :key="g.group"
      :label="g.label"
    >
      <option v-for="p in g.items" :key="p.value" :value="p.value">
        {{ p.label }}
      </option>
    </optgroup>
  </select>
</template>
