<template>
  <div class="mb-5">
    <div v-for="category in chartData" :key="category[0]">
      <div class="bg-dark text-light">
        {{ category.libelle || category.code }}
      </div>
      <div class="container-fluid flex">
        <div class="row">
          <article
            v-for="(round, index) in category.rounds"
            :key="index"
            :class="{
              'col-md': true,
              'd-flex': true,
              'flex-column': round.type === 'C',
              'flex-column-reverse': round.type === 'E',
              'justify-content-center': true,
              'align-items-stretch': true,
              'bg-light': true,
              'm-1': true
            }"
          >
            <div
              class="m-1"
              v-for="(phase, index2) in round.phases"
              :key="index2"
            >
              <h6>{{ phase.libelle }}</h6>
              <chart-championship
                v-if="category.type === 'CHPT' && phase.type === 'C'"
                :chartGames="phase.games"
              />
              <chart-group
                v-if="category.type === 'CP' && phase.type === 'C'"
                :chartTeams="phase.teams"
                :chartTeamCount="+phase.t_count"
                :chartGames="phase.games"
              />
              <chart-game
                v-if="category.type === 'CP' && phase.type === 'E'"
                :chartGames="phase.games"
              />
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gamesDisplayMixin } from '@/services/mixins'
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
  methods: {}
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
