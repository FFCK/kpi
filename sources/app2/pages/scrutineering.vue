<template>
  <div class="container-fluid mb-16">
    <div class="p-4 bg-white border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <button @click="navigateTo('/')" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
          </button>
          <h1 class="text-2xl font-bold flex items-center">
            <UIcon name="i-heroicons-clipboard-document-check" class="h-6 w-6 mr-2" />
            {{ t('Scrutineering.Scrutineering') }}
          </h1>
        </div>
        <div v-if="prefs?.scr_team_id" class="flex items-center space-x-2">
          <button @click="loadPlayers" class="p-2 rounded-md hover:bg-gray-100">
            <UIcon name="i-heroicons-arrow-path" class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

    <div v-if="user">
      <div v-if="authorized && user.profile <= 3" class="p-4">
        <TeamSelector @change-team="loadPlayers" />

        <div v-if="prefs?.scr_team_id" class="mt-4">
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Player') }}
                  </th>
                  <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Kayak') }}
                  </th>
                  <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Vest') }}
                  </th>
                  <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Helmet') }}
                  </th>
                  <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Paddles') }}
                  </th>
                  <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">
                    {{ t('Scrutineering.Print') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="player in players" :key="player.player_id" class="border-b hover:bg-gray-50">
                  <td class="px-4 py-2">
                    <span v-if="player.cap !== 'E'" class="inline-block px-2 py-1 text-xs font-bold text-white bg-gray-800 rounded mr-2">
                      {{ player.num }}
                    </span>
                    <span v-if="player.cap === 'E'" class="inline-block px-2 py-1 text-xs font-bold text-white bg-gray-800 rounded mr-2">
                      {{ t('Scrutineering.Coach') }}
                    </span>
                    {{ player.last_name }} {{ player.first_name }}
                    <span v-if="player.cap === 'C'" class="inline-block px-2 py-1 text-xs font-bold text-gray-900 bg-yellow-400 rounded ml-2">
                      C
                    </span>
                  </td>
                  <td v-if="player.cap !== 'E'" class="px-4 py-2 text-center border-r">
                    <button
                      type="button"
                      :class="getEquipmentButtonClass(player.kayak_status)"
                      @click="updatePlayer(player.player_id, 'kayak_status', player.kayak_status)"
                    >
                      <UIcon :name="getEquipmentIcon(player.kayak_status)" class="h-5 w-5" />
                    </button>
                  </td>
                  <td v-if="player.cap !== 'E'" class="px-4 py-2 text-center">
                    <button
                      type="button"
                      :class="getEquipmentButtonClass(player.vest_status)"
                      @click="updatePlayer(player.player_id, 'vest_status', player.vest_status)"
                    >
                      <UIcon :name="getEquipmentIcon(player.vest_status)" class="h-5 w-5" />
                    </button>
                  </td>
                  <td v-if="player.cap !== 'E'" class="px-4 py-2 text-center">
                    <button
                      type="button"
                      :class="getEquipmentButtonClass(player.helmet_status)"
                      @click="updatePlayer(player.player_id, 'helmet_status', player.helmet_status)"
                    >
                      <UIcon :name="getEquipmentIcon(player.helmet_status)" class="h-5 w-5" />
                    </button>
                  </td>
                  <td v-if="player.cap !== 'E'" class="px-4 py-2 text-center">
                    <button
                      type="button"
                      :class="getPaddleButtonClass(player.paddle_count)"
                      @click="updatePlayer(player.player_id, 'paddle_count', player.paddle_count)"
                    >
                      <b>{{ player.paddle_count || 0 }}</b>
                    </button>
                  </td>
                  <td v-if="player.cap !== 'E'" class="px-4 py-2 text-center">
                    <button type="button" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                      <UIcon name="i-heroicons-printer" class="h-4 w-4" />
                    </button>
                  </td>
                  <td v-if="player.cap === 'E'" colspan="5" class="px-4 py-2"></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between items-center mt-4">
            <div>
              <label class="text-sm font-medium text-gray-700 mb-2 block">
                <i>{{ t('Scrutineering.Issues') }}:</i>
              </label>
              <div class="flex space-x-2">
                <button class="px-3 py-1 text-sm text-white bg-red-600 rounded flex items-center" disabled>
                  <UIcon name="i-heroicons-exclamation-circle" class="h-4 w-4 mr-1" />
                  {{ t('Scrutineering.Cosmetic') }}
                </button>
                <button class="px-3 py-1 text-sm text-white bg-red-600 rounded flex items-center" disabled>
                  <UIcon name="i-heroicons-exclamation-triangle" class="h-4 w-4 mr-1" />
                  {{ t('Scrutineering.Safety') }}
                </button>
                <button class="px-3 py-1 text-sm text-white bg-red-600 rounded flex items-center" disabled>
                  <UIcon name="i-heroicons-shield-exclamation" class="h-4 w-4 mr-1" />
                  {{ t('Scrutineering.Technical') }}
                </button>
              </div>
            </div>
            <button type="button" class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 flex items-center">
              <UIcon name="i-heroicons-printer" class="h-5 w-5 mr-2" />
              {{ t('Scrutineering.PrintAll') }}
            </button>
          </div>
        </div>
      </div>

      <div v-else class="p-4">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4" role="alert">
          <div class="flex justify-between items-center">
            <p class="font-bold text-yellow-800">
              {{ t('Scrutineering.ChangeEvent') }}
            </p>
            <button
              type="button"
              class="px-4 py-2 text-sm bg-yellow-400 text-yellow-900 rounded hover:bg-yellow-500 flex items-center"
              @click="navigateTo('/')"
            >
              <UIcon name="i-heroicons-arrow-left" class="h-4 w-4 mr-1" />
              {{ t('nav.ChangeEvent') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useScrutineering } from '~/composables/useScrutineering'
import { useUser } from '~/composables/useUser'
import { useStatus } from '~/composables/useStatus'
import { usePrefs } from '~/composables/usePrefs'
import TeamSelector from '~/components/TeamSelector.vue'

const { t } = useI18n()
const { user, getUser } = useUser()
const { authorized, checkAuthorized } = useStatus()
const { prefs, getPrefs } = usePrefs()
const { players, loadPlayers, updatePlayer } = useScrutineering()

onMounted(async () => {
  await getUser()
  if (!user.value) {
    navigateTo('/login')
    return
  }
  await checkAuthorized()
  await getPrefs()
  if (prefs.value?.scr_team_id) {
    loadPlayers()
  }
})

const getEquipmentButtonClass = (status) => {
  const baseClass = 'inline-flex items-center justify-center px-3 py-1 text-sm rounded'
  if (status === 1) return `${baseClass} text-white bg-green-600 hover:bg-green-700`
  if (status > 1) return `${baseClass} text-white bg-red-600 hover:bg-red-700`
  return `${baseClass} text-gray-700 bg-gray-200 hover:bg-gray-300`
}

const getEquipmentIcon = (status) => {
  if (status < 1) return 'i-heroicons-square-2-stack'
  if (status === 1) return 'i-heroicons-check-badge'
  if (status === 2) return 'i-heroicons-exclamation-circle'
  if (status === 3) return 'i-heroicons-exclamation-triangle'
  if (status === 4) return 'i-heroicons-shield-exclamation'
  return 'i-heroicons-square-2-stack'
}

const getPaddleButtonClass = (count) => {
  const baseClass = 'inline-flex items-center justify-center px-3 py-1 text-sm rounded'
  if (count > 0) return `${baseClass} text-white bg-green-600 hover:bg-green-700`
  return `${baseClass} text-gray-700 bg-gray-200 hover:bg-gray-300`
}
</script>
