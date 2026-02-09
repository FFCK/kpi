<script setup lang="ts">
import type { Competition } from '~/types'

const { t } = useI18n()
const workContext = useWorkContextStore()

const emit = defineEmits<{
  (e: 'change', code: string): void
}>()

// Available competitions grouped by section
interface SectionGroup {
  sectionLabel: string
  competitions: Competition[]
}

const groupedCompetitions = computed(() => {
  const codes = new Set(workContext.competitionCodes)
  const sections: SectionGroup[] = []

  for (const group of workContext.groups) {
    const filtered = group.competitions.filter(c => codes.has(c.code))
    if (filtered.length > 0) {
      // Merge into existing section if already present
      const existing = sections.find(s => s.sectionLabel === group.sectionLabel)
      if (existing) {
        existing.competitions.push(...filtered)
      }
      else {
        sections.push({ sectionLabel: group.sectionLabel, competitions: [...filtered] })
      }
    }
  }

  return sections
})

const hasCompetitions = computed(() => groupedCompetitions.value.some(s => s.competitions.length > 0))
const isSingleSection = computed(() => groupedCompetitions.value.length === 1)

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

    <div v-if="!hasCompetitions" class="text-sm text-gray-500 italic">
      {{ t('context.no_competitions') }}
    </div>

    <select
      v-else
      :value="workContext.pageCompetitionCode"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      @change="onSelect(($event.target as HTMLSelectElement).value)"
    >
      <!-- Single section: no optgroup needed -->
      <template v-if="isSingleSection">
        <option
          v-for="comp in groupedCompetitions[0]!.competitions"
          :key="comp.code"
          :value="comp.code"
        >
          {{ formatCompetitionLabel(comp) }}
        </option>
      </template>

      <!-- Multiple sections: group with optgroup -->
      <template v-else>
        <optgroup
          v-for="section in groupedCompetitions"
          :key="section.sectionLabel"
          :label="section.sectionLabel"
        >
          <option
            v-for="comp in section.competitions"
            :key="comp.code"
            :value="comp.code"
          >
            {{ formatCompetitionLabel(comp) }}
          </option>
        </optgroup>
      </template>
    </select>
  </div>
</template>
