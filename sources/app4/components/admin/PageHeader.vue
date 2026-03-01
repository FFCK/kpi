<script setup lang="ts">
const { t } = useI18n()
const router = useRouter()
const workContext = useWorkContextStore()
const slots = useSlots()

interface Props {
  title: string
  showFilters?: boolean
  showAllOption?: boolean
  competitionFilteredCodes?: string[] | null
}

withDefaults(defineProps<Props>(), {
  showFilters: true,
  showAllOption: false,
  competitionFilteredCodes: null,
})

const emit = defineEmits<{
  'event-group-change': []
  'competition-change': []
}>()

// Notices dismissal state
const noticesDismissed = ref(false)
const hasNotices = computed(() => !!slots.notices && !noticesDismissed.value)

// Reset dismissed state when slot content might change (competition change)
watch(() => workContext.pageCompetitionCode, () => {
  noticesDismissed.value = false
})
watch(() => workContext.pageCompetitionCodeAll, () => {
  noticesDismissed.value = false
})
</script>

<template>
  <div class="mb-2">
    <!-- Row 1: Title + Work Context Summary -->
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
      <div class="flex items-center gap-3">
        <button
          :title="t('common.back')"
          class="inline-flex items-center p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
          @click="router.back()"
        >
          <UIcon name="heroicons:arrow-left" class="w-5 h-5" />
        </button>
        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
      </div>
      <AdminWorkContextSummary compact />
    </div>

    <!-- Row 2: Filters -->
    <div v-if="showFilters" class="flex flex-wrap gap-3 items-end">
      <!-- Event / Group filter -->
      <div class="min-w-48 max-w-96">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('eventGroupSelect.label') }}</label>
        <AdminEventGroupSelect @change="emit('event-group-change')" />
      </div>

      <!-- Competition filter -->
      <div class="min-w-48 max-w-96">
        <label class="block text-xs font-medium text-gray-500 mb-1">{{ t(workContext.competitionFilterLabelKey) }}</label>
        <AdminCompetitionSingleSelect
          :show-all-option="showAllOption"
          :filtered-codes="competitionFilteredCodes"
          @change="emit('competition-change')"
        />
      </div>

      <!-- Extra filters slot -->
      <slot name="filters" />

      <!-- Badges slot -->
      <slot name="badges" />
    </div>

    <!-- Notices (dismissable) -->
    <div v-if="slots.notices && !noticesDismissed" class="mt-2 relative">
      <slot name="notices" />
      <button
        class="absolute top-1 right-1 p-1 text-gray-400 hover:text-gray-600 rounded transition-colors"
        @click="noticesDismissed = true"
      >
        <UIcon name="heroicons:x-mark" class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>
