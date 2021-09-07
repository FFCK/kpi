<template>
  <div class="my-5">
    <div v-for="category in chartData" :key="category[0]">
      <div class="bg-secondary text-light ps-3">
        {{ category.libelle || category.code }}
      </div>
      <div class="container-fluid flex">
        <div class="row">
          <article
            v-for="(round, index) in category.rounds"
            :key="index"
            class="col-md d-flex flex-column justify-content-center align-items-stretch bg-light m-1"
          >
            <div
              v-for="(phase, index2) in objectReorder(round.phases)"
              :key="index2"
              class="m-1"
            >
              <h6 class="text-center">{{ phase.libelle }}</h6>
              <chart-championship
                v-if="category.type === 'CHPT' && phase.type === 'C'"
                :chart-games="phase.games"
              />
              <chart-group
                v-if="category.type === 'CP' && phase.type === 'C'"
                :chart-teams="phase.teams"
                :chart-team-count="+phase.t_count"
                :chart-games="phase.games"
              />
              <chart-game
                v-if="category.type === 'CP' && phase.type === 'E'"
                :chart-games="phase.games"
              />
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gamesDisplayMixin } from '@/mixins/mixins'
import ChartChampionship from './ChartChampionship.vue'
import ChartGroup from './ChartGroup.vue'
import ChartGame from './ChartGame.vue'
export default {
  name: 'Charts',
  components: {
    ChartChampionship,
    ChartGroup,
    ChartGame
  },
  mixins: [gamesDisplayMixin],
  props: {
    chartData: {
      type: Object,
      default: null
    }
  },
  methods: {
    objectReorder (object) {
      const ordered = []
      let key = ''
      for (key in object) {
        ordered[ordered.length] = object[key]
      }
      return ordered
    }
  }
}
</script>

<style scoped>
.content-table {
  margin-top: 52px;
}
.table-sm {
  font-size: 13px;
}
</style>
