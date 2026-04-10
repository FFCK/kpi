<script setup lang="ts">
const props = defineProps<{
  phpPage: string
  title: string
}>()

const { t } = useI18n()
const { getLegacyUrl } = useLegacyRedirect()

const legacyUrl = computed(() => getLegacyUrl(props.phpPage))

// Redirect after a short delay
onMounted(() => {
  setTimeout(() => {
    window.location.href = legacyUrl.value
  }, 1500)
})
</script>

<template>
  <div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center max-w-md mx-auto p-6">
      <div class="mb-6">
        <UIcon name="heroicons:arrow-path" class="w-16 h-16 text-primary-500 animate-spin mx-auto" />
      </div>

      <h1 class="text-2xl font-bold text-header-900 mb-2">
        {{ title }}
      </h1>

      <p class="text-header-600 mb-6">
        {{ t('legacy_redirect.message') }}
      </p>

      <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
          <UIcon name="heroicons:information-circle" class="w-5 h-5 text-warning-600 mt-0.5 shrink-0" />
          <p class="text-sm text-warning-800">
            {{ t('legacy_redirect.info') }}
          </p>
        </div>
      </div>

      <a
        :href="legacyUrl"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
      >
        <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4" />
        {{ t('legacy_redirect.go_now') }}
      </a>
    </div>
  </div>
</template>
