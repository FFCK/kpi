<template>
  <div class="container-fluid">
    <div class="p-4 bg-white border-b border-gray-200">
      <div class="flex items-center">
        <button @click="navigateTo('/')" class="p-2 rounded-md hover:bg-gray-100">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
        </button>
        <button @click="showFilters = !showFilters" class="ml-4 p-2 rounded-md hover:bg-gray-100">
          {{ t('nav.Filters') }} <UIcon name="i-heroicons-filter" class="h-6 w-6" />
        </button>
        <select v-model="fav_dates" @change="changeFav" class="ml-4 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
          <option value="">{{ t('Games.AllDates') }}</option>
          <option v-for="(game_date, index) in game_dates" :key="index" :value="game_date">{{ d(new Date(game_date), 'short') }}</option>
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
          <USelectMenu v-model="fav_categories" :options="categories" multiple searchable @change="changeFav" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ t('Games.Teams') }} & {{ t('Games.Refs') }}</label>
          <USelectMenu v-model="fav_teams" :options="teams.concat(refs)" multiple searchable @change="changeFav" />
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
  loadGames()
})

</script>
