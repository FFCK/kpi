<template>
  <div class="container-fluid mb-16">
    <div class="p-4 bg-white border-b border-gray-200">
      <div class="flex items-center">
        <button @click="navigateTo('/')" class="p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
        </button>
        <button @click="showFilters = !showFilters" class="ml-4 p-2 rounded-md hover:bg-gray-100">
          {{ t('nav.Filters') }} <UIcon name="i-heroicons-filter" class="h-6 w-6" />
        </button>
        <select v-model="fav_dates" @change="changeFav" class="ml-4 block w-auto pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
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
        <button :disabled="!visibleButton" @click="loadGames" class="ml-4 p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
        </button>
        <button @click="navigateTo('/charts')" class="ml-4 p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-right" class="h-6 w-6" />
        </button>
      </div>
    </div>

    <div v-if="showFilters" class="p-4 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ t('Games.Categories') }}</label>
          <select v-model="fav_categories" multiple @change="changeFav">
            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ t('Games.Teams') }} & {{ t('Games.Refs') }}</label>
          <select v-model="fav_teams" multiple @change="changeFav">
            <option v-for="team in teams" :key="team" :value="team">{{ team }}</option>
            <option v-for="ref in refs" :key="ref" :value="ref">{{ ref }}</option>
          </select>
        </div>
        <div class="flex items-center">
          <UToggle v-model="showRefs" />
          <label class="ml-2 block text-sm font-medium text-gray-700">{{ t('Games.ShowRefs') }}</label>
        </div>
        <div class="flex items-center">
          <UToggle v-model="showFlags" @change="changeFav" />
          <label class="ml-2 block text-sm font-medium text-gray-700">{{ t('Games.ShowFlags') }}</label>
        </div>
      </div>
    </div>

    <GameList :games="filteredGames" :show-refs="showRefs" :show-flags="showFlags" :games-count="gamesCount" :filtered-games-count="filteredGamesCount" :key="locale" />

    <button @click="scrollToTop" class="fixed bottom-4 right-4 bg-gray-800 hover:bg-gray-700 text-white font-bold p-3 rounded-full">
      <UIcon name="i-heroicons-arrow-up" class="h-6 w-6" />
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useGames } from '~/composables/useGames'
import GameList from '~/components/GameList.vue'
import { navigateTo } from '#app'

const { t, d, locale } = useI18n()
const showFilters = ref(false)

const {
  games,
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
  visibleButton,
  loadGames,
  getFav,
  changeFav
} = useGames()

onMounted(() => {
  getFav()
  loadGames().then(() => {
    console.log('categories', categories.value)
    console.log('teams', teams.value)
    console.log('refs', refs.value)
  })
})

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>