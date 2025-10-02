<template>
  <div class="container-fluid mb-16">
    <div class="p-4 bg-white border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <button @click="navigateTo('/games')" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
          </button>
          <button
            @click="showFilters = !showFilters"
            class="ml-4 px-3 py-2 border-2 rounded-md transition-colors flex items-center space-x-1 text-base hover:bg-gray-100"
            :style="hasActiveFilters ? 'background-color: #dbeafe; border: 2px solid #60a5fa; color: #1e40af;' : 'border: 2px solid #d1d5db;'"
          >
            <span>{{ t('nav.Filters') }}</span>
            <UIcon name="i-heroicons-filter" class="h-4 w-4" />
            <UIcon
              :name="showFilters ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
              class="h-4 w-4"
            />
          </button>
        </div>
        <div class="flex items-center">
          <button :disabled="!visibleButton" @click="loadCharts" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
          </button>
          <button @click="navigateTo('/about')" class="ml-4 p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-right" class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

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
            <label class="block text-sm font-medium text-gray-700">{{ t('Games.Teams') }}</label>
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
            <div v-for="team in filteredTeams" :key="team" class="flex items-center">
              <UCheckbox
                :model-value="fav_teams.includes(team)"
                @update:model-value="(checked) => toggleTeam(team, checked)"
                :id="`team-${team}`"
              />
              <label :for="`team-${team}`" class="ml-2 text-sm text-gray-700 cursor-pointer">
                {{ team }}
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Charts v-if="chartData" :key="chartIndex" :chart-data="chartData" :show-flags="showFlags" />

    <button @click="scrollToTop" class="fixed bottom-4 right-4 bg-gray-800 hover:bg-gray-700 text-white font-bold p-3 rounded-full">
      <UIcon name="i-heroicons-arrow-up" class="h-6 w-6" />
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCharts } from '~/composables/useCharts'
import Charts from '~/components/Charts.vue'
import { navigateTo } from '#app'

const { t } = useI18n()
const showFilters = ref(false)
const teamSearchQuery = ref('')

const {
  chartData,
  chartIndex,
  visibleButton,
  showFlags,
  categories,
  teams,
  fav_categories,
  fav_teams,
  loadCharts,
  getFav,
  changeFav
} = useCharts()

onMounted(async () => {
  await getFav()
  await loadCharts()
})

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const hasActiveFilters = computed(() => {
  return fav_categories.value.length > 0 || fav_teams.value.length > 0
})

const filteredTeams = computed(() => {
  if (!teamSearchQuery.value) {
    return teams.value
  }
  return teams.value.filter(team =>
    team.toLowerCase().includes(teamSearchQuery.value.toLowerCase())
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