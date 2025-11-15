<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
          {{ t('app.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          {{ t('app.subtitle') }}
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Create New Match -->
        <div class="card">
          <h2 class="text-2xl font-semibold mb-4">{{ t('match.new') }}</h2>

          <form @submit.prevent="createNewMatch" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">{{ t('match.teamA') }}</label>
              <input
                v-model="newMatch.teamA"
                type="text"
                required
                class="input-text"
                :placeholder="t('match.teamName')"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">{{ t('match.teamB') }}</label>
              <input
                v-model="newMatch.teamB"
                type="text"
                required
                class="input-text"
                :placeholder="t('match.teamName')"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">{{ t('match.date') }}</label>
                <input
                  v-model="newMatch.date"
                  type="date"
                  required
                  class="input-text"
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">{{ t('match.time') }}</label>
                <input
                  v-model="newMatch.time"
                  type="time"
                  required
                  class="input-text"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">{{ t('match.field') }}</label>
              <input
                v-model="newMatch.field"
                type="text"
                class="input-text"
                placeholder="1"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">{{ t('match.type') }}</label>
              <div class="flex gap-4">
                <label class="flex items-center">
                  <input
                    v-model="newMatch.type"
                    type="radio"
                    value="C"
                    class="mr-2"
                  />
                  {{ t('match.typeClassement') }}
                </label>
                <label class="flex items-center">
                  <input
                    v-model="newMatch.type"
                    type="radio"
                    value="E"
                    class="mr-2"
                  />
                  {{ t('match.typeElimination') }}
                </label>
              </div>
            </div>

            <button type="submit" class="btn-primary w-full">
              {{ t('match.create') }}
            </button>
          </form>
        </div>

        <!-- Load Existing Match -->
        <div class="card">
          <h2 class="text-2xl font-semibold mb-4">{{ t('match.load') }}</h2>

          <form @submit.prevent="loadExistingMatch" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">{{ t('match.matchId') }}</label>
              <input
                v-model.number="loadMatchId"
                type="number"
                required
                class="input-text"
                placeholder="12345"
              />
            </div>

            <button type="submit" class="btn-primary w-full" :disabled="loading">
              <span v-if="loading">{{ t('common.loading') }}</span>
              <span v-else>{{ t('match.load') }}</span>
            </button>

            <div v-if="error" class="p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg text-sm">
              {{ error }}
            </div>
          </form>

          <!-- Recent Matches -->
          <div v-if="recentMatches.length > 0" class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Recent Matches</h3>
            <div class="space-y-2">
              <button
                v-for="match in recentMatches"
                :key="match.id"
                @click="loadRecentMatch(match)"
                class="w-full text-left p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <div class="font-medium">{{ match.teamA }} vs {{ match.teamB }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(match.date) }} - {{ match.time }}
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useMatchStore } from '~/stores/matchStore'
import db from '~/utils/db'

const { t } = useI18n()
const router = useRouter()
const matchStore = useMatchStore()

const newMatch = ref({
  teamA: '',
  teamB: '',
  date: new Date().toISOString().split('T')[0],
  time: new Date().toTimeString().split(' ')[0].substring(0, 5),
  field: '1',
  type: 'C' as 'C' | 'E'
})

const loadMatchId = ref<number | null>(null)
const loading = ref(false)
const error = ref('')
const recentMatches = ref<any[]>([])

onMounted(async () => {
  // Load recent matches from local DB
  try {
    const matches = await db.matches
      .orderBy('timestamp')
      .reverse()
      .limit(5)
      .toArray()
    recentMatches.value = matches
  } catch (e) {
    console.error('Error loading recent matches:', e)
  }
})

const createNewMatch = () => {
  matchStore.createMatch(newMatch.value)
  router.push('/match')
}

const loadExistingMatch = async () => {
  if (!loadMatchId.value) return

  loading.value = true
  error.value = ''

  try {
    await matchStore.loadMatch(loadMatchId.value)
    router.push('/match')
  } catch (e: any) {
    error.value = e.message || t('common.error')
  } finally {
    loading.value = false
  }
}

const loadRecentMatch = (match: any) => {
  matchStore.currentMatch = match
  router.push('/match')
}

const formatDate = (dateStr: string) => {
  try {
    return new Date(dateStr).toLocaleDateString()
  } catch {
    return dateStr
  }
}
</script>
