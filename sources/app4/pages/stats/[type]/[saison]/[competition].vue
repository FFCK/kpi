<script setup lang="ts">
/**
 * Dynamic stats route: /stats/:type/:saison/:competition
 * Pre-fills the stats store and redirects to /stats
 */
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const statsStore = useStatsStore()
const filtersStore = useFiltersStore()

onMounted(() => {
  const type = route.params.type as string
  const saison = route.params.saison as string
  const competition = route.params.competition as string

  // Set params in both stores so the stats page loads with these values
  statsStore.setParams({
    statType: type,
    season: saison,
    competitions: [competition]
  })
  filtersStore.setSeasonAndCompetition(saison, competition)

  // Redirect to stats page
  router.replace('/stats')
})
</script>

<template>
  <div class="flex items-center justify-center py-12">
    <UIcon name="heroicons:arrow-path" class="w-8 h-8 animate-spin text-gray-400" />
  </div>
</template>
