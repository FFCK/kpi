<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

// Initialize context on mount if not already
onMounted(() => {
  if (!workContext.initialized) {
    workContext.initContext()
  }
})
</script>

<template>
  <div v-if="workContext.hasValidContext" class="flex items-center gap-2 text-sm text-header-600 mb-4">
    <UIcon name="i-heroicons-calendar" class="w-4 h-4" />
    <span>{{ t('context.season') }}: <strong>{{ workContext.season }}</strong></span>
    <span class="text-header-400">|</span>
    <span>{{ workContext.contextLabel }}</span>
    <span class="text-header-400">({{ t('context.competitions_count', { count: workContext.competitionCount }) }})</span>
    <NuxtLink to="/" class="text-primary-600 hover:underline text-xs ml-2">
      {{ t('context.change') }}
    </NuxtLink>
  </div>
  <div v-else class="flex items-center gap-2 text-sm text-warning-600 mb-4 bg-warning-50 p-2 rounded">
    <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4" />
    <span>{{ t('context.no_context') }}</span>
    <NuxtLink to="/" class="text-primary-600 hover:underline text-xs ml-2">
      {{ t('context.change') }}
    </NuxtLink>
  </div>
</template>
