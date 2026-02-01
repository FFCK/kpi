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
        <UIcon name="heroicons:arrow-path" class="w-16 h-16 text-blue-500 animate-spin mx-auto" />
      </div>

      <h1 class="text-2xl font-bold text-gray-900 mb-2">
        {{ title }}
      </h1>

      <p class="text-gray-600 mb-6">
        {{ t('legacy_redirect.message') }}
      </p>

      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
          <UIcon name="heroicons:information-circle" class="w-5 h-5 text-yellow-600 mt-0.5 shrink-0" />
          <p class="text-sm text-yellow-800">
            {{ t('legacy_redirect.info') }}
          </p>
        </div>
      </div>

      <a
        :href="legacyUrl"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <UIcon name="heroicons:arrow-top-right-on-square" class="w-4 h-4" />
        {{ t('legacy_redirect.go_now') }}
      </a>
    </div>
  </div>
</template>
