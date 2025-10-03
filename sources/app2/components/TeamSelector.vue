<template>
  <div class="container">
    <div v-if="prefs?.scr_team_id && !showSelector" class="flex items-center justify-center gap-4 cursor-pointer" @click="loadTeams">
      <img
        v-if="prefs?.scr_team_logo"
        class="team-logo"
        :src="`${baseUrl}/img/${prefs.scr_team_logo}`"
        alt="Logo"
      />
      <span class="text-xl font-semibold">{{ prefs?.scr_team_label }}</span>
      <button class="px-4 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 flex items-center">
        <UIcon name="i-heroicons-arrow-path" class="h-4 w-4 mr-2" />
        {{ t('Teams.Change') }}
      </button>
    </div>
    <div v-else class="text-center">
      <button v-if="!showSelector" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="loadTeams">
        {{ t('Teams.SelectTeam') }}
      </button>
    </div>

    <form v-if="showSelector" class="mt-4">
      <div class="flex flex-col md:flex-row items-center justify-center gap-4">
        <div class="flex items-center gap-4">
          <img
            v-if="prefs?.scr_team_logo"
            class="team-logo"
            :src="`${baseUrl}/img/${prefs.scr_team_logo}`"
            alt="Logo"
          />
          <span class="text-xl font-semibold">{{ prefs?.scr_team_label }}</span>
        </div>
        <select
          v-model="teamSelected"
          class="px-3 py-2 border ring-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          @change="changeButton = true"
        >
          <option disabled value="0">
            ▼ {{ t('Teams.PleaseSelectOne') }} ▼
          </option>
          <option
            v-for="team in teams"
            :key="team.id"
            :value="team.team_id"
          >
            {{ team.label }}
          </option>
        </select>
        <div class="flex gap-2">
          <button
            class="px-4 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700"
            @click.prevent="cancelTeam"
          >
            {{ t('Teams.Cancel') }}
          </button>
          <button
            v-if="changeButton"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
            @click.prevent="changeTeam"
          >
            {{ t('Teams.Confirm') }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePrefs } from '~/composables/usePrefs'
import { useStatus } from '~/composables/useStatus'

const emit = defineEmits(['changeTeam'])
const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()
const apiBaseUrl = runtimeConfig.public.apiBaseUrl
const baseUrl = runtimeConfig.public.apiBaseUrl.replace('/api', '')


const { prefs, getPrefs, updatePref } = usePrefs()
const { checkOnline } = useStatus()
const { getCookie } = useApi()

const showSelector = ref(false)
const teamSelected = ref(0)
const changeButton = ref(false)
const teams = ref([])

const loadTeams = async () => {
  if (!checkOnline()) {
    return
  }

  const eventId = prefs.value?.lastEvent?.id
  if (!eventId) {
    console.error('No event selected')
    return
  }

  const token = getCookie('kpi_app')
  if (!token) {
    console.error('No authentication token found in cookie')
    return
  }

  try {
    const response = await fetch(`${apiBaseUrl}/staff/${eventId}/teams`, {
      headers: {
        'Cache-Control': 'no-cache',
        Pragma: 'no-cache',
        Expires: '0',
        'X-Auth-Token': token
      }
    })

    if (!response.ok) {
      throw new Error(`Failed to load teams: ${response.status}`)
    }

    const data = await response.json()
    teams.value = data.sort((a, b) => b.id - a.id)
    teamSelected.value = prefs.value?.scr_team_id || 0
    showSelector.value = true
  } catch (error) {
    console.error('Error loading teams:', error)
  }
}

const changeTeam = async () => {
  if (!checkOnline()) {
    return
  }

  const selectedTeam = teams.value.find(t => t.team_id === teamSelected.value)
  if (selectedTeam) {
    await updatePref({
      scr_team_id: selectedTeam.team_id,
      scr_team_label: selectedTeam.label,
      scr_team_club: selectedTeam.club,
      scr_team_logo: selectedTeam.logo
    })
    showSelector.value = false
    changeButton.value = false
    emit('changeTeam')
  }
}

const cancelTeam = () => {
  showSelector.value = false
  changeButton.value = false
}

onMounted(() => {
  getPrefs()
})
</script>

<style scoped>
.team-logo {
  max-height: 55px;
  max-width: 55px;
}
</style>
