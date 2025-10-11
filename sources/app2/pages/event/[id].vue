<template>
  <div class="p-8 text-center text-gray-500">
    <p>{{ t('Event.Selecting') }}</p>
  </div>
</template>

<script setup>
import { onMounted, toRaw } from 'vue'
import { useRoute } from 'vue-router'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useEventStore } from '~/stores/eventStore'
import { navigateTo } from '#app'
import { useApi } from '~/composables/useApi'
import db from '~/utils/db'

// Disable prerendering and SSR for this route since it only redirects
definePageMeta({
  prerender: false,
  ssr: false
})

const route = useRoute()
const preferenceStore = usePreferenceStore()
const eventStore = useEventStore()
const { getApi } = useApi()
const { t } = useI18n()

// Function to fetch all events (both std and champ)
const fetchAllEvents = async () => {
  try {
    const [stdResponse, champResponse] = await Promise.all([
      getApi('/events/std'),
      getApi('/events/champ')
    ])

    if (!stdResponse.ok || !champResponse.ok) {
      throw new Error('Failed to fetch events')
    }

    const stdEvents = await stdResponse.json()
    const champEvents = await champResponse.json()
    
    const allEvents = [...stdEvents, ...champEvents].map(event => ({ ...event, id: parseInt(event.id) }))
    await eventStore.clearAndUpdateEvents(allEvents)
    return true
  } catch (error) {
    console.error('Error fetching all events:', error)
    return false
  }
}

onMounted(async () => {
  const eventId = parseInt(route.params.id)
  if (isNaN(eventId)) {
    return navigateTo('/', { replace: true })
  }

  await preferenceStore.fetchItems()

  // Clear old event data from IndexedDB when changing events
  const lastEvent = preferenceStore.preferences.lastEvent
  if (lastEvent && lastEvent.id !== eventId) {
    await Promise.all([
      db.games.clear(),
      db.charts.clear(),
      preferenceStore.putItem('games_last_api_load', 0),
      preferenceStore.putItem('charts_last_api_load', 0)
    ])
  }

  const success = await fetchAllEvents()

  if (success) {
    const selectedEvent = eventStore.getEventById(eventId)
    if (selectedEvent) {
      await preferenceStore.putItem('lastEvent', toRaw(selectedEvent))
      await preferenceStore.putItem('last_team', null) // Reset team preference
    }
  }

  // Always redirect to home, which will now use the new context
  await navigateTo('/', { replace: true })
})
</script>
