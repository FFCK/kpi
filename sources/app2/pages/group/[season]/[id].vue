<template>
  <ClientOnly>
    <div class="p-8 text-center text-gray-500">
      <p>{{ t('Event.Selecting') }}</p>
    </div>
  </ClientOnly>
</template>

<script setup>
import { onMounted, toRaw } from 'vue'
import { useRoute } from 'vue-router'
import { usePreferenceStore } from '~/stores/preferenceStore'
import { useGroupStore } from '~/stores/groupStore'
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
const groupStore = useGroupStore()
const { getApi } = useApi()
const { t } = useI18n()

// Function to fetch groups for a specific season
const fetchGroups = async (season) => {
  try {
    const result = await getApi(`/groups/${season}`)
    if (!result.ok) {
      throw new Error('Failed to fetch groups')
    }
    const data = await result.json()
    groupStore.selectSeason(season)
    groupStore.setSections(data.sections)
    return true
  } catch (error) {
    console.error('Error fetching groups:', error)
    return false
  }
}

onMounted(async () => {
  const season = route.params.season
  const groupCode = route.params.id

  if (!season || !groupCode) {
    return navigateTo('/', { replace: true })
  }

  await preferenceStore.fetchItems()

  // Clear old data from IndexedDB when changing groups
  const lastGroup = preferenceStore.preferences.lastGroup
  const lastSeason = preferenceStore.preferences.lastSeason
  if (lastGroup && (lastGroup.code !== groupCode || lastSeason !== season)) {
    await Promise.all([
      db.games.clear(),
      db.charts.clear(),
      preferenceStore.putItem('games_last_api_load', 0),
      preferenceStore.putItem('charts_last_api_load', 0)
    ])
  }

  const success = await fetchGroups(season)

  if (success) {
    const selectedGroup = groupStore.getGroupByCode(groupCode)
    if (selectedGroup) {
      await preferenceStore.putItem('lastGroup', toRaw(selectedGroup))
      await preferenceStore.putItem('lastSeason', season)
      await preferenceStore.putItem('lastEvent', null)
      await preferenceStore.putItem('eventMode', 'group')
      await preferenceStore.putItem('last_team', null)
    }
  }

  // Always redirect to home, which will now use the new context
  await navigateTo('/', { replace: true })
})
</script>
