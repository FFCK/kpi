<template>
  <div class="container-fluid pb-16">
    <AppSecondaryNav>
      <template #left>
        <NuxtLink :to="`/team/${teamId}`" class="p-2 rounded-md hover:bg-gray-100 cursor-pointer flex items-center gap-2">
          <UIcon name="i-heroicons-arrow-left" class="h-6 w-6" />
          <span class="hidden sm:inline">Retour à l'équipe</span>
        </NuxtLink>
      </template>
    </AppSecondaryNav>

    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">Statistiques de l'équipe</h1>
      <div v-if="loading" class="text-center text-gray-500">
        <p>Chargement des statistiques...</p>
      </div>
      <div v-if="error" class="p-4 text-center text-red-500">
        <p>{{ error }}</p>
      </div>
      <div v-if="stats && stats.length > 0" class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead class="bg-gray-800 text-white">
            <tr>
              <th class="py-2 px-4 border-b">Joueur</th>
              <th class="py-2 px-4 border-b">Buts</th>
              <th class="py-2 px-4 border-b">Tirs</th>
              <th class="py-2 px-4 border-b">Arrêts</th>
              <th class="py-2 px-4 border-b">Cartons</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="player in stats" :key="player.licence" class="hover:bg-gray-100">
              <td class="py-2 px-4 border-b">
                <div class="font-medium flex items-center">
                  <span class="text-sm text-gray-500 mr-2">#{{ player.number }}</span>
                  {{ player.firstname }} {{ player.name }}
                  <span v-if="player.captain === 'C'" class="ml-2 bg-black text-white text-xs font-bold w-4 h-4 flex items-center justify-center rounded-sm">C</span>
                </div>
              </td>
              <td class="py-2 px-4 border-b text-center">{{ player.goals }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.shots }}</td>
              <td class="py-2 px-4 border-b text-center">{{ player.saves }}</td>
              <td class="py-2 px-4 border-b text-center">
                <span v-if="player.green_cards > 0" class="inline-block bg-green-500 text-white w-6 h-6 rounded-md text-center leading-6 mr-1">{{ player.green_cards }}</span>
                <span v-if="player.yellow_cards > 0" class="inline-block bg-yellow-400 text-white w-6 h-6 rounded-md text-center leading-6 mr-1">{{ player.yellow_cards }}</span>
                <span v-if="player.red_cards > 0" class="inline-block bg-red-500 text-white w-6 h-6 rounded-md text-center leading-6">{{ player.red_cards }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="stats && stats.length === 0 && !loading" class="p-4 text-center text-gray-500">
        <p>Aucune statistique disponible pour cette équipe.</p>
      </div>
    </div>
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useApi } from '~/composables/useApi';

const route = useRoute();
const teamId = route.params.team;

const stats = ref(null);
const loading = ref(false);
const error = ref(null);

const { getApi } = useApi();

const fetchStats = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await getApi(`/team-stats/${teamId}`);
    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des statistiques');
    }
    stats.value = await response.json();
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
});
</script>