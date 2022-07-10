<template>
  <component :is="display" v-if="game" :gameData="game" :zone="zone" :mode="mode" :key="game.id_match"></component>
</template>

<script>
import routeMixin from '@/mixins/routeMixin'
import gameMixin from '@/mixins/gameMixin'
import Main from '@/components/display/Main.vue'
import Match from '@/components/display/Match.vue'
import Score from '@/components/display/Score.vue'

export default {
  name: 'Live',
  mixins: [routeMixin, gameMixin],
  components: { Main, Match, Score },
  created () {
    this.$watch(() => this.$route.fullPath, (tofullPath, previousfullPath) => {
      this.event = this.$route.params.event
      this.pitch = this.$route.params.pitch
      this.options = this.$route.params.options
      this.checkOptions()
    })
  },
  mounted () {
    this.checkOptions()
    this.fetchGameRotate()
  },
  beforeUnmount () {
    clearInterval(this.intervalGame)
  }
}
</script>

<style scoped lang="scss">

</style>
