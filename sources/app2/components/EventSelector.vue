<template>
  <!-- Loading spinner for initial load -->
  <div v-if="isInitialLoading" class="mt-3 text-center">
    <UIcon name="i-heroicons-arrow-path" class="h-8 w-8 animate-spin text-blue-600 mx-auto" />
    <p class="mt-2 text-gray-600">{{ t('Event.Loading') }}</p>
  </div>

  <div v-else-if="preferenceStore.preferences" class="mt-3">
    <!-- Display selected event -->
    <div v-if="preferenceStore.preferences.lastEvent && !showSelector" role="button" class="text-center" @click="loadEvents">
      <img
        v-if="preferenceStore.preferences.lastEvent.logo"
        class="mb-2 mx-auto max-h-14"
        :src="`/img/${preferenceStore.preferences.lastEvent.logo}`"
        alt="Logo"
      />
      <br />
      <span class="font-semibold">{{ preferenceStore.preferences.lastEvent.libelle }} - {{ preferenceStore.preferences.lastEvent.place }}</span>
      <button class="ml-2 px-2 py-1 bg-gray-500 text-white text-xs rounded cursor-pointer">
        <UIcon name="i-heroicons-arrows-right-left" /> {{ t('Event.Change') }}
      </button>
    </div>
    <!-- Button to select event if none is selected -->
    <div v-else-if="!showSelector" class="text-center">
      <button class="px-4 py-2 bg-blue-600 text-white rounded cursor-pointer" @click="loadEvents">
        {{ t('Event.SelectEvent') }}
      </button>
    </div>

    <!-- Event selection form -->
    <form v-if="showSelector" class="text-center">
      <!-- Event mode selector -->
      <div class="mb-1 inline-flex rounded-md shadow-sm" role="group">
        <button
          type="button"
          @click="changeEventMode('std')"
          :class="['px-4 py-1 text-sm font-medium rounded-l-lg border border-gray-200 cursor-pointer', eventMode === 'std' ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-100']"
        >
          {{ t('Event.StdEvents') }}
        </button>
        <button
          type="button"
          @click="changeEventMode('champ')"
          :class="['px-4 py-1 text-sm font-medium rounded-r-md border border-gray-200 cursor-pointer', eventMode === 'champ' ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-100']"
        >
          {{ t('Event.LocalChamp') }}
        </button>
      </div>
      
      <!-- Event dropdown -->
      <div class="my-2 max-w-md mx-auto">
        <select v-model="eventSelectedId" @change="changeButton = true" class="block w-full px-3 py-2 border border-gray-400 rounded focus:outline-none focus:ring focus:border-blue-500 cursor-pointer">
          <option disabled :value="null">
            ▼ {{ t('Event.PleaseSelectOne') }} ▼
          </option>
          <option v-for="event in events" :key="event.id" :value="event.id">
            {{ event.id }} | {{ event.libelle }} - {{ event.place }}
          </option>
        </select>
      </div>

      <!-- Action buttons -->
      <div class="flex justify-center space-x-4">
        <button @click.prevent="cancelEvent" class="px-4 py-1 bg-gray-500 text-white text-sm rounded cursor-pointer">
          {{ t('Event.Cancel') }}
        </button>
        <button v-if="changeButton" @click.prevent="changeEvent" class="px-4 py-1 bg-blue-600 text-white text-sm rounded cursor-pointer">
          {{ t('Event.Confirm') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, toRaw } from 'vue'
import { usePreferenceStore } from '~/stores/preferenceStore';
import { useEventStore } from '~/stores/eventStore';
import { useGames } from '~/composables/useGames';
import db from '~/utils/db'
const { t } = useI18n()

// Stores & Composables
const preferenceStore = usePreferenceStore()
const eventStore = useEventStore()
const { resetAllFilters } = useGames()
const { getApi } = useApi()

// State
const showSelector = ref(false)
const eventSelectedId = ref(null)
const changeButton = ref(false)
const eventMode = ref('std') // Default mode
const isInitialLoading = ref(true) // Track initial loading from IndexedDB

// Computed
const events = computed(() => {
  if (eventMode.value === 'std') {
    // Create a copy and sort it in descending order by id
    return [...eventStore.events].sort((a, b) => b.id - a.id)
  }
  return eventStore.events
})

// Methods
const changeEventMode = async (mode) => {
  if (mode !== eventMode.value) {
    eventMode.value = mode
    // NOTE: The old component saved eventMode to preferences.
    // This version keeps it as local component state.
    await loadEvents()
  }
}

const loadEvents = async () => {
  eventStore.loading = true
  showSelector.value = false // Hide selector while loading
  try {
    const result = await getApi(`/events/${eventMode.value}`)
    if (!result.ok) {
      throw new Error(`HTTP error! status: ${result.status}`)
    }
    const eventsData = await result.json()
    const eventsResult = eventsData.map(event => ({ ...event, id: parseInt(event.id) }))
    
    await eventStore.clearAndUpdateEvents(eventsResult)
    
    eventSelectedId.value = preferenceStore.preferences.lastEvent?.id || null
    showSelector.value = true
  } catch (error) {
    eventStore.error = error
    console.error('Failed to load events:', error)
  } finally {
    eventStore.loading = false
  }
}

const changeEvent = async () => {
  if (!eventSelectedId.value) return

  const selectedEvent = eventStore.getEventById(eventSelectedId.value)
  if (!selectedEvent) return

  // Clear old event data from IndexedDB when changing events
  const lastEvent = preferenceStore.preferences.lastEvent
  if (lastEvent && lastEvent.id !== eventSelectedId.value) {
    await Promise.all([
      db.games.clear(),
      db.charts.clear(),
      preferenceStore.putItem('games_last_api_load', 0),
      preferenceStore.putItem('charts_last_api_load', 0)
    ])
  }

  await preferenceStore.putItem('lastEvent', toRaw(selectedEvent))
  await preferenceStore.putItem('last_team', null)

  // Reset all game filters when changing event
  await resetAllFilters()

  showSelector.value = false
  changeButton.value = false

  // NOTE: The old component emitted a 'changeEvent'.
  // If the parent page needs to react, defineEmits can be used here.
}

const cancelEvent = () => {
  showSelector.value = false
  changeButton.value = false
}

onMounted(async () => {
  try {
    // Fetch initial preferences when the component is mounted
    await preferenceStore.fetchItems()
    // Set initial selected event ID if a preference exists
    if (preferenceStore.preferences.lastEvent) {
      eventSelectedId.value = preferenceStore.preferences.lastEvent.id
    }
  } catch (error) {
    console.error('Failed to load preferences:', error)
  } finally {
    // Hide loading spinner after initial load
    isInitialLoading.value = false
  }
})
</script>
