<script setup lang="ts">
import type { Event, PaginatedResponse } from '~/types'
import type { Gameday } from '~/types/gamedays'

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()

const workContext = useWorkContextStore()
const toast = useToast()
const route = useRoute()

const eventId = computed(() => parseInt(route.params.id as string, 10))

// ─── Event header ───
const event = ref<Event | null>(null)
const eventLoading = ref(false)
const eventNotFound = ref(false)

// ─── Associated IDs ───
const associatedIds = ref<Set<number>>(new Set())
const associationsLoading = ref(false)

// ─── Candidates ───
const candidates = ref<Gameday[]>([])
const total = ref(0)
const totalPages = ref(0)
const loading = ref(false)

// ─── Filters ───
const filterSeason = ref(workContext.season || '')
const filterCompetitions = ref<string[]>([])
const filterState = ref<'all' | 'linked' | 'unlinked'>('all')
const search = ref('')
const page = ref(1)
const limit = ref(25)

// ─── Debounced search ───
let searchTimeout: ReturnType<typeof setTimeout> | null = null
const debouncedSearch = ref('')
watch(search, (val) => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    debouncedSearch.value = val
    page.value = 1
  }, 300)
})

// ─── Computed ───
const associatedCount = computed(() => associatedIds.value.size)

// Filtre "Non associées" appliqué côté client (exclut les lignes associées de la page courante)
// Filtres "Toutes" et "Associées" sont gérés côté serveur via le param `event=`
const filteredCandidates = computed(() => {
  if (filterState.value === 'unlinked') return candidates.value.filter(g => !associatedIds.value.has(g.id))
  return candidates.value
})

const stateOptions = computed(() => [
  { value: 'all', label: t('events.association.filter_state_all') },
  { value: 'linked', label: t('events.association.filter_state_linked') },
  { value: 'unlinked', label: t('events.association.filter_state_unlinked') },
])

// ─── Load event ───
const loadEvent = async () => {
  eventLoading.value = true
  eventNotFound.value = false
  try {
    event.value = await api.get<Event>(`/admin/events/${eventId.value}`)
  } catch {
    eventNotFound.value = true
  } finally {
    eventLoading.value = false
  }
}

// ─── Load associated IDs ───
const loadAssociatedIds = async () => {
  if (!filterSeason.value) return
  associationsLoading.value = true
  try {
    const params: Record<string, string | number> = {
      event: eventId.value,
      season: filterSeason.value,
      limit: 9999,
    }
    const response = await api.get<PaginatedResponse<Gameday>>('/admin/gamedays', params)
    associatedIds.value = new Set(response.items.map((g: Gameday) => g.id))
  } catch {
    // silently fail
  } finally {
    associationsLoading.value = false
  }
}

// ─── Load candidates ───
const loadCandidates = async () => {
  if (!filterSeason.value) return
  loading.value = true
  try {
    const params: Record<string, string | number> = {
      season: filterSeason.value,
      page: page.value,
      limit: limit.value,
    }
    // "Associées" : on passe event= pour que l'API fasse l'INNER JOIN et ne retourne que les associées
    if (filterState.value === 'linked') {
      params.event = eventId.value
    }
    if (filterCompetitions.value.length > 0) {
      params.competitions = filterCompetitions.value.join(',')
    }
    if (debouncedSearch.value.trim()) {
      params.search = debouncedSearch.value.trim()
    }
    const response = await api.get<PaginatedResponse<Gameday>>('/admin/gamedays', params)
    candidates.value = response.items
    total.value = response.total
    totalPages.value = response.totalPages
  } catch {
    // silently fail
  } finally {
    loading.value = false
  }
}

// ─── Watchers ───
watch([filterCompetitions, debouncedSearch, limit, filterState], () => {
  page.value = 1
  loadCandidates()
})
watch(page, loadCandidates)
watch(filterSeason, () => {
  page.value = 1
  filterCompetitions.value = []
  Promise.all([loadAssociatedIds(), loadCandidates()])
})

// ─── Toggle association ───
const toggling = ref<Set<number>>(new Set())

const toggleAssociation = async (g: Gameday) => {
  if (toggling.value.has(g.id)) return
  toggling.value.add(g.id)
  try {
    if (associatedIds.value.has(g.id)) {
      await api.del(`/admin/gamedays/${g.id}/event/${eventId.value}`)
      associatedIds.value.delete(g.id)
      // Trigger reactivity
      associatedIds.value = new Set(associatedIds.value)
      toast.add({ title: t('common.success'), description: t('events.association.unlinked'), color: 'success' })
    } else {
      await api.put(`/admin/gamedays/${g.id}/event/${eventId.value}`)
      associatedIds.value.add(g.id)
      associatedIds.value = new Set(associatedIds.value)
      toast.add({ title: t('common.success'), description: t('events.association.linked'), color: 'success' })
    }
  } catch {
    // Error shown by api composable
  } finally {
    toggling.value.delete(g.id)
  }
}

// ─── Format date ───
const formatDate = (date: string | null): string => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: '2-digit' })
}

// ─── Mount ───
onMounted(async () => {
  if (!filterSeason.value && workContext.seasons.length > 0) {
    filterSeason.value = workContext.seasons[0]?.code || ''
  }
  await Promise.all([loadEvent(), loadAssociatedIds(), loadCandidates()])
})
</script>

<template>
  <div class="space-y-4">
    <!-- Back button + header -->
    <div>
      <button
        class="flex items-center gap-1 text-sm text-primary-600 hover:text-primary-800 mb-3"
        @click="navigateTo('/events')"
      >
        <UIcon name="heroicons:arrow-left" class="w-4 h-4" />
        {{ t('events.association.back_to_events') }}
      </button>

      <div v-if="eventLoading" class="h-16 flex items-center">
        <UIcon name="heroicons:arrow-path" class="w-5 h-5 animate-spin text-header-400" />
      </div>
      <div v-else-if="eventNotFound" class="text-danger-600 text-sm">
        {{ t('events.association.event_not_found') }}
      </div>
      <div v-else-if="event" class="bg-white rounded-lg shadow px-4 py-3">
        <h1 class="text-lg font-semibold text-header-900">
          {{ t('events.association.page_title') }} — <span class="text-primary-700">#{{ event.id }} {{ event.libelle }}</span>
        </h1>
        <div class="flex flex-wrap gap-4 mt-1 text-sm text-header-600">
          <span v-if="event.dateDebut || event.dateFin">
            <UIcon name="heroicons:calendar-days" class="w-4 h-4 inline mr-1" />
            {{ formatDate(event.dateDebut) }}
            <template v-if="event.dateFin && event.dateFin !== event.dateDebut">
              — {{ formatDate(event.dateFin) }}
            </template>
          </span>
          <span v-if="event.lieu">
            <UIcon name="heroicons:map-pin" class="w-4 h-4 inline mr-1" />
            {{ event.lieu }}
          </span>
          <span class="font-medium text-purple-700">
            <UIcon name="heroicons:link" class="w-4 h-4 inline mr-1" />
            <template v-if="associationsLoading">…</template>
            <template v-else>
              {{ t('events.association.associated_count', associatedCount) }}
            </template>
          </span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow px-4 py-3 space-y-3">
      <div class="flex flex-wrap gap-3">
        <!-- Season filter -->
        <div class="min-w-30">
          <label class="block text-xs font-medium text-header-600 mb-1">
            {{ t('events.association.filter_season') }}
          </label>
          <select
            v-model="filterSeason"
            class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="s in workContext.seasons" :key="s.code" :value="s.code">
              {{ s.code }}
            </option>
          </select>
        </div>

        <!-- State filter -->
        <div class="min-w-35">
          <label class="block text-xs font-medium text-header-600 mb-1">
            {{ t('events.association.filter_state') }}
          </label>
          <select
            v-model="filterState"
            class="w-full px-2 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="opt in stateOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>

        <!-- Search -->
        <div class="flex-1 min-w-50">
          <label class="block text-xs font-medium text-header-600 mb-1">
            &nbsp;
          </label>
          <div class="relative">
            <UIcon name="heroicons:magnifying-glass" class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-header-400" />
            <input
              v-model="search"
              type="search"
              :placeholder="t('events.association.search_placeholder')"
              class="w-full pl-8 pr-3 py-1.5 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
          </div>
        </div>
      </div>

      <!-- Competition multi-select -->
      <AdminCompetitionMultiSelect v-model="filterCompetitions" />
    </div>

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading && candidates.length === 0" class="py-12 text-center text-header-400">
        <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
      </div>
      <div v-else-if="filteredCandidates.length === 0" class="py-12 text-center text-header-400 text-sm">
        {{ t('events.association.no_candidates') }}
      </div>
      <table v-else class="w-full text-sm">
        <thead class="bg-header-50 text-header-600 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-center w-10">
              <UIcon name="heroicons:link" class="w-4 h-4" />
            </th>
            <th class="px-4 py-3 text-left">Id</th>
            <th class="px-4 py-3 text-left">{{ t('gamedays.field.competition') }}</th>
            <th class="px-4 py-3 text-left">{{ t('gamedays.field.phase') }}</th>
            <th class="px-4 py-3 text-left">{{ t('gamedays.field.date_debut') }}</th>
            <th class="px-4 py-3 text-left">{{ t('gamedays.field.lieu') }}</th>
            <th class="px-4 py-3 text-left">{{ t('gamedays.field.departement') }}</th>
            <th class="px-4 py-3 text-center">{{ t('events.association.filter_state') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-header-100">
          <tr
            v-for="g in filteredCandidates"
            :key="g.id"
            class="hover:bg-header-50 transition-colors"
            :class="{ 'opacity-60': toggling.has(g.id) }"
          >
            <td class="px-4 py-2 text-center">
              <input
                type="checkbox"
                :checked="associatedIds.has(g.id)"
                :disabled="toggling.has(g.id)"
                class="rounded border-header-300 text-purple-600 focus:ring-purple-500 cursor-pointer"
                @change="toggleAssociation(g)"
              >
            </td>
            <td class="px-4 py-2 text-header-500 font-mono text-xs">
              {{ g.id }}
            </td>
            <td class="px-4 py-2 font-medium text-header-800">
              {{ g.codeCompetition }}
              <span v-if="g.competitionLibelle" class="font-normal text-header-500 text-xs ml-1">{{ g.competitionLibelle }}</span>
            </td>
            <td class="px-4 py-2 text-header-700">
              {{ g.phase || '—' }}
            </td>
            <td class="px-4 py-2 text-header-600">
              {{ formatDate(g.dateDebut) }}
              <template v-if="g.dateFin && g.dateFin !== g.dateDebut">
                — {{ formatDate(g.dateFin) }}
              </template>
            </td>
            <td class="px-4 py-2 text-header-600">
              {{ g.lieu || '—' }}
            </td>
            <td class="px-4 py-2 text-header-500">
              {{ g.departement || '—' }}
            </td>
            <td class="px-4 py-2 text-center">
              <span
                v-if="associatedIds.has(g.id)"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"
              >
                <UIcon name="heroicons:check-circle" class="w-3.5 h-3.5" />
                {{ t('events.association.linked_badge') }}
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-header-100 text-header-500"
              >
                {{ t('events.association.unlinked_badge') }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Desktop Pagination -->
      <AdminPagination
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('gamedays.total', { count: total })"
      />
    </div>

    <!-- Mobile cards -->
    <AdminCardList
      class="md:hidden"
      :loading="loading && candidates.length === 0"
      :empty="filteredCandidates.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('events.association.no_candidates')"
    >
      <AdminCard
        v-for="g in filteredCandidates"
        :key="g.id"
        :title="`#${g.id} ${g.codeCompetition}`"
        :subtitle="g.phase || undefined"
      >
        <template #header-right>
          <span
            v-if="associatedIds.has(g.id)"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"
          >
            <UIcon name="heroicons:check-circle" class="w-3.5 h-3.5" />
            {{ t('events.association.linked_badge') }}
          </span>
          <span
            v-else
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-header-100 text-header-500"
          >
            {{ t('events.association.unlinked_badge') }}
          </span>
        </template>

        <div class="space-y-1 text-sm text-header-600">
          <div v-if="g.dateDebut">
            <UIcon name="heroicons:calendar-days" class="w-4 h-4 inline mr-1" />
            {{ formatDate(g.dateDebut) }}
            <template v-if="g.dateFin && g.dateFin !== g.dateDebut">
              — {{ formatDate(g.dateFin) }}
            </template>
          </div>
          <div v-if="g.lieu">
            <UIcon name="heroicons:map-pin" class="w-4 h-4 inline mr-1" />
            {{ g.lieu }}<template v-if="g.departement"> ({{ g.departement }})</template>
          </div>
        </div>

        <template #footer-right>
          <button
            :disabled="toggling.has(g.id)"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
            :class="associatedIds.has(g.id)
              ? 'bg-danger-50 text-danger-600 hover:bg-danger-100'
              : 'bg-purple-50 text-purple-700 hover:bg-purple-100'"
            @click="toggleAssociation(g)"
          >
            <UIcon
              :name="associatedIds.has(g.id) ? 'heroicons:link-slash' : 'heroicons:link'"
              class="w-4 h-4"
            />
            {{ associatedIds.has(g.id) ? t('events.association.action_unlink') : t('events.association.action_link') }}
          </button>
        </template>
      </AdminCard>

      <!-- Mobile Pagination -->
      <AdminPagination
        v-if="candidates.length > 0"
        v-model:page="page"
        v-model:limit="limit"
        :total="total"
        :total-pages="totalPages"
        :showing-text="t('gamedays.total', { count: total })"
        class="mt-4 rounded-lg shadow"
      />
    </AdminCardList>

    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
