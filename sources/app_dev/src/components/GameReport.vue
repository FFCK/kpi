<template>
  <div>
    <!-- Nav -->
    <div class="local-filters mb-1">
      <div class="input-group input-group-sm">
        <div class="btn btn-outline-secondary" @click="$emit('hide')">
          <i class="bi bi-caret-left-square-fill" /> Back
        </div>
        <input
          type="text"
          class="btn form-control text-center"
          data-bs-toggle="offcanvas"
          href="#offcanvasParams"
          role="button"
          aria-controls="offcanvasParams"
          readonly
          :value="$t('GameReports.Game') + ' #' + currentGame.g_id"
        />
        <div
          class="btn btn-outline-secondary"
          data-bs-toggle="offcanvas"
          href="#offcanvasParams"
          role="button"
          aria-controls="offcanvasParams"
        >
          <i class="bi bi-gear-fill" />
        </div>
        <!-- <div class="btn btn-outline-secondary" @click="showGameParams">
          <i class="bi bi-caret-right-square-fill" />
        </div> -->
      </div>
    </div>

    <!-- Score & timer -->
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
      <!-- Teams -->
      <div class="col-6 d-grid gap-1">
        <div
          class="badge bg-light text-dark position-relative team-label"
          data-bs-toggle="offcanvas"
          href="#offcanvasTeamA"
          role="button"
          aria-controls="offcanvasTeamA"
        >
          <span
            class="position-absolute top-50 start-0 translate-middle-y bi bi-caret-right-fill"
          />
          <span v-html="teamNameResize(currentGame.t_a_label)" />
        </div>
      </div>
      <div class="col-6 d-grid gap-1">
        <div
          class="badge bg-light text-dark position-relative team-label"
          data-bs-toggle="offcanvas"
          href="#offcanvasTeamB"
          role="button"
          aria-controls="offcanvasTeamB"
        >
          <span v-html="teamNameResize(currentGame.t_b_label)" />
          <span
            class="position-absolute top-50 end-0 translate-middle-y bi bi-caret-left-fill"
          />
        </div>
      </div>

      <!-- Actions -->
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

      <div class="col-12 d-grid gap-1">
        <div
          class="btn btn-secondary"
          data-bs-toggle="offcanvas"
          href="#offcanvasEvents"
          role="button"
          aria-controls="offcanvasEvents"
        >
          Events
        </div>
      </div>
    </div>

    <!-- Team A -->
    <div
      class="offcanvas offcanvas-start col-8"
      tabindex="-1"
      id="offcanvasTeamA"
      aria-labelledby="offcanvasTeamALabel"
    >
      <div class="offcanvas-header" data-bs-dismiss="offcanvas">
        <h1 class="w-100">
          <div class="badge d-block position-relative" :style="colorA">
            <div
              class="badge bg-light text-dark text-nowrap border border-dark"
              style="opacity: .8"
              v-html="teamNameResize(currentGame.t_a_label)"
            />
            <span
              class="position-absolute top-50 end-0 translate-middle-y bi bi-caret-left-fill"
            />
          </div>
        </h1>
      </div>
      <div class="offcanvas-body" data-bs-dismiss="offcanvas">
        <div>
          <ul class="list-group">
            <li class="list-group-item">An item</li>
            <li class="list-group-item">A second item</li>
            <li class="list-group-item">A third item</li>
            <li class="list-group-item">A fourth item</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Team B -->
    <div
      class="offcanvas offcanvas-end col-8"
      tabindex="-1"
      id="offcanvasTeamB"
      aria-labelledby="offcanvasTeamBLabel"
    >
      <div class="offcanvas-header" data-bs-dismiss="offcanvas">
        <h1 class="w-100">
          <div class="badge d-block position-relative" :style="colorB">
            <div
              class="badge bg-light text-dark text-nowrap border border-dark"
              style="opacity: .8"
              v-html="teamNameResize(currentGame.t_b_label)"
            />
            <span
              class="position-absolute top-50 start-0 translate-middle-y bi bi-caret-right-fill"
            />
          </div>
        </h1>
      </div>
      <div class="offcanvas-body" data-bs-dismiss="offcanvas">
        <div>
          <ul class="list-group">
            <li class="list-group-item">An item</li>
            <li class="list-group-item">A second item</li>
            <li class="list-group-item">A third item</li>
            <li class="list-group-item">A fourth item</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Game params -->
    <div
      class="offcanvas offcanvas-top vh-75"
      tabindex="-1"
      id="offcanvasParams"
      aria-labelledby="offcanvasParamsLabel"
    >
      <div class="offcanvas-header" data-bs-dismiss="offcanvas">
        <div class="mb-1">
          <div class="input-group input-group-sm">
            <div class="btn btn-outline-secondary" @click="$emit('hide')">
              <i class="bi bi-caret-left-square-fill" /> Back
            </div>
            <input
              type="text"
              class="btn form-control text-center"
              data-bs-toggle="offcanvas"
              href="#offcanvasParams"
              role="button"
              aria-controls="offcanvasParams"
              readonly
              :value="$t('GameReports.Game') + ' #' + currentGame.g_id"
            />
            <div
              class="btn btn-outline-secondary"
              data-bs-toggle="offcanvas"
              href="#offcanvasParams"
              role="button"
              aria-controls="offcanvasParams"
            >
              <i class="bi bi-gear-fill" />
            </div>
          </div>
        </div>
      </div>
      <div class="offcanvas-body" data-bs-dismiss="offcanvas">
        <div>
          <ul class="list-group">
            <li class="list-group-item">An item</li>
            <li class="list-group-item">A second item</li>
            <li class="list-group-item">A third item</li>
            <li class="list-group-item">A fourth item</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Game events -->
    <div
      class="offcanvas offcanvas-bottom"
      tabindex="-1"
      id="offcanvasEvents"
      aria-labelledby="offcanvasEventsLabel"
    >
      <div class="offcanvas-header" data-bs-dismiss="offcanvas">
        <h1 class="h-200b">
          <div class="badge d-block position-relative" :style="colorB">
            <div
              class="badge bg-light text-dark text-nowrap border border-dark"
              style="opacity: .8"
              v-html="teamNameResize(currentGame.t_b_label)"
            />
            <span
              class="position-absolute top-50 start-0 translate-middle-y bi bi-caret-right-fill"
            />
          </div>
        </h1>
      </div>
      <div class="offcanvas-body" data-bs-dismiss="offcanvas">
        <div>
          <ul class="list-group">
            <li class="list-group-item">An item</li>
            <li class="list-group-item">A second item</li>
            <li class="list-group-item">A third item</li>
            <li class="list-group-item">A fourth item</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
            <li class="list-group-item">And a fifth one</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ReportTimer from '@/components/ReportTimer'
import gameDisplayMixin from '@/mixins/gameDisplayMixin'

export default {
  components: { ReportTimer },
  mixins: [gameDisplayMixin],
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
  methods: {
    showGameParams () {
      alert('showGameParams')
    }
  }
}
</script>

<style>
.team-label {
  font-size: 3.5vw;
}
</style>
