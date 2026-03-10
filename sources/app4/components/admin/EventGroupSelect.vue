<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()
const api = useApi()

const emit = defineEmits<{
  (e: 'change', value: string): void
}>()

// Handle selection change
function onSelectionChange(val: string) {
  workContext.setPageEventGroupSelection(val)

  // Reset "All" competition selection: pages with showAllOption will show "All",
  // pages without it will auto-select the first competition via their watcher
  workContext.setPageCompetitionAll('')

  // Load event competitions when an event is selected
  if (val.startsWith('event:')) {
    const eventId = parseInt(val.substring(6), 10)
    workContext.loadPageEventCompetitions(eventId, api)
  }
  else {
    workContext.pageEventCompetitionCodes = []
  }

  emit('change', val)
}

// Load event competitions on mount if an event was restored from localStorage
onMounted(() => {
  if (workContext.pageEventGroupType === 'event' && workContext.pageEventCompetitionCodes.length === 0) {
    const eventId = parseInt(workContext.pageEventGroupValue, 10)
    if (eventId) {
      workContext.loadPageEventCompetitions(eventId, api)
    }
  }
})

// Groups organized by section for optgroups
interface SectionWithGroups {
  sectionLabel: string
  groups: Array<{ code: string; libelle: string }>
}

const groupsBySection = computed(() => {
  const sectionMap = new Map<number, SectionWithGroups>()

  // Build section labels from workContext.groups
  const sectionLabels = new Map<number, string>()
  for (const g of workContext.groups) {
    if (!sectionLabels.has(g.section)) {
      sectionLabels.set(g.section, g.sectionLabel)
    }
  }

  // Only include groups whose competitions intersect with competitionCodes
  const contextCodes = new Set(workContext.competitionCodes)
  for (const group of workContext.uniqueGroups) {
    const hasContextCompetitions = group.competitions.some(c => contextCodes.has(c))
    if (!hasContextCompetitions) continue

    if (!sectionMap.has(group.section)) {
      sectionMap.set(group.section, {
        sectionLabel: sectionLabels.get(group.section) || t('groups.sections.' + group.section),
        groups: [],
      })
    }
    sectionMap.get(group.section)!.groups.push({
      code: group.code,
      libelle: group.libelle,
    })
  }

  return Array.from(sectionMap.values())
})

// Watch for context changes: reset if current selection is no longer valid
watch(
  () => [workContext.competitionCodes, workContext.events],
  () => {
    if (!workContext.pageEventGroupSelection) return

    if (workContext.pageEventGroupType === 'event') {
      const eventId = parseInt(workContext.pageEventGroupValue, 10)
      if (!workContext.events.some(e => e.id === eventId)) {
        onSelectionChange('')
      }
    }
    else if (workContext.pageEventGroupType === 'group') {
      const groupCode = workContext.pageEventGroupValue
      const contextCodes = new Set(workContext.competitionCodes)
      const group = workContext.uniqueGroups.find(g => g.code === groupCode)
      if (!group || !group.competitions.some(c => contextCodes.has(c))) {
        onSelectionChange('')
      }
    }
  },
  { deep: true },
)
</script>

<template>
  <select
    :value="workContext.pageEventGroupSelection"
    class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500"
    @change="onSelectionChange(($event.target as HTMLSelectElement).value)"
  >
    <option value="">{{ t('eventGroupSelect.all') }}</option>

    <!-- Events optgroup -->
    <optgroup v-if="workContext.events.length > 0" :label="t('eventGroupSelect.events')">
      <option
        v-for="evt in workContext.events"
        :key="'event:' + evt.id"
        :value="'event:' + evt.id"
      >
        {{ evt.id }} - {{ evt.libelle }}
      </option>
    </optgroup>

    <!-- Groups by section optgroups -->
    <optgroup
      v-for="section in groupsBySection"
      :key="section.sectionLabel"
      :label="section.sectionLabel"
    >
      <option
        v-for="group in section.groups"
        :key="'group:' + group.code"
        :value="'group:' + group.code"
      >
        {{ group.code }} - {{ group.libelle }}
      </option>
    </optgroup>
  </select>
</template>
