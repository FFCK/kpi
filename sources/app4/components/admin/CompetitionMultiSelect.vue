<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

const props = defineProps<{
  modelValue: string[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void
}>()

// Available competitions from context
const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code),
  ),
)

// Selected codes with v-model binding
const selectedCodes = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

// Check if all are selected
const allSelected = computed(() =>
  selectedCodes.value.length === availableCompetitions.value.length
  && availableCompetitions.value.length > 0,
)

// Check if some are selected
const someSelected = computed(() =>
  selectedCodes.value.length > 0,
)

// Toggle all
function toggleAll() {
  if (allSelected.value) {
    selectedCodes.value = []
  }
  else {
    selectedCodes.value = availableCompetitions.value.map(c => c.code)
  }
}

// Toggle single competition
function toggleCompetition(code: string) {
  if (selectedCodes.value.includes(code)) {
    selectedCodes.value = selectedCodes.value.filter(c => c !== code)
  }
  else {
    selectedCodes.value = [...selectedCodes.value, code]
  }
}

// Format competition label
function formatCompetitionLabel(comp: { code: string; libelle: string; soustitre?: string | null }): string {
  return comp.soustitre ? `${comp.code} - ${comp.libelle} (${comp.soustitre})` : `${comp.code} - ${comp.libelle}`
}
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-header-700 mb-1">
      {{ t('context.competitions_from_context') }}
    </label>

    <div v-if="availableCompetitions.length === 0" class="text-sm text-header-500 italic">
      {{ t('context.no_competitions') }}
    </div>

    <div v-else class="border border-header-300 rounded-md overflow-hidden">
      <!-- "All" option -->
      <label
        class="flex items-center gap-2 p-2 hover:bg-header-50 cursor-pointer border-b border-header-200"
      >
        <input
          type="checkbox"
          :checked="allSelected"
          :indeterminate="someSelected && !allSelected"
          class="rounded border-header-300 text-primary-600 focus:ring-primary-500"
          @change="toggleAll"
        >
        <span class="font-medium text-header-900">
          {{ t('context.all_competitions') }} ({{ availableCompetitions.length }})
        </span>
      </label>

      <!-- Individual competitions -->
      <div class="max-h-48 overflow-y-auto">
        <label
          v-for="comp in availableCompetitions"
          :key="comp.code"
          class="flex items-center gap-2 p-2 hover:bg-header-50 cursor-pointer"
        >
          <input
            type="checkbox"
            :checked="selectedCodes.includes(comp.code)"
            class="rounded border-header-300 text-primary-600 focus:ring-primary-500"
            @change="toggleCompetition(comp.code)"
          >
          <span class="text-sm text-header-700">{{ formatCompetitionLabel(comp) }}</span>
        </label>
      </div>
    </div>

    <!-- Selection count -->
    <p v-if="someSelected" class="mt-1 text-xs text-header-500">
      {{ t('context.competitions_count', { count: selectedCodes.length }) }}
    </p>
  </div>
</template>
