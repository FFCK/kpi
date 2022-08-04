<template>
  <div class="container-fluid">
    <p><i>Version: {{ version }}</i></p>
  </div>
</template>

<script>
import routeMixin from '@/mixins/routeMixin'
import userMixin from '@/mixins/userMixin'
import { WsInit, WsBroadcastClose, WsFlowClose } from '@/network/wsGames'

export default {
  name: 'Home',
  mixins: [routeMixin, userMixin],
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
