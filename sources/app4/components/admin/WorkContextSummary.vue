<script setup lang="ts">
interface Props {
  compact?: boolean
}

withDefaults(defineProps<Props>(), {
  compact: false,
})

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
  <!-- Normal mode (full-width bar) -->
  <div v-if="!compact" class="mb-4 px-4 py-1 bg-primary-50 border border-primary-200 rounded-lg w-fit">
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-start gap-2 sm:gap-8">
      <div class="flex items-center gap-2">
        <UIcon name="i-heroicons-calendar" class="w-5 h-5 text-primary-600 shrink-0" />
        <span class="text-sm text-header-600">{{ t('context.season') }}:</span>
        <span class="font-semibold text-header-900">{{ workContext.season || '-' }}</span>
      </div>
      <div v-if="workContext.hasValidContext" class="flex items-center gap-2">
        <UIcon name="i-heroicons-funnel" class="w-5 h-5 text-primary-600 shrink-0" />
        <span class="text-sm text-header-600">{{ t('context.scope') }}:</span>
        <span class="font-semibold text-header-900">{{ scopeLabel }}</span>
        <span class="text-sm text-header-500">({{ t('context.competitions_count', { count: workContext.competitionCount }) }})</span>
      </div>
      <div v-else class="flex items-center gap-2 text-sm text-warning-600">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
        {{ t('context.no_context') }}
      </div>
      <NuxtLink
        to="/"
        class="inline-flex items-center gap-1 self-start px-3 py-1.5 text-sm font-medium text-primary-700 bg-primary-100 hover:bg-primary-200 rounded-lg transition-colors"
      >
        <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
        {{ t('context.change') }}
      </NuxtLink>
    </div>
  </div>

  <!-- Compact mode (inline badges for PageHeader title row) -->
  <div v-else class="flex flex-wrap items-center gap-2 bg-primary-50 border border-primary-200 rounded-md px-2.5 py-1 text-sm">
    <span class="inline-flex items-center gap-1.5">
      <UIcon name="i-heroicons-calendar" class="w-4 h-4 text-primary-600 shrink-0" />
      <span class="font-medium text-header-900">{{ workContext.season || '-' }}</span>
    </span>
    <span v-if="workContext.hasValidContext" class="inline-flex items-center gap-1.5">
      <UIcon name="i-heroicons-funnel" class="w-4 h-4 text-primary-600 shrink-0" />
      <span class="font-medium text-header-900">{{ scopeLabel }}</span>
      <span class="text-xs text-header-500">({{ workContext.competitionCount }})</span>
    </span>
    <span v-else class="inline-flex items-center gap-1 text-xs text-warning-600">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-3.5 h-3.5 shrink-0" />
      {{ t('context.no_context') }}
    </span>
    <NuxtLink
      to="/"
      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary-800 bg-primary-200 hover:bg-primary-300 rounded transition-colors"
    >
      <UIcon name="i-heroicons-pencil-square" class="w-3.5 h-3.5" />
      {{ t('context.change') }}
    </NuxtLink>
  </div>
</template>
