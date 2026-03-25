<script setup lang="ts">
import type { Competition } from '~/types'

const { t } = useI18n()
const workContext = useWorkContextStore()

const props = withDefaults(defineProps<{
  showAllOption?: boolean
  allOptionLabel?: string
  filteredCodes?: string[] | null
}>(), {
  showAllOption: false,
  allOptionLabel: '',
  filteredCodes: null,
})

const emit = defineEmits<{
  (e: 'change', code: string): void
}>()

// The current competition code depends on the mode:
// - showAllOption=true uses pageCompetitionCodeAll (can be '' = all)
// - showAllOption=false uses pageCompetitionCode (always a real code)
const currentCode = computed(() => {
  return props.showAllOption ? workContext.pageCompetitionCodeAll : workContext.pageCompetitionCode
})

// Available competitions grouped by section
interface SectionGroup {
  sectionLabel: string
  competitions: Competition[]
}

const groupedCompetitions = computed(() => {
  // Use filteredCodes if provided, otherwise use all context competition codes
  const codes = props.filteredCodes
    ? new Set(props.filteredCodes.filter(c => workContext.competitionCodes.includes(c)))
    : new Set(workContext.competitionCodes)
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
  if (props.showAllOption) {
    workContext.setPageCompetitionAll(code)
  }
  else {
    workContext.setPageCompetition(code)
  }
  emit('change', code)
}

// Resolved label for the "All" option
const resolvedAllLabel = computed(() => props.allOptionLabel || t('context.all_competitions_for_selection'))

// Available codes: filteredCodes (intersected with context) or all context codes
const availableCodes = computed(() => {
  if (props.filteredCodes) {
    return props.filteredCodes.filter(c => workContext.competitionCodes.includes(c))
  }
  return workContext.competitionCodes
})

// Auto-select: when available competitions change, ensure we have a valid selection
watch(
  availableCodes,
  (codes) => {
    if (!codes.length) {
      if (currentCode.value) {
        onSelect('')
      }
      return
    }
    // If "All" option is active and showAllOption is enabled, keep it
    if (props.showAllOption && currentCode.value === '') {
      return
    }
    // If current selection is still valid, keep it
    if (currentCode.value && codes.includes(currentCode.value)) {
      return
    }
    // Auto-select first
    onSelect(codes[0])
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
    <div v-if="!hasCompetitions" class="text-sm text-header-500 italic">
      {{ t('context.no_competitions') }}
    </div>

    <select
      v-else
      :value="currentCode"
      class="w-full px-3 py-2 border rounded-lg text-header-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
      :class="showAllOption && currentCode === '' ? 'border-header-300 bg-white' : 'border-warning-400 bg-warning-50'"
      @change="onSelect(($event.target as HTMLSelectElement).value)"
    >
      <!-- "All competitions" option -->
      <option v-if="showAllOption" value="">{{ resolvedAllLabel }}</option>

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
