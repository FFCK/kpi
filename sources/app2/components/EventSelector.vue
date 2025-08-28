<template>
  <div v-if="prefs" class="max-w-2xl mx-auto mt-6">
    <div
      v-if="prefs.event > 0"
      role="button"
      class="text-xl font-semibold text-center cursor-pointer"
      @click="loadEvents"
    >
      <img
        class="mb-2 event_logo mx-auto"
        :src="`${baseUrl}/img/${prefs.event_logo}`"
        alt="Logo"
        v-if="prefs.event_logo"
      />
      <br />
      {{ prefs.event_name }} - {{ prefs.event_place }}
      <button v-if="!showSelector" class="ml-2 px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition" >
        <i class="bi bi-arrow-left-right" /> {{ $t("Event.Change") }}
      </button>
    </div>
    <div v-else class="text-center">
      <button v-if="!showSelector" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" @click="loadEvents">
        {{ $t("Event.SelectEvent") }}
      </button>
    </div>

    <form v-if="showSelector" class="flex flex-col items-center mt-4">
      <div class="text-center mb-2">
        <div class="inline-flex rounded shadow overflow-hidden">
          <button
            type="button"
            :class="[
              'px-4 py-1 text-sm font-medium focus:outline-none transition',
              eventMode.value === 'std' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-blue-600'
            ]"
            @click="changeEventMode('std')"
          >
            {{ $t("Event.StdEvents") }}
          </button>
          <button
            type="button"
            :class="[
              'px-4 py-1 text-sm font-medium focus:outline-none transition',
              eventMode.value === 'champ' ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-blue-600'
            ]"
            @click="changeEventMode('champ')"
          >
            {{ $t("Event.LocalChamp") }}
          </button>
        </div>
      </div>
      <div class="w-full mb-2">
        <div class="flex justify-center">
          <select
            v-model="eventSelected.value"
            class="block w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:border-blue-500 text-center"
            @change="changeButton.value = true"
          >
            <option disabled value="0">
              ▼ {{ $t("Event.PleaseSelectOne") }} ▼
            </option>
            <option v-for="event in events.value" :key="event.id" :value="event.id">
              {{ event.id }} | {{ event.libelle }} - {{ event.place }}
            </option>
          </select>
        </div>
      </div>
      <div class="flex w-full justify-center gap-4 mt-2">
        <button class="px-4 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition" @click.prevent="cancelEvent">
          {{ $t("Event.Cancel") }}
        </button>
        <button
          v-if="changeButton.value"
          class="px-4 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition"
          @click.prevent="changeEvent"
        >
          {{ $t("Event.Confirm") }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePrefs } from '@/composables/usePrefs'
import { useUser } from '@/composables/useUser'
import { useStatus } from '@/composables/useStatus'
import idbs from '@/services/idbStorage'
import useFetchApi from '@/composables/useFetchApi'
import { useEventStore } from '@/stores/eventStore'
import { usePreferenceStore } from '@/stores/preferenceStore'
import { useGameStore } from '@/stores/gameStore'

const baseUrl = process.env.VUE_APP_BASE_URL
const showSelector = ref(false)
const eventSelected = ref(0)
const changeButton = ref(false)

const prefs = usePrefs()
const user = useUser()
const status = useStatus()
const fetchApi = useFetchApi()
const eventStore = useEventStore()
const preferenceStore = usePreferenceStore()
const gameStore = useGameStore()

const eventMode = computed(() => preferenceStore.preferences.events ?? 'std')
const events = computed(() => eventStore.events)

function changeEventMode(mode) {
  if (mode !== eventMode.value) {
    preferenceStore.updatePreferences({ events: mode })
    idbs.dbPut('preferences', preferenceStore.preferences)
    loadEvents()
  }
}

async function loadEvents() {
  if (!(await status.checkOnline())) {
    return
  }
  eventStore.loading = true
  try {
    const result = await fetchApi.getEvents(eventMode.value)
    const eventsResult = result.data.map(event => {
      event.id = parseInt(event.id)
      return event
    })
    eventStore.events = eventsResult
    eventSelected.value = prefs.value.event
    showSelector.value = true
  } catch (error) {
    if (error.message === 'Network Error') {
      console.log('Offline !')
    }
    eventStore.error = error
  } finally {
    eventStore.loading = false
  }
}

async function changeEvent() {
  if (!(await status.checkOnline())) {
    return
  }
  const e = eventStore.events.find(ev => ev.id === eventSelected.value)
  preferenceStore.updatePreferences({
    event: e.id,
    event_name: e.libelle,
    event_place: e.place,
    event_logo: e.logo,
    fav_categories: '[]',
    fav_teams: '[]',
    fav_refs: '[]',
    fav_dates: '',
    fav_flags: true
  })
  idbs.dbPut('preferences', preferenceStore.preferences)
  Games.deleteAll()
  idbs.dbClear('games')
  idbs.dbClear('charts')
  showSelector.value = false
  changeButton.value = false
  // $emit equivalent in <script setup>:
  // If you need to emit, use defineEmits
  // const emit = defineEmits(['changeEvent'])
  // emit('changeEvent')
}

function cancelEvent() {
  showSelector.value = false
  changeButton.value = false
}
</script>

<style scoped>
.event_logo {
  max-height: 55px;
  max-width: 100%;
}
</style>
