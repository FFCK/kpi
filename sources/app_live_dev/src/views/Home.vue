<template>
  <div class="container-fluid">
    <p><i>BaseUrl: {{ baseUrl }}</i></p>
    <p><i>Version: {{ version }}</i></p>
    <p><a href="/#/manager">WebSocket Manager</a></p>
    <p><a href="/#/114/1/score/fr">Live: Evt 114 pitch 1</a></p>
  </div>
  <!-- <div v-if="game">
    <p>{{ $t('test.Working_game') }} : #{{ gameId }} ({{ game.numero_ordre }}) - {{ game.equipe1.nom }} / {{ game.equipe2.nom }}</p>
  </div> -->
  <hr>
  <div id="message"></div>
  <div id="main">
    <h3 class="text-center">
      KP Score
      <span id="clock" class="badge bg-success">23h59.59.9</span>
    </h3>
    <div class="row text-center">
      <h1 id="score">
        <span id="goal_left">0</span> - <span id="goal_right">0</span>
      </h1>
    </div>
  </div>
  <div class="btn btn-success me-1" v-if="!wsLaunched" @click="init()">Start</div>
  <div class="btn btn-warning me-1" v-if="wsLaunched" @click="closeBroadcast()">Stop Broadcast</div>
  <div class="btn btn-danger" v-if="wsLaunched" @click="closeFlow()">Stop Flow</div>
  <i>BaseUrl: {{ baseUrl }}</i>
</template>

<script>
import routeMixin from '@/mixins/routeMixin'
import { WsInit, WsBroadcastClose, WsFlowClose } from '@/network/wsGames'

export default {
  name: 'Home',
  mixins: [routeMixin],
  data () {
    return {
      wsLaunched: false
    }
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  },
  methods: {
    init () {
      console.log('start')
      WsInit()
      this.wsLaunched = true
    },
    closeBroadcast () {
      console.log('stop')
      WsBroadcastClose()
    },
    closeFlow () {
      console.log('stop')
      WsFlowClose()
      this.wsLaunched = false
    }
  }
}
</script>

<style scoped lang="scss">

</style>
