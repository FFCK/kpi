<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

const scopeLabel = computed(() => {
  switch (workContext.selectionType) {
    case 'all':
      return t('context.type_all')
    case 'selection':
      return `${t('context.type_selection')} (${workContext.selectedCompetitionCodes.length})`
    case 'section': {
      const section = workContext.sections.find(s => s.id === workContext.sectionId)
      const sectionName = section ? t(section.labelKey) : String(workContext.sectionId)
      return `${t('context.type_section')} ${sectionName}`
    }
    case 'group':
      return `${t('context.type_group')} ${workContext.groupCode}`
    case 'event': {
      const event = workContext.events.find(e => e.id === workContext.eventId)
      return event?.libelle || t('context.type_event')
    }
    default:
      return ''
  }
})
</script>

<template>
  <div class="mb-4 px-4 py-1 bg-blue-50 border border-blue-200 rounded-lg w-fit">
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-start gap-2 sm:gap-8">
      <div class="flex items-center gap-2">
        <UIcon name="i-heroicons-calendar" class="w-5 h-5 text-blue-600 shrink-0" />
        <span class="text-sm text-gray-600">{{ t('context.season') }}:</span>
        <span class="font-semibold text-gray-900">{{ workContext.season || '-' }}</span>
      </div>
      <div v-if="workContext.hasValidContext" class="flex items-center gap-2">
        <UIcon name="i-heroicons-funnel" class="w-5 h-5 text-blue-600 shrink-0" />
        <span class="text-sm text-gray-600">{{ t('context.scope') }}:</span>
        <span class="font-semibold text-gray-900">{{ scopeLabel }}</span>
        <span class="text-sm text-gray-500">({{ t('context.competitions_count', { count: workContext.competitionCount }) }})</span>
      </div>
      <div v-else class="flex items-center gap-2 text-sm text-amber-600">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
        {{ t('context.no_context') }}
      </div>
      <NuxtLink
        to="/"
        class="inline-flex items-center gap-1 self-start px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors"
      >
        <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
        {{ t('context.change') }}
      </NuxtLink>
    </div>
  </div>
</template>
