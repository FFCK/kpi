<template>
  <div>
    <AppTitle>KPI Application</AppTitle>

    <div class="flex justify-center">
      <button v-if="!eventStore.loading" @click="fetchEvents" class="ml-2 px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition cursor-pointer" >
        <i class="bi bi-arrow-left-right"></i> Refresh
      </button>

      <!-- spinner -->
      <div v-if="eventStore.loading">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
      </div>

      <!-- selecteur d'évènements -->
      <div v-if="eventStore.events.length > 0 && !eventStore.loading" class="ml-2 px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition">
        <select v-model="selectedEventId" @change="selectEvent" class="block w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-500 text-center">
          <option v-for="event in eventStore.events" :key="event.id" :value="event.id">
            {{ event.id }} | {{ event.libelle }} - {{ event.place }}
          </option>
        </select>
      </div>

      <!-- événement sélectionné -->
      <div v-if="preferenceStore.preferences.lastEvent" class="ml-2 px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition">
        {{ preferenceStore.preferences.lastEvent.id }} | {{ preferenceStore.preferences.lastEvent.libelle }} - {{ preferenceStore.preferences.lastEvent.place }}
      </div>
      
    </div>
    
    <div class="flex justify-around my-5">
      <NuxtLink to="/games" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">Matchs</NuxtLink>
      <NuxtLink to="/charts" class="border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white font-semibold rounded px-4 py-2 text-lg w-2/5 transition-colors text-center">Progression</NuxtLink>
    </div>

    <AppAlert>
      This is an auto-imported component
    </AppAlert>
    
    <div class="text-center">
      <img class="w-30" src="/img/logo_kp.png" alt="kayak-polo" />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, toRaw } from 'vue'
const { getApi } = useApi()
const runtimeConfig = useRuntimeConfig()
const apiBaseUrl = runtimeConfig.public.apiBaseUrl
const eventStore = useEventStore()
const preferenceStore = usePreferenceStore()
const selectedEvent = ref({})
const selectedEventId = ref(null)
const eventMode = ref('std')

const fetchApi = async (url) => {
  const response = await getApi(url)
  const data = await response.json()
  return data
}

const fetchEvents = async () => {
  eventStore.loading = true
  try {
    const result = await fetchApi(`${apiBaseUrl}/events/${eventMode.value}`)
    const eventsResult = result.map(event => {
      event.id = parseInt(event.id)
      return event
    })
    await eventStore.clearAndUpdateEvents(eventsResult)
  } catch (error) {
    eventStore.error = error
  } finally {
    eventStore.loading = false
  }
}

const selectEvent = async () => {
  selectedEvent.value = await eventStore.getEventById(selectedEventId.value)
  eventStore.selectedEvent = selectedEvent.value
  await preferenceStore.putItem('lastEvent', toRaw(selectedEvent.value))
}

onMounted(async () => {
  await preferenceStore.fetchItems()
  // if (eventStore.events.length === 0) {
  //   await fetchEvents()
  // }
  if (preferenceStore.preferences.lastEvent) {
    selectedEventId.value = preferenceStore.preferences.lastEvent.id
    selectedEvent.value = preferenceStore.preferences.lastEvent
    eventStore.selectedEvent = selectedEvent.value
  }
})
</script>