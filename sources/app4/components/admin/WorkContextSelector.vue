<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

// Create API instance at setup time (required for Nuxt composables)
const api = useApi()

// Initialize context on mount
onMounted(() => {
  workContext.initContext()
})

// Season change handler
async function onSeasonChange(seasonCode: string) {
  await workContext.setSeason(seasonCode, api)
}

// Selection type handler
function onSelectionTypeChange(type: 'all' | 'selection' | 'section' | 'group' | 'event') {
  if (type === workContext.selectionType) return

  switch (type) {
    case 'all':
      workContext.selectAll()
      break
    case 'selection':
      workContext.selectCompetitions([])
      break
    default:
      workContext.clearSelection()
      workContext.selectionType = type
      workContext.saveToStorage()
      break
  }
}

// Section selection handler
function onSectionChange(sectionId: string) {
  if (sectionId) {
    workContext.selectSection(parseInt(sectionId, 10))
  }
}

// Group selection handler
function onGroupChange(groupCode: string) {
  if (groupCode) {
    workContext.selectGroup(groupCode)
  }
}

// Multi-competition toggle handler
function onCompetitionToggle(code: string) {
  const current = [...workContext.selectedCompetitionCodes]
  const idx = current.indexOf(code)
  if (idx >= 0) {
    current.splice(idx, 1)
  }
  else {
    current.push(code)
  }
  workContext.selectCompetitions(current)
}

// Event selection handler
async function onEventChange(eventId: string) {
  if (eventId) {
    await workContext.selectEvent(parseInt(eventId, 10), api)
  }
}

// Format competition label
function formatCompetitionLabel(comp: { code: string; libelle: string; soustitre?: string | null }): string {
  return comp.soustitre ? `${comp.code} - ${comp.libelle} (${comp.soustitre})` : `${comp.code} - ${comp.libelle}`
}

// Format event label
function formatEventLabel(event: { id: number; libelle: string; dateDebut: string | null }): string {
  if (event.dateDebut) {
    const date = new Date(event.dateDebut)
    const year = date.getFullYear()
    return `${event.libelle} (${year})`
  }
  return event.libelle
}
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center gap-2 mb-4">
      <UIcon name="i-heroicons-cog-6-tooth" class="w-5 h-5 text-header-500" />
      <h2 class="text-lg font-semibold text-header-900">{{ t('context.title') }}</h2>
    </div>

    <!-- Loading state -->
    <div v-if="workContext.loading" class="flex items-center justify-center py-8">
      <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 text-header-500 animate-spin" />
      <span class="ml-2 text-header-500">{{ t('common.loading') }}</span>
    </div>

    <div v-else>
      <!-- 2-column layout on desktop: Season+Scope left, Competition count right -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- LEFT COLUMN: Season + Scope -->
        <div class="space-y-6">
          <!-- Season -->
          <div>
            <label class="block text-sm font-medium text-header-500 mb-1">
              {{ t('context.season') }}
            </label>
            <select
              :value="workContext.season"
              class="w-full rounded-md border-header-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              @change="onSeasonChange(($event.target as HTMLSelectElement).value)"
            >
              <option value="" disabled>{{ t('context.select_season') }}</option>
              <option
                v-for="season in workContext.seasons"
                :key="season.code"
                :value="season.code"
              >
                {{ season.code }}{{ season.active ? ` (${t('context.active_season')})` : '' }}
              </option>
            </select>
          </div>

          <!-- Scope -->
          <div>
            <label class="block text-sm font-medium text-header-500 mb-3">
              {{ t('context.scope') }}
            </label>

            <div class="space-y-3">
              <!-- 1. All competitions -->
              <div class="flex items-start gap-3">
                <input
                  id="type-all"
                  type="radio"
                  :checked="workContext.selectionType === 'all'"
                  name="selection-type"
                  class="mt-1"
                  @change="onSelectionTypeChange('all')"
                >
                <label for="type-all" class="block text-sm font-medium text-header-500 cursor-pointer">
                  {{ t('context.type_all') }}
                </label>
              </div>

              <!-- 2. Selection (multi-select competitions) -->
              <div class="flex items-start gap-3">
                <input
                  id="type-selection"
                  type="radio"
                  :checked="workContext.selectionType === 'selection'"
                  name="selection-type"
                  class="mt-1"
                  @change="onSelectionTypeChange('selection')"
                >
                <div class="flex-1">
                  <label for="type-selection" class="block text-sm font-medium text-header-500 cursor-pointer">
                    {{ t('context.type_selection') }}
                  </label>
                  <div
                    v-if="workContext.selectionType === 'selection'"
                    class="mt-1 max-h-48 overflow-y-auto border border-header-200 rounded-md p-2 space-y-1"
                  >
                    <template v-for="group in workContext.groups" :key="group.section">
                      <div class="text-xs font-semibold text-header-500 mt-1 first:mt-0 px-1">
                        {{ t(`groups.sections.${group.section}`) }}
                      </div>
                      <label
                        v-for="comp in group.competitions"
                        :key="comp.code"
                        class="flex items-center gap-2 px-1 py-0.5 rounded hover:bg-header-50 cursor-pointer text-sm"
                      >
                        <input
                          type="checkbox"
                          :checked="workContext.selectedCompetitionCodes.includes(comp.code)"
                          class="rounded border-header-300 text-primary-600 focus:ring-primary-500"
                          @change="onCompetitionToggle(comp.code)"
                        >
                        <span class="truncate">{{ formatCompetitionLabel(comp) }}</span>
                      </label>
                    </template>
                  </div>
                  <p v-if="workContext.selectionType === 'selection' && workContext.selectedCompetitionCodes.length > 0" class="text-xs text-header-500 mt-1">
                    {{ t('context.competitions_count', { count: workContext.selectedCompetitionCodes.length }) }}
                  </p>
                </div>
              </div>

              <!-- 3. Section -->
              <div class="flex items-start gap-3">
                <input
                  id="type-section"
                  type="radio"
                  :checked="workContext.selectionType === 'section'"
                  name="selection-type"
                  class="mt-1"
                  @change="onSelectionTypeChange('section')"
                >
                <div class="flex-1">
                  <label for="type-section" class="block text-sm font-medium text-header-500 cursor-pointer">
                    {{ t('context.type_section') }}
                  </label>
                  <select
                    v-if="workContext.selectionType === 'section'"
                    :value="workContext.sectionId ?? ''"
                    class="mt-1 w-full rounded-md border-header-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                    @change="onSectionChange(($event.target as HTMLSelectElement).value)"
                  >
                    <option value="" disabled>{{ t('context.select_section') }}</option>
                    <option
                      v-for="section in workContext.availableSections"
                      :key="section.id"
                      :value="section.id"
                    >
                      {{ t(section.labelKey) }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- 4. Group -->
              <div class="flex items-start gap-3">
                <input
                  id="type-group"
                  type="radio"
                  :checked="workContext.selectionType === 'group'"
                  name="selection-type"
                  class="mt-1"
                  @change="onSelectionTypeChange('group')"
                >
                <div class="flex-1">
                  <label for="type-group" class="block text-sm font-medium text-header-500 cursor-pointer">
                    {{ t('context.type_group') }}
                  </label>
                  <select
                    v-if="workContext.selectionType === 'group'"
                    :value="workContext.groupCode ?? ''"
                    class="mt-1 w-full rounded-md border-header-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                    @change="onGroupChange(($event.target as HTMLSelectElement).value)"
                  >
                    <option value="" disabled>{{ t('context.select_group') }}</option>
                    <optgroup
                      v-for="section in workContext.availableSections"
                      :key="section.id"
                      :label="t(section.labelKey)"
                    >
                      <option
                        v-for="group in workContext.groupsBySection(section.id)"
                        :key="group.code"
                        :value="group.code"
                      >
                        {{ group.code }} - {{ group.libelle }} ({{ group.competitions.length }})
                      </option>
                    </optgroup>
                  </select>
                </div>
              </div>

              <!-- 5. Event -->
              <div class="flex items-start gap-3">
                <input
                  id="type-event"
                  type="radio"
                  :checked="workContext.selectionType === 'event'"
                  name="selection-type"
                  class="mt-1"
                  @change="onSelectionTypeChange('event')"
                >
                <div class="flex-1">
                  <label for="type-event" class="block text-sm font-medium text-header-500 cursor-pointer">
                    {{ t('context.type_event') }}
                  </label>
                  <select
                    v-if="workContext.selectionType === 'event'"
                    :value="workContext.eventId ?? ''"
                    class="mt-1 w-full rounded-md border-header-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                    @change="onEventChange(($event.target as HTMLSelectElement).value)"
                  >
                    <option value="" disabled>{{ t('context.select_event') }}</option>
                    <option
                      v-for="event in workContext.events"
                      :key="event.id"
                      :value="event.id"
                    >
                      {{ formatEventLabel(event) }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Competition count summary -->
        <div>
          <!-- Context summary -->
          <div
            v-if="workContext.hasValidContext"
            class="bg-primary-50 border border-primary-200 rounded-lg p-4"
          >
            <div class="flex items-center gap-2 mb-2">
              <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-primary-600" />
              <span class="font-medium text-primary-800">
                {{ t('context.competitions_count', { count: workContext.competitionCount }) }}
              </span>
            </div>
            <ul v-if="workContext.selectionType !== 'all' && workContext.competitionCount <= 20" class="text-sm text-primary-600 ml-4 space-y-1">
              <li
                v-for="comp in workContext.contextCompetitions"
                :key="comp.code"
                class="flex items-center gap-1"
              >
                <span class="text-primary-400">&rarr;</span>
                {{ formatCompetitionLabel(comp) }}
              </li>
            </ul>
          </div>

          <!-- No context warning -->
          <div
            v-else-if="workContext.selectionType && workContext.selectionType !== 'all' && workContext.competitionCodes.length === 0"
            class="bg-warning-50 border border-warning-200 rounded-lg p-4"
          >
            <div class="flex items-center gap-2">
              <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-warning-600" />
              <span class="text-warning-800">{{ t('context.no_competitions') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
