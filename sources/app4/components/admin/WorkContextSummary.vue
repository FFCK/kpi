<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()
</script>

<template>
  <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-2 sm:gap-4">
      <div class="flex items-center gap-2">
        <UIcon name="i-heroicons-calendar" class="w-5 h-5 text-blue-600 shrink-0" />
        <span class="text-sm text-gray-600">{{ t('context.season') }}:</span>
        <span class="font-semibold text-gray-900">{{ workContext.season || '-' }}</span>
      </div>
      <div v-if="workContext.hasValidContext" class="flex items-center gap-2">
        <UIcon name="i-heroicons-funnel" class="w-5 h-5 text-blue-600 shrink-0" />
        <span class="text-sm text-gray-600">{{ t('context.scope') }}:</span>
        <span class="font-semibold text-gray-900">{{ workContext.contextLabel }}</span>
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
