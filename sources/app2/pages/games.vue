<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #left>
        <button
          @click="showFilters = !showFilters"
          class="ml-4 px-3 py-1 border-2 rounded-md transition-colors flex items-center space-x-1 text-base hover:bg-gray-100"
          :style="hasActiveFilters ? 'background-color: #dbeafe; border: 2px solid #60a5fa; color: #1e40af;' : 'border: 2px solid #d1d5db;'"
        >
          <span>{{ t('nav.Filters') }}</span>
          <UIcon name="i-heroicons-filter" class="h-4 w-4" />
          <UIcon
            :name="showFilters ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
            class="h-4 w-4"
          />
        </button>
        <select
          v-model="fav_dates"
          @change="changeFav"
          class="ml-4 block w-auto px-3 py-2 border-2 text-base focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition-colors hover:bg-gray-100"
          :style="hasActiveDateFilter ? 'background-color: #dbeafe; border: 2px solid #60a5fa; color: #1e40af;' : 'border: 2px solid #d1d5db;'"
        >
          <option value="">{{ t('Games.AllDates') }}</option>
          <option v-for="(game_date, index) in game_dates" :key="index" :value="game_date">
            <NuxtTime :datetime="game_date" day="numeric" month="long" year="numeric" :locale="locale" />
          </option>
          <option disabled>──────</option>
          <option value="Today">{{ t('Games.Today') }}</option>
          <option value="Tomorow">{{ t('Games.Tomorow') }}</option>
          <option value="Prev">{{ t('Games.Prev') }}</option>
          <option value="Next">{{ t('Games.Next') }}</option>
        </select>
      </template>
      <template #right>
        <button v-if="visibleButton" @click="handleRefresh" class="p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
        </button>
      </template>
    </AppSecondaryNav>

    <div v-if="showFilters" class="p-4 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">{{ t('Games.Categories') }}</label>
            <button
              v-if="fav_categories.length > 0"
              @click="resetCategories"
              class="flex items-center px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md border border-red-200 hover:border-red-300 transition-colors cursor-pointer"
              :title="t('Games.Reset')"
            >
              <UIcon name="i-heroicons-x-mark" class="h-3 w-3 mr-1" />
              {{ t('Games.Reset') }}
            </button>
          </div>
          <div class="max-h-32 overflow-y-auto border rounded-md p-3 bg-white space-y-2">
            <div v-for="category in categories" :key="category" class="flex items-center">
              <UCheckbox
                :model-value="fav_categories.includes(category)"
                @update:model-value="(checked) => toggleCategory(category, checked)"
                :id="`cat-${category}`"
              />
              <label :for="`cat-${category}`" class="ml-2 text-sm text-gray-700 cursor-pointer">
                {{ category }}
              </label>
            </div>
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">{{ t('Games.Teams') }} & {{ t('Games.Refs') }}</label>
            <div class="flex items-center space-x-2">
              <div class="relative">
                <input
                  v-model="teamSearchQuery"
                  type="text"
                  :placeholder="t('Games.Search')"
                  class="text-xs px-2 py-1 border border-gray-300 rounded-md pr-6 w-32"
                />
                <button
                  v-if="teamSearchQuery"
                  @click="teamSearchQuery = ''"
                  class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <UIcon name="i-heroicons-x-mark" class="h-3 w-3" />
                </button>
              </div>
              <button
                v-if="fav_teams.length > 0"
                @click="resetTeams"
                class="flex items-center px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md border border-red-200 hover:border-red-300 transition-colors cursor-pointer"
                :title="t('Games.Reset')"
              >
                <UIcon name="i-heroicons-x-mark" class="h-3 w-3 mr-1" />
                {{ t('Games.Reset') }}
              </button>
            </div>
          </div>
          <div class="max-h-32 overflow-y-auto border rounded-md p-3 bg-white space-y-2">
            <div v-for="item in filteredTeamsAndRefs" :key="item" class="flex items-center">
              <UCheckbox
                :model-value="fav_teams.includes(item)"
                @update:model-value="(checked) => toggleTeam(item, checked)"
                :id="`team-${item}`"
              />
              <label :for="`team-${item}`" class="ml-2 text-sm text-gray-700 cursor-pointer">
                {{ item }}
              </label>
            </div>
          </div>
        </div>
        <div class="flex items-center">
          <label class="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="showRefs"
              @change="changeFav"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
          <label class="ml-2 block text-sm font-medium text-gray-700">{{ t('Games.ShowRefs') }}</label>
        </div>
        <div class="hidden md:flex items-center">
          <label class="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              v-model="showFlags"
              @change="changeFav"
              class="sr-only peer"
            />
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
          <label class="ml-2 block text-sm font-medium text-gray-700">{{ t('Games.ShowFlags') }}</label>
        </div>
      </div>
    </div>

    <GameList :games="filteredGames" :show-refs="showRefs" :show-flags="showFlags" :games-count="gamesCount" :filtered-games-count="filteredGamesCount" :key="locale" />

    <button @click="scrollToTop" class="fixed bottom-8 right-4 bg-gray-800 hover:bg-gray-700 text-white font-bold p-3 rounded-full">
      <UIcon name="i-heroicons-arrow-up" class="h-6 w-6" />
    </button>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useGames } from '~/composables/useGames'
import GameList from '~/components/GameList.vue'

const { t, locale } = useI18n()
const showFilters = ref(false)
const teamSearchQuery = ref('')
const visibleButton = ref(true)

// Page-specific SEO
useSeoMeta({
  title: 'Games - KPI Application',
  description: 'Browse all kayak polo matches, filter by categories, teams, and dates. Real-time scores and schedules.',
  ogTitle: 'Games - KPI Application',
  ogDescription: 'Browse all kayak polo matches with real-time scores'
})

const {
  gamesCount,
  filteredGames,
  filteredGamesCount,
  categories,
  game_dates,
  teams,
  refs,
  showRefs,
  showFlags,
  fav_categories,
  fav_teams,
  fav_dates,
  loadGames,
  getFav,
  changeFav,
  resetAllFilters
} = useGames()

const handleRefresh = () => {
  visibleButton.value = false
  loadGames(true)
  setTimeout(() => {
    visibleButton.value = true
  }, 5000)
}

onMounted(async () => {
  await getFav()
  await loadGames()
})

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const hasActiveFilters = computed(() => {
  return fav_categories.value.length > 0 ||
         fav_teams.value.length > 0 ||
         showRefs.value !== true ||
         showFlags.value !== true
})

const hasActiveDateFilter = computed(() => {
  return fav_dates.value !== ''
})

const filteredTeamsAndRefs = computed(() => {
  const allItems = [...teams.value, ...refs.value]
  if (!teamSearchQuery.value) {
    return allItems
  }
  return allItems.filter(item =>
    item.toLowerCase().includes(teamSearchQuery.value.toLowerCase())
  )
})

const resetCategories = () => {
  fav_categories.value = []
  changeFav()
}

const resetTeams = () => {
  fav_teams.value = []
  changeFav()
}

const toggleCategory = (category, checked) => {
  if (checked) {
    if (!fav_categories.value.includes(category)) {
      fav_categories.value.push(category)
    }
  } else {
    const index = fav_categories.value.indexOf(category)
    if (index > -1) {
      fav_categories.value.splice(index, 1)
    }
  }
  changeFav()
}

const toggleTeam = (team, checked) => {
  if (checked) {
    if (!fav_teams.value.includes(team)) {
      fav_teams.value.push(team)
    }
  } else {
    const index = fav_teams.value.indexOf(team)
    if (index > -1) {
      fav_teams.value.splice(index, 1)
    }
  }
  changeFav()
}
</script>