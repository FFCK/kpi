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
function onSelectionTypeChange(type: 'section' | 'group' | 'competition' | 'event') {
  // Clear previous selection when changing type
  workContext.clearSelection()
  workContext.selectionType = type
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

// Competition selection handler
function onCompetitionChange(code: string) {
  if (code) {
    workContext.selectCompetition(code)
  }
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
      <UIcon name="i-heroicons-cog-6-tooth" class="w-5 h-5 text-gray-500" />
      <h2 class="text-lg font-semibold text-gray-900">{{ t('context.title') }}</h2>
    </div>

    <!-- Loading state -->
    <div v-if="workContext.loading" class="flex items-center justify-center py-8">
      <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 text-gray-400 animate-spin" />
      <span class="ml-2 text-gray-500">{{ t('common.loading') }}</span>
    </div>

    <div v-else class="space-y-6">
      <!-- Season selector -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('context.season') }}
        </label>
        <select
          :value="workContext.season"
          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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

      <!-- Scope selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
          {{ t('context.scope') }}
        </label>

        <div class="space-y-4">
          <!-- Section -->
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
              <label for="type-section" class="block text-sm font-medium text-gray-700 cursor-pointer">
                {{ t('context.type_section') }}
              </label>
              <select
                v-if="workContext.selectionType === 'section'"
                :value="workContext.sectionId ?? ''"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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

          <!-- Group -->
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
              <label for="type-group" class="block text-sm font-medium text-gray-700 cursor-pointer">
                {{ t('context.type_group') }}
              </label>
              <select
                v-if="workContext.selectionType === 'group'"
                :value="workContext.groupCode ?? ''"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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

          <!-- Competition -->
          <div class="flex items-start gap-3">
            <input
              id="type-competition"
              type="radio"
              :checked="workContext.selectionType === 'competition'"
              name="selection-type"
              class="mt-1"
              @change="onSelectionTypeChange('competition')"
            >
            <div class="flex-1">
              <label for="type-competition" class="block text-sm font-medium text-gray-700 cursor-pointer">
                {{ t('context.type_competition') }}
              </label>
              <select
                v-if="workContext.selectionType === 'competition'"
                :value="workContext.competitionCode ?? ''"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                @change="onCompetitionChange(($event.target as HTMLSelectElement).value)"
              >
                <option value="" disabled>{{ t('context.select_competition') }}</option>
                <optgroup
                  v-for="group in workContext.groups"
                  :key="group.section"
                  :label="t(`context.sections.${group.section}`)"
                >
                  <option
                    v-for="comp in group.competitions"
                    :key="comp.code"
                    :value="comp.code"
                  >
                    {{ formatCompetitionLabel(comp) }}
                  </option>
                </optgroup>
              </select>
            </div>
          </div>

          <!-- Event -->
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
              <label for="type-event" class="block text-sm font-medium text-gray-700 cursor-pointer">
                {{ t('context.type_event') }}
              </label>
              <select
                v-if="workContext.selectionType === 'event'"
                :value="workContext.eventId ?? ''"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
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

      <!-- Context summary -->
      <div
        v-if="workContext.hasValidContext"
        class="bg-green-50 border border-green-200 rounded-lg p-4"
      >
        <div class="flex items-center gap-2 mb-2">
          <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-green-600" />
          <span class="font-medium text-green-800">
            {{ t('context.current') }}: {{ workContext.season }} / {{ workContext.contextLabel }}
          </span>
        </div>
        <p class="text-sm text-green-700 mb-2">
          {{ t('context.competitions_count', { count: workContext.competitionCount }) }}
        </p>
        <ul class="text-sm text-green-600 ml-4 space-y-1">
          <li
            v-for="comp in workContext.contextCompetitions"
            :key="comp.code"
            class="flex items-center gap-1"
          >
            <span class="text-green-400">&rarr;</span>
            {{ formatCompetitionLabel(comp) }}
          </li>
        </ul>
      </div>

      <!-- No context warning -->
      <div
        v-else-if="workContext.selectionType && workContext.competitionCodes.length === 0"
        class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
      >
        <div class="flex items-center gap-2">
          <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-yellow-600" />
          <span class="text-yellow-800">{{ t('context.no_competitions') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
