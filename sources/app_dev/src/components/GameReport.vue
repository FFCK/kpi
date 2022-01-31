<template>
  <div class="row text-nowrap g-1">
    <div
      class="col d-flex align-items-center justify-content-center h1 rounded"
      :style="colorA"
    >
      <span
        class="badge bg-light text-dark text-nowrap lcd border border-dark"
        style="opacity: .8"
        >5</span
      >
    </div>
    <div class="col-7">
      <report-timer />
    </div>
    <div
      class="col d-flex align-items-center justify-content-center h1 rounded"
      :style="colorB"
    >
      <span
        class="badge bg-light text-dark text-nowrap lcd border border-dark"
        style="opacity: .8"
        >2</span
      >
    </div>
  </div>
  <div class="row pt-1 g-1">
    <div class="col-6 d-grid gap-1">
      <div class="badge bg-light text-dark" style="font-size: 3.5vw;">
        <span class="float-start badge p-2" :style="colorA"
          ><span class="visually-hidden">Team color</span></span
        >
        {{ currentGame.t_a_label }}
      </div>
    </div>
    <div class="col-6 d-grid gap-1">
      <div class="badge bg-light text-dark" style="font-size: 3.5vw;">
        {{ currentGame.t_b_label }}
        <span class="float-end badge p-2" :style="colorB"
          ><span class="visually-hidden">Team color</span></span
        >
      </div>
    </div>

    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-success">Goal</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-warning">Fault</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-warning">Fault</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-success">Goal</div>
    </div>

    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-secondary">Shot</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-secondary">Kick</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-secondary">Kick</div>
    </div>
    <div class="col-3 d-grid gap-1">
      <div class="btn btn-lg btn-secondary">Shot</div>
    </div>
  </div>
</template>

<script>
import ReportTimer from '@/components/ReportTimer'

export default {
  components: { ReportTimer },
  name: 'GameReport',
  props: {
    currentGame: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      colorA:
        'background: linear-gradient(to bottom right, ' +
        this.currentGame.t_a_color1 +
        ' 50%, ' +
        this.currentGame.t_a_color2 +
        ' 50%);',
      colorB:
        'background: linear-gradient(to bottom right, ' +
        this.currentGame.t_b_color1 +
        ' 50%, ' +
        this.currentGame.t_b_color2 +
        ' 50%);'
    }
  },
  mounted () {},
  methods: {}
}
</script>

<style>
</style>
