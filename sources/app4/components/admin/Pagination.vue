<script setup lang="ts">
const { t } = useI18n()

interface Props {
  page: number
  totalPages: number
  total: number
  limit: number
  showingText?: string
  itemsPerPageText?: string
  limitOptions?: number[]
  showAll?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showingText: 'Affichage de {from} à {to} sur {total}',
  itemsPerPageText: 'Éléments par page',
  limitOptions: () => [10, 20, 50, 100],
  showAll: false,
})

const emit = defineEmits<{
  (e: 'update:page', value: number): void
  (e: 'update:limit', value: number): void
}>()

const isShowAll = computed(() => props.limit === 0)
const paginationFrom = computed(() => isShowAll.value ? 1 : ((props.page - 1) * props.limit) + 1)
const paginationTo = computed(() => isShowAll.value ? props.total : Math.min(props.page * props.limit, props.total))

const showingTextFormatted = computed(() => {
  return props.showingText
    .replace('{from}', String(paginationFrom.value))
    .replace('{to}', String(paginationTo.value))
    .replace('{total}', String(props.total))
})

const localLimit = computed({
  get: () => props.limit,
  set: (value: number) => emit('update:limit', value)
})
</script>

<template>
  <div class="px-4 py-3 border-t border-header-200 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
    <div class="text-sm text-header-500">
      <span v-if="total > 0">
        {{ showingTextFormatted }}
      </span>
    </div>

    <div class="flex items-center gap-4">
      <!-- Items per page -->
      <div class="flex items-center gap-2">
        <span class="text-sm text-header-600">{{ itemsPerPageText }}</span>
        <select
          v-model.number="localLimit"
          class="px-3 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
        >
          <option v-for="opt in limitOptions" :key="opt" :value="opt">{{ opt }}</option>
          <option v-if="showAll" :value="0">{{ t('common.all') }}</option>
        </select>
      </div>

      <!-- Page navigation -->
      <div v-if="!isShowAll" class="flex items-center gap-2">
        <button
          type="button"
          class="p-2 text-header-600 hover:bg-header-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page <= 1"
          @click="emit('update:page', page - 1)"
        >
          <UIcon name="heroicons:chevron-left" class="w-5 h-5" />
        </button>
        <span class="text-sm text-header-700 px-2 min-w-[4rem] text-center">
          {{ page }} / {{ totalPages || 1 }}
        </span>
        <button
          type="button"
          class="p-2 text-header-600 hover:bg-header-100 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page >= totalPages"
          @click="emit('update:page', page + 1)"
        >
          <UIcon name="heroicons:chevron-right" class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>
