<template>
  <!-- Loading spinner for initial load -->
  <div v-if="isInitialLoading" class="mt-3 text-center">
    <UIcon name="i-heroicons-arrow-path" class="h-8 w-8 animate-spin text-blue-600 mx-auto" />
    <p class="mt-2 text-gray-600">{{ t('Event.Loading') }}</p>
  </div>

  <div v-else-if="preferenceStore.preferences" class="mt-3">
    <!-- Display selected event or group -->
    <div v-if="hasSelection && !showSelector" role="button" class="text-center" @click="loadEvents">
      <!-- Event display -->
      <template v-if="preferenceStore.preferences.lastEvent">
        <img
          v-if="preferenceStore.preferences.lastEvent.logo"
          class="mb-2 mx-auto max-h-14"
          :src="`/img/${preferenceStore.preferences.lastEvent.logo}`"
          alt="Logo"
        />
        <br />
        <span class="font-semibold">{{ preferenceStore.preferences.lastEvent.libelle }} - {{ preferenceStore.preferences.lastEvent.place }}</span>
      </template>
      <!-- Group display -->
      <template v-else-if="preferenceStore.preferences.lastGroup">
        <span class="font-semibold">{{ preferenceStore.preferences.lastGroup.code }} - {{ getGroupLabel(preferenceStore.preferences.lastGroup) }}</span>
        <span class="text-sm text-gray-500 ml-2">({{ t('Season.Label') }}: {{ preferenceStore.preferences.lastSeason }})</span>
      </template>
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
          @click="changeEventMode('group')"
          :class="['px-4 py-1 text-sm font-medium rounded-l-lg border border-gray-200 cursor-pointer', eventMode === 'group' ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-100']"
        >
          {{ t('Event.Competitions') }}
        </button>
        <button
          type="button"
          @click="changeEventMode('std')"
          :class="['px-4 py-1 text-sm font-medium rounded-r-md border border-gray-200 cursor-pointer', eventMode === 'std' ? 'bg-blue-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-100']"
        >
          {{ t('Event.StdEvents') }}
        </button>
      </div>

      <!-- Event dropdown (for std and champ modes) -->
      <div v-if="eventMode !== 'group'" class="my-2 max-w-md mx-auto">
        <!-- Season selector for events -->
        <div class="mb-2 flex items-center justify-center gap-2">
          <label class="text-sm text-gray-600">{{ t('Season.Label') }}:</label>
          <select
            v-model="selectedEventSeason"
            @change="onEventSeasonChange"
            class="px-3 py-2 border border-gray-400 rounded focus:outline-none focus:ring focus:border-blue-500 cursor-pointer"
          >
            <option v-for="year in availableEventSeasons" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
        <!-- Event dropdown -->
        <select v-model="eventSelectedId" @change="changeButton = true" class="block w-full px-3 py-2 border border-gray-400 rounded focus:outline-none focus:ring focus:border-blue-500 cursor-pointer">
          <option disabled :value="null">
            ▼ {{ t('Event.PleaseSelectOne') }} ▼
          </option>
          <option v-for="event in filteredEvents" :key="event.id" :value="event.id">
            {{ event.id }} | {{ event.libelle }} - {{ event.place }}
          </option>
        </select>
      </div>

      <!-- Group mode: season selector + group dropdown -->
      <div v-else class="my-2 max-w-md mx-auto">
        <!-- Season selector -->
        <div class="mb-2 flex items-center justify-center gap-2">
          <label class="text-sm text-gray-600">{{ t('Season.Label') }}:</label>
          <select
            v-model="selectedSeason"
            @change="onSeasonChange"
            class="px-3 py-2 border border-gray-400 rounded focus:outline-none focus:ring focus:border-blue-500 cursor-pointer"
          >
            <option v-for="year in availableSeasons" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
        <!-- Group dropdown -->
        <select v-model="selectedGroupCode" @change="changeButton = true" class="block w-full px-3 py-2 border border-gray-400 rounded focus:outline-none focus:ring focus:border-blue-500 cursor-pointer">
          <option disabled :value="null">
            ▼ {{ t('Event.PleaseSelectGroup') }} ▼
          </option>
          <optgroup v-for="section in groupSections" :key="section.section" :label="t(`Section.${section.label}`)">
            <option v-for="group in section.groups" :key="group.code" :value="group.code">
              {{ group.code }} - {{ getGroupLabel(group) }}
            </option>
          </optgroup>
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
import { useGroupStore } from '~/stores/groupStore';
import db from '~/utils/db'
const { t } = useI18n()
const { getGroupLabel } = useGroupLabel()

// Stores & Composables
const preferenceStore = usePreferenceStore()
const eventStore = useEventStore()
const groupStore = useGroupStore()
const { getApi } = useApi()

// State
const showSelector = ref(false)
const eventSelectedId = ref(null)
const selectedGroupCode = ref(null)
const changeButton = ref(false)
const eventMode = ref('std') // Default mode: 'std', 'champ', or 'group'
const isInitialLoading = ref(true)
const groupSections = ref([])

// Season management
const currentYear = new Date().getFullYear()
const selectedSeason = ref(currentYear.toString())
const selectedEventSeason = ref(currentYear.toString())
const availableSeasons = computed(() => {
  const seasons = []
  for (let i = 0; i < 6; i++) {
    seasons.push((currentYear - i).toString())
  }
  return seasons
})

// Available seasons for events (based on loaded events)
const availableEventSeasons = computed(() => {
  const years = new Set()
  eventStore.events.forEach(event => {
    if (event.year) {
      years.add(event.year.toString())
    }
  })
  // Sort descending and return array
  const sortedYears = Array.from(years).sort((a, b) => parseInt(b) - parseInt(a))
  // If no years found, return default list
  if (sortedYears.length === 0) {
    return availableSeasons.value
  }
  return sortedYears
})

// Computed
const events = computed(() => {
  if (eventMode.value === 'std') {
    return [...eventStore.events].sort((a, b) => b.id - a.id)
  }
  return eventStore.events
})

// Filtered events by selected season
const filteredEvents = computed(() => {
  const selectedYear = parseInt(selectedEventSeason.value)
  const filtered = eventStore.events.filter(event => event.year === selectedYear)
  if (eventMode.value === 'std') {
    return [...filtered].sort((a, b) => b.id - a.id)
  }
  return filtered
})

const hasSelection = computed(() => {
  return (preferenceStore.preferences?.lastEvent !== undefined && preferenceStore.preferences?.lastEvent !== null) ||
         (preferenceStore.preferences?.lastGroup !== undefined && preferenceStore.preferences?.lastGroup !== null)
})

// Methods
const changeEventMode = async (mode) => {
  if (mode !== eventMode.value) {
    eventMode.value = mode
    changeButton.value = false
    if (mode === 'group') {
      await loadGroups()
    } else {
      await loadEvents()
    }
  }
}

const loadEvents = async () => {
  if (eventMode.value === 'group') {
    await loadGroups()
    return
  }

  eventStore.loading = true
  showSelector.value = false
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

const loadGroups = async () => {
  groupStore.loading = true
  showSelector.value = false
  try {
    // Use selected season
    const season = selectedSeason.value
    groupStore.selectSeason(season)

    const result = await getApi(`/groups/${season}`)
    if (!result.ok) {
      throw new Error(`HTTP error! status: ${result.status}`)
    }
    const data = await result.json()
    groupStore.setSections(data.sections)
    groupSections.value = data.sections

    selectedGroupCode.value = preferenceStore.preferences.lastGroup?.code || null
    showSelector.value = true
  } catch (error) {
    groupStore.error = error
    console.error('Failed to load groups:', error)
  } finally {
    groupStore.loading = false
  }
}

// Called when season is changed in the dropdown (for groups)
const onSeasonChange = async () => {
  const previousSeason = groupStore.getCurrentSeason
  groupStore.selectSeason(selectedSeason.value)

  // Keep the current group selection if changing season only
  // Show confirm button if we have a group selected (even from a previous season)
  const hadGroupSelected = selectedGroupCode.value !== null
  selectedGroupCode.value = null // Reset group selection for new season's groups

  await loadGroups()

  // If there was a previously selected group and we changed season, show confirm button
  // This allows user to confirm the season change with a new group
  if (hadGroupSelected || previousSeason !== selectedSeason.value) {
    changeButton.value = true
  }
}

// Called when season is changed for events
const onEventSeasonChange = () => {
  // Reset event selection when changing season
  const hadEventSelected = eventSelectedId.value !== null
  eventSelectedId.value = null

  // Show confirm button if we had an event selected
  if (hadEventSelected) {
    changeButton.value = true
  }
}

// Reset game filters directly via preferences (avoids composable context issues)
const resetGameFilters = async () => {
  await Promise.all([
    preferenceStore.putItem('fav_categories', '[]'),
    preferenceStore.putItem('fav_teams', '[]'),
    preferenceStore.putItem('fav_dates', ''),
    preferenceStore.putItem('show_flags', true)
  ])
}

const changeEvent = async () => {
  if (eventMode.value === 'group') {
    await changeGroup()
    return
  }

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
  await preferenceStore.putItem('lastGroup', null)
  await preferenceStore.putItem('lastSeason', null)
  await preferenceStore.putItem('eventMode', eventMode.value)
  await preferenceStore.putItem('last_team', null)

  // Reset scrutineering team data when changing event
  await Promise.all([
    preferenceStore.putItem('scr_team_id', null),
    preferenceStore.putItem('scr_team_label', null),
    preferenceStore.putItem('scr_team_club', null),
    preferenceStore.putItem('scr_team_logo', null)
  ])

  // Reset all game filters when changing event
  await resetGameFilters()

  showSelector.value = false
  changeButton.value = false
}

const changeGroup = async () => {
  if (!selectedGroupCode.value) return

  const selectedGroup = groupStore.getGroupByCode(selectedGroupCode.value)
  if (!selectedGroup) return

  const season = groupStore.getCurrentSeason

  // Clear old data from IndexedDB when changing groups
  await Promise.all([
    db.games.clear(),
    db.charts.clear(),
    preferenceStore.putItem('games_last_api_load', 0),
    preferenceStore.putItem('charts_last_api_load', 0)
  ])

  await preferenceStore.putItem('lastGroup', toRaw(selectedGroup))
  await preferenceStore.putItem('lastSeason', season)
  await preferenceStore.putItem('lastEvent', null)
  await preferenceStore.putItem('eventMode', 'group')
  await preferenceStore.putItem('last_team', null)

  // Reset scrutineering team data when changing group
  await Promise.all([
    preferenceStore.putItem('scr_team_id', null),
    preferenceStore.putItem('scr_team_label', null),
    preferenceStore.putItem('scr_team_club', null),
    preferenceStore.putItem('scr_team_logo', null)
  ])

  // Reset all game filters when changing group
  await resetGameFilters()

  showSelector.value = false
  changeButton.value = false
}

const cancelEvent = () => {
  showSelector.value = false
  changeButton.value = false
}

onMounted(async () => {
  const route = useRoute()

  try {
    await preferenceStore.fetchItems()

    // Set initial selected event/group based on preferences
    if (preferenceStore.preferences.lastEvent) {
      eventSelectedId.value = preferenceStore.preferences.lastEvent.id
      eventMode.value = preferenceStore.preferences.eventMode || 'std'
    } else if (preferenceStore.preferences.lastGroup) {
      selectedGroupCode.value = preferenceStore.preferences.lastGroup.code
      eventMode.value = 'group'
      // Restore season from preferences
      if (preferenceStore.preferences.lastSeason) {
        selectedSeason.value = preferenceStore.preferences.lastSeason
        groupStore.selectSeason(preferenceStore.preferences.lastSeason)
      }
    } else if (preferenceStore.preferences.lastSeason) {
      // Restore season even if no group is selected (for season selector display)
      selectedSeason.value = preferenceStore.preferences.lastSeason
      groupStore.selectSeason(preferenceStore.preferences.lastSeason)
    }
  } catch (error) {
    console.error('Failed to load preferences:', error)
  } finally {
    isInitialLoading.value = false

    // Check if there's a pending redirect from query parameter
    if (hasSelection.value && route.query.redirect) {
      const targetUrl = String(route.query.redirect)
      await navigateTo(targetUrl, { replace: true })
    }
  }
})
</script>
