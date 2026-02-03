<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

// Available competitions from context
const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code),
  ),
)

// Selected code with v-model binding
const selectedCode = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

// Format competition label
function formatCompetitionLabel(comp: { code: string; libelle: string; soustitre?: string | null }): string {
  return comp.soustitre ? `${comp.code} - ${comp.libelle} (${comp.soustitre})` : `${comp.code} - ${comp.libelle}`
}
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ t('context.competition_from_context') }}
    </label>

    <div v-if="availableCompetitions.length === 0" class="text-sm text-gray-500 italic">
      {{ t('context.no_competitions') }}
    </div>

    <select
      v-else
      v-model="selectedCode"
      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    >
      <option value="" disabled>{{ t('context.select_competition') }}</option>
      <option
        v-for="comp in availableCompetitions"
        :key="comp.code"
        :value="comp.code"
      >
        {{ formatCompetitionLabel(comp) }}
      </option>
    </select>
  </div>
</template>
