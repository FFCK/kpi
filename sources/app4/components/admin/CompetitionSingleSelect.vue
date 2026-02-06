<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

const emit = defineEmits<{
  (e: 'change', code: string): void
}>()

// Available competitions from context
const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code),
  ),
)

// Handle selection change
function onSelect(code: string) {
  workContext.setPageCompetition(code)
  emit('change', code)
}

// Auto-select: when competitions change, ensure we have a valid selection
watch(
  () => workContext.competitionCodes,
  (codes) => {
    if (!codes.length) {
      if (workContext.pageCompetitionCode) {
        workContext.setPageCompetition('')
        emit('change', '')
      }
      return
    }
    // If current selection is still valid, keep it
    if (workContext.pageCompetitionCode && codes.includes(workContext.pageCompetitionCode)) {
      return
    }
    // Auto-select first
    workContext.setPageCompetition(codes[0])
    emit('change', codes[0])
  },
  { immediate: true },
)

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
      :value="workContext.pageCompetitionCode"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      @change="onSelect(($event.target as HTMLSelectElement).value)"
    >
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
