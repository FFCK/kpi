<script setup lang="ts">
import type { TvEvent, TvMatchesResponse, TvGlobalFilters } from '~/types/tv'
import { TV_STYLES } from '~/types/tv'

const props = defineProps<{
  events: TvEvent[]
  matchData: TvMatchesResponse | null
}>()

const filters = defineModel<TvGlobalFilters>({ required: true })

const { t } = useI18n()

const STORAGE_KEY = 'tv_global_filters'

// Restore from localStorage on mount
onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      if (parsed.eventId) filters.value.eventId = parsed.eventId
      if (parsed.date) filters.value.date = parsed.date
      if (parsed.css) filters.value.css = parsed.css
      if (parsed.lang) filters.value.lang = parsed.lang
    }
    catch {}
  }
})

// Persist on change
watch(filters, (val) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(val))
}, { deep: true })
</script>

<template>
  <div class="flex flex-wrap items-end gap-4 p-4 bg-header-50 rounded-lg border border-header-200">
    <!-- Event -->
    <div class="flex flex-col gap-1">
      <label class="text-xs font-medium text-header-600">{{ t('tv.global.event') }}</label>
      <select
        v-model.number="filters.eventId"
        class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[250px]"
      >
        <option :value="null">{{ t('tv.global.select') }}</option>
        <option v-for="e in events" :key="e.id" :value="e.id">
          {{ e.libelle }} - {{ e.lieu }}
        </option>
      </select>
    </div>

    <!-- Date -->
    <div class="flex flex-col gap-1">
      <label class="text-xs font-medium text-header-600">{{ t('tv.global.date') }}</label>
      <select
        v-model="filters.date"
        class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[140px]"
      >
        <option value="">{{ t('tv.global.all_dates') }}</option>
        <option v-for="d in matchData?.dates ?? []" :key="d" :value="d">{{ d }}</option>
      </select>
    </div>

    <!-- Style -->
    <div class="flex flex-col gap-1">
      <label class="text-xs font-medium text-header-600">{{ t('tv.global.style') }}</label>
      <select
        v-model="filters.css"
        class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[180px]"
      >
        <option v-for="s in TV_STYLES" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
    </div>

    <!-- Language -->
    <div class="flex flex-col gap-1">
      <label class="text-xs font-medium text-header-600">{{ t('tv.global.language') }}</label>
      <select
        v-model="filters.lang"
        class="px-3 py-2 text-sm border border-header-300 rounded-lg bg-white min-w-[80px]"
      >
        <option value="en">EN</option>
        <option value="fr">FR</option>
      </select>
    </div>
  </div>
</template>
