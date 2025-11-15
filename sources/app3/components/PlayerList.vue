<template>
  <div>
    <div class="flex justify-between items-center mb-3">
      <h4 class="font-semibold">{{ t('players.players') }}</h4>
      <button
        v-if="!locked"
        @click="showAddPlayer = !showAddPlayer"
        class="btn-icon text-sm"
      >
        {{ showAddPlayer ? '−' : '+' }}
      </button>
    </div>

    <!-- Add Player Form -->
    <div v-if="showAddPlayer && !locked" class="mb-4 p-3 bg-gray-700 rounded-lg">
      <form @submit.prevent="addPlayer" class="space-y-2">
        <input
          v-model.number="newPlayer.number"
          type="number"
          :placeholder="t('players.number')"
          class="input-text text-sm"
          min="0"
          max="99"
          required
        />

        <input
          v-model="newPlayer.firstName"
          type="text"
          :placeholder="t('players.firstName')"
          class="input-text text-sm"
          required
        />

        <input
          v-model="newPlayer.lastName"
          type="text"
          :placeholder="t('players.lastName')"
          class="input-text text-sm"
          required
        />

        <select v-model="newPlayer.status" class="input-text text-sm">
          <option value="J">{{ t('players.player') }}</option>
          <option value="C">{{ t('players.captain') }}</option>
          <option value="E">{{ t('players.coach') }}</option>
        </select>

        <div class="flex gap-2">
          <button type="submit" class="btn-success text-sm flex-1">
            {{ t('players.addPlayer') }}
          </button>
          <button type="button" @click="showAddPlayer = false" class="btn-secondary text-sm">
            {{ t('common.cancel') }}
          </button>
        </div>

        <div v-if="error" class="text-xs text-red-400">{{ error }}</div>
      </form>
    </div>

    <!-- Players List -->
    <div class="space-y-1">
      <!-- Regular Players -->
      <button
        v-for="player in players"
        :key="player.id"
        @click="!locked && $emit('playerClick', {
          team,
          playerId: player.id,
          number: player.number,
          name: `${player.firstName} ${player.lastName}`
        })"
        :class="[
          'w-full text-left p-2 rounded-lg transition-colors',
          locked ? 'cursor-default' : 'hover:bg-gray-700 cursor-pointer'
        ]"
      >
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span class="font-bold text-lg w-8">{{ player.number }}</span>
            <span>{{ player.lastName }} {{ player.firstName.charAt(0) }}.</span>
            <span v-if="player.status === 'C'" class="text-xs bg-yellow-600 px-1 rounded">
              {{ t('players.captain') }}
            </span>
          </div>

          <button
            v-if="!locked"
            @click.stop="removePlayer(player.id)"
            class="text-red-400 hover:text-red-300"
          >
            ×
          </button>
        </div>
      </button>

      <!-- Coaches -->
      <div v-if="coaches.length > 0" class="pt-2 border-t border-gray-700">
        <div
          v-for="coach in coaches"
          :key="coach.id"
          class="p-2 rounded-lg bg-gray-700 mb-1"
        >
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <span class="text-sm">{{ coach.lastName }} {{ coach.firstName.charAt(0) }}.</span>
              <span class="text-xs bg-blue-600 px-1 rounded">{{ t('players.coach') }}</span>
            </div>

            <button
              v-if="!locked"
              @click.stop="removePlayer(coach.id)"
              class="text-red-400 hover:text-red-300"
            >
              ×
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useMatchStore } from '~/stores/matchStore'

const props = defineProps<{
  team: 'A' | 'B'
  locked: boolean
}>()

const emit = defineEmits<{
  playerClick: [data: any]
}>()

const { t } = useI18n()
const matchStore = useMatchStore()

const showAddPlayer = ref(false)
const error = ref('')

const newPlayer = ref({
  number: 0,
  firstName: '',
  lastName: '',
  status: 'J' as 'J' | 'C' | 'E'
})

const players = computed(() => {
  return props.team === 'A' ? matchStore.teamAPlayers : matchStore.teamBPlayers
})

const coaches = computed(() => {
  return props.team === 'A' ? matchStore.teamACoaches : matchStore.teamBCoaches
})

const addPlayer = () => {
  error.value = ''

  try {
    matchStore.addPlayer(props.team, {
      ...newPlayer.value,
      firstName: newPlayer.value.firstName.trim(),
      lastName: newPlayer.value.lastName.trim()
    })

    // Reset form
    newPlayer.value = {
      number: 0,
      firstName: '',
      lastName: '',
      status: 'J'
    }

    showAddPlayer.value = false
  } catch (e: any) {
    error.value = e.message
  }
}

const removePlayer = (playerId: string) => {
  if (props.locked) return
  matchStore.removePlayer(props.team, playerId)
}
</script>
