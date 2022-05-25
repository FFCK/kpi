<template>
  <div :id="styleZone" class="container-fluid animate__animated animate__fadeInDown">
    <div id="bandeau_score">
      <div id="match_horloge">10:00</div>
      <div id="match_periode"></div>

      <div id="equipe1">{{ teamName(gameData.equipe1.nom) }}</div>
      <div id="equipe2">{{ teamName(gameData.equipe2.nom) }}</div>

      <div id="nation1" v-html="logo48(gameData.equipe1.club)"></div>
      <div id="nation2" v-html="logo48(gameData.equipe2.club)"></div>

      <div id="score1"></div>
      <div id="score_separation">-</div>
      <div id="score2"></div>
    </div>
  </div>
  <div id="categorie" class="animate__animated animate__fadeInUp">{{ gameData.categ }}</div>
  <div id="bandeau_goal" class="ban_goal_card_2 animate__animated">
    <div id="goal_card"></div>
    <div id="banner_goal_card" class="text-start">
      <div id="match_event_line2" class="banner_line text-start"></div>
      <div id="match_event_line1" class="banner_line text-start"></div>
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
  methods: {
  },
  async mounted () {
    this.fetchScore(this.gameData.id_match)
    this.intervalScore = setInterval(this.fetchScore, process.env.VUE_APP_INTERVAL_SCORE || 2000, this.gameData.id_match)
  },
  beforeUnmount () {
    clearInterval(this.intervalScore)
  }

}
</script>

<style scoped>
</style>
