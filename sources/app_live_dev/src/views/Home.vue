<template>
  <div class="container-fluid">
    <p><i>BaseUrl: {{ baseUrl }}</i></p>
    <p><i>Version: {{ version }}</i></p>
    <p><a href="./#/114/1/score/fr">Live: Evt 114 pitch 1</a></p>
  </div>
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
