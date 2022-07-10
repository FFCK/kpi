<template>
  <!-- <div :id="styleZone" v-show="['full', 'only'].includes(mode)"
    :class="{ 'container-fluid': true, 'animate__animated': ['full'].includes(mode), 'animate__fadeInDown': ['full'].includes(mode) }"> -->
  <div
    :id="styleZone"
    :class="{
      'container-fluid': true,
      'animate__animated': true,
      'animate__fadeInDown': true,
      'static': mode === 'static'
      }">
    <div id="bandeau_score" class="live_ws">
      <div id="match_horloge" :class="{'red': !matchHorlogeStarted}">{{ matchHorloge }}</div>
      <div id="match_periode">{{ matchPeriode }}</div>
      <div id="match_possession">{{ matchPossession }}</div>

      <div id="pen1" :class="{ 'club': this.zone === 'club'}"><i v-for="i in pen1 || 0" :key="i" class="bi bi-circle-fill text-primary" /></div>
      <div id="equipe1">{{ teamName(gameData.equipe1.nom) }}</div>
      <div id="equipe2">{{ teamName(gameData.equipe2.nom) }}</div>
      <div id="pen2" :class="{ 'club': this.zone === 'club'}"><i v-for="i in pen2 || 0" :key="i" class="bi bi-circle-fill text-primary" /></div>

      <div id="nation1" v-html="logo48(gameData.equipe1.club)"></div>
      <div id="nation2" v-html="logo48(gameData.equipe2.club)"></div>

      <div id="score1">{{ score1 }}</div>
      <div id="score_separation">-</div>
      <div id="score2">{{ score2 }}</div>
    </div>
  </div>

  <!-- <div id="categorie" v-show="['full', 'only'].includes(mode)" class="animate__animated animate__fadeInUp">{{ gameData.categ }}</div> -->
  <div
    id="categorie"
    :class="{
      'animate__animated': true,
      'animate__fadeInUp': true,
      'static': mode === 'static'
      }">
    {{ gameData.categ }}
  </div>

  <!-- <div id="bandeau_goal" class="ban_goal_card_2 animate__animated" v-show="['full', 'events', 'static'].includes(mode)"> -->
  <div id="bandeau_goal" class="ban_goal_card_2 animate__animated live_ws">
    <div id="goal_card"><img id="goal_card_img" src="" alt=""></div>
    <div id="banner_goal_card" class="text-start">
      <div id="match_event_line2" class="banner_line text-start"></div>
      <div id="match_event_line1" class="banner_line text-start"></div>
      <div id="match_player"><img src="/img/KIP/players/none.png" alt=""></div>
    </div>
  </div>
</template>

<script>
import gameMixin from '@/mixins/gameMixin'

export default {
  name: 'Score',
  mixins: [gameMixin],
  props: {
    zone: {
      type: String,
      required: true
    },
    mode: {
      type: String,
      required: true
    },
    gameData: {
      type: Object,
      default: null
    }
  },
  computed: {
    styleZone () {
      return (this.zone === 'club') ? 'ban_score_club' : 'ban_score'
    }
  },
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      matchHorloge: '',
      matchHorlogeStarted: false,
      matchPeriode: '',
      matchPossession: '',
      score1: 0,
      score2: 0,
      pen1: 0,
      pen2: 0
    }
  },
  methods: {
  },
  async mounted () {
    this.fetchNetwork()
    this.fetchScore(this.gameData.id_match)

    // this.intervalScore = setInterval(this.fetchScore, process.env.VUE_APP_INTERVAL_SCORE || 2000, this.gameData.id_match)
  },
  beforeUnmount () {
    clearInterval(this.intervalScore)
  }

}
</script>

<style scoped>
.red {
  color: red !important;
}
</style>
