<template>
  <div class="text-center">
    <div class="d-flex justify-content-center">
      <div>
        <div class="input-group text-nowrap h1">
          <div
            id="chrono"
            class="btn badge lcd bg-warning text-dark text-center"
            data-bs-toggle="modal"
            data-bs-target="#timerModal"
          >
            {{ timer }}
          </div>
          <div
            class="btn badge bg-sm bg-secondary"
            data-bs-toggle="modal"
            data-bs-target="#periodModal"
          >
            OVT
          </div>
        </div>
      </div>
    </div>
    <div class="btn-group-sm text-nowrap">
      <span class="btn btn-outline-secondary bi bi-skip-backward-fill" />
      <span
        class="btn btn-outline-secondary bi bi-play-fill"
        style="width: 6em;"
      />
      <!-- <span class="btn btn-outline-secondary bi bi-gear-fill" /> -->
    </div>
  </div>

  <!-- Modals -->
  <teleport to="body">
    <div
      class="modal fade"
      id="timerModal"
      tabindex="-1"
      aria-labelledby="timerModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="timerModalLabel">Timer Modal</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            ...
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
    <div
      class="modal fade"
      id="periodModal"
      tabindex="-1"
      aria-labelledby="periodModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="periodModalLabel">Period Modal</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            ...
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
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
