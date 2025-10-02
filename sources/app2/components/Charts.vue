<template>
  <article class="mb-5">
    <div v-for="category in chartData" :key="category[0]">
      <div class="bg-gray-800 text-white px-3 py-2">
        {{ category.libelle || category.code }}
      </div>
      <div
        class="container-fluid"
        v-if="category.type === 'CHPT' && category.status !== 'ATT'"
      >
        <ChartChptRanking
          :chart-ranking="category.ranking"
          :chart-status="category.status"
          :show-flags="showFlags"
        />
      </div>
      <div v-else>
        <!-- Final Ranking - Centered with optimal width -->
        <div
          v-if="category.type === 'CP' && category.status === 'END'"
          class="flex justify-center p-4"
        >
          <div class="w-full max-w-2xl">
            <ChartCpRanking
              :chart-ranking="category.ranking"
              :chart-status="category.status"
              :show-flags="showFlags"
              :competition-type="category.type"
            />
          </div>
        </div>

        <!-- Rounds and Phases -->
        <div
          class="gap-4 p-4"
          style="display: grid !important; grid-template-columns: repeat(1, 1fr); gap: 1rem;"
          :style="roundsGridStyle"
        >
          <article
            v-for="(round, index) in category.rounds"
            :key="index"
            class="flex flex-col justify-center items-stretch bg-gray-50 p-4 rounded-lg border"
          >
            <div
              v-for="(phase, index2) in objectReorder(round.phases)"
              :key="index2"
              class="mb-4"
            >
              <h6 class="font-semibold text-gray-700 mb-1 text-center">{{ phase.libelle }}</h6>
              <ChartGroup
                v-if="category.type === 'CP' && phase.type === 'C'"
                :chart-round="index"
                :chart-team-list="category.ranking"
                :chart-teams="phase.teams"
                :chart-team-count="+phase.t_count"
                :chart-games="phase.games"
                :chart-group="phase.libelle"
                :competition-type="category.type"
                :phase-type="phase.type"
              />
              <ChartGame
                v-if="category.type === 'CP' && phase.type === 'E'"
                :chart-games="phase.games"
              />
            </div>
          </article>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import ChartGroup from './ChartGroup.vue'
import ChartGame from './ChartGame.vue'
import ChartChptRanking from './ChartChptRanking.vue'
import ChartCpRanking from './ChartCpRanking.vue'

const props = defineProps({
  chartData: {
    type: Object,
    default: null
  },
  showFlags: {
    type: Boolean,
    default: true
  }
})

const screenWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1200)

const updateScreenWidth = () => {
  screenWidth.value = window.innerWidth
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateScreenWidth)
    updateScreenWidth()
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateScreenWidth)
  }
})

const roundsGridStyle = computed(() => {
  let columns = 1

  if (screenWidth.value >= 1280) { // xl
    columns = 4
  } else if (screenWidth.value >= 1024) { // lg
    columns = 3
  } else if (screenWidth.value >= 768) { // md
    columns = 2
  }

  return {
    'grid-template-columns': `repeat(${columns}, 1fr)`
  }
})

const objectReorder = (object) => {
  const ordered = []
  let key = ''
  for (key in object) {
    ordered[ordered.length] = object[key]
  }
  return ordered
}
</script>

<style scoped>
.content-table {
  margin-top: 52px;
}
</style>