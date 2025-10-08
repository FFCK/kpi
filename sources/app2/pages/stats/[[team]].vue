<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #left>
        <NuxtLink :to="`/team/${teamId}`" class="p-2 rounded-md hover:bg-gray-100 cursor-pointer flex items-center gap-2">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
          <span class="hidden sm:inline">{{ t('Stats.BackToTeam') }}</span>
        </NuxtLink>
      </template>
    </AppSecondaryNav>

    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">{{ t('Stats.Title') }}</h1>
      <div v-if="loading" class="text-center text-gray-500">
        <p>{{ t('Stats.Loading') }}</p>
      </div>
      <div v-if="error" class="p-4 text-center text-red-500">
        <p>{{ error }}</p>
      </div>
      <div v-if="filteredStats && filteredStats.length > 0" class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="py-2 px-4 border-b">{{ t('Stats.Player') }}</th>
              <th class="py-2 px-4 border-b">{{ t('Stats.Goals') }}</th>
              <th class="py-2 px-4 border-b">
                <div class="inline-block bg-green-500 w-6 h-8 transform -rotate-12 rounded-sm"></div>
              </th>
              <th class="py-2 px-4 border-b">
                <div class="inline-block bg-yellow-400 w-6 h-8 transform -rotate-12 rounded-sm"></div>
              </th>
              <th class="py-2 px-4 border-b">
                <div class="inline-block bg-red-500 w-6 h-8 transform -rotate-12 rounded-sm"></div>
              </th>
              <th class="py-2 px-4 border-b">
                <div class="inline-block bg-red-500 w-6 h-8 transform -rotate-12 rounded-sm flex items-center justify-center text-white font-bold text-xs">E</div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="player in filteredStats" :key="player.licence" class="hover:bg-gray-100">
              <td class="py-2 px-4 border-b">
                <div class="font-medium flex items-center">
                  <span v-if="player.captain !== 'E'" class="text-sm text-gray-500 mr-2">#{{ player.number }}</span>
                  {{ player.firstname }} {{ player.name }}
                  <span v-if="player.captain === 'C'" class="ml-2 bg-black text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-sm">C</span>
                  <span v-if="player.captain === 'E'" class="ml-2 text-xs text-gray-500">({{ t('Stats.Coach') }})</span>
                </div>
              </td>
              <td class="py-2 px-4 border-b text-center">{{ player.goals > 0 ? player.goals : '' }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.green_cards > 0 ? player.green_cards : '' }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.captain !== 'E' && player.yellow_cards > 0 ? player.yellow_cards : '' }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.red_cards > 0 ? player.red_cards : '' }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.exclusions > 0 ? player.exclusions : '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredStats && filteredStats.length === 0 && !loading" class="p-4 text-center text-gray-500">
        <p>{{ t('Stats.NoStats') }}</p>
      </div>
    </div>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '~/composables/useApi';
import { usePreferenceStore } from '~/stores/preferenceStore';

const { t } = useI18n();
const route = useRoute();
const teamId = route.params.team;
const preferenceStore = usePreferenceStore();

const stats = ref(null);
const loading = ref(false);
const error = ref(null);

const { getApi } = useApi();

// Filter and sort stats
const filteredStats = computed(() => {
  if (!stats.value) return [];

  return stats.value
    // Exclude non-players (A = referees, X = inactive)
    .filter(player => player.captain !== 'A' && player.captain !== 'X')
    // Sort: players first by number, then coaches by number
    .sort((a, b) => {
      const aIsCoach = a.captain === 'E';
      const bIsCoach = b.captain === 'E';

      // If one is coach and other is player, player comes first
      if (aIsCoach && !bIsCoach) return 1;
      if (!aIsCoach && bIsCoach) return -1;

      // Both same type, sort by number
      return (a.number || 0) - (b.number || 0);
    });
});

const fetchStats = async () => {
  loading.value = true;
  error.value = null;
  try {
    const eventId = preferenceStore.preferences.lastEvent?.id;
    if (!eventId) {
      throw new Error(t('Stats.NoEventSelected'));
    }
    const response = await getApi(`/team-stats/${teamId}/${eventId}`);
    if (!response.ok) {
      throw new Error(t('Stats.Error'));
    }
    stats.value = await response.json();
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await preferenceStore.fetchItems();
  await fetchStats();
});
</script>