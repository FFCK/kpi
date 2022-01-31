<template>
  <div class="text-center">
    <div class="d-flex justify-content-center">
      <div>
        <div class="input-group text-nowrap h1">
          <!-- <span class="input-group-text bi bi-dash-circle" /> -->
          <!-- <span class="input-group-text bi bi-dash-circle-dotted" /> -->
          <div
            id="chrono"
            class="badge lcd bg-warning text-dark text-center border"
          >
            {{ timer }}
          </div>
          <!-- <span class="input-group-text bi bi-plus-circle-dotted" /> -->
          <!-- <span class="input-group-text bi bi-plus-circle" /> -->
        </div>
      </div>
    </div>
    <div class="btn-group-sm text-nowrap">
      <span class="btn btn-outline-secondary bi bi-skip-backward-fill" />
      <span
        class="btn btn-outline-secondary bi bi-play-fill"
        style="width: 5em;"
      />
      <!-- <span class="btn btn-outline-secondary bi bi-pause-fill" /> -->
      <span class="btn btn-outline-secondary bi bi-gear-fill" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReportTimer',
  props: {
    timer: {
      type: String,
      default: '10:00'
    },
    status: {
      type: String,
      default: null
    }
  },
  data () {
    return {}
  },
  mounted () {},
  methods: {}
}
</script>

<style scoped>
</style>
