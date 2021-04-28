<template>
  <div class="container-sm">
    {{ $t("message") }}
    <div class="row">
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body">
            <h5 class="card-title">
              {{ $t("nav.Games") }}
            </h5>
            <p class="card-text">{{ $t("nav.Game_report") }}</p>
            <button class="btn btn-primary" @click="changePage('Game')">
              Go
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body">
            <h5 class="card-title">
              {{ $t("nav.Stats") }}
            </h5>
            <p class="card-text">{{ $t("nav.Stats_report") }}</p>
            <button class="btn btn-primary" @click="changePage('Stats')">
              Go
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mb-2">
          <div class="card-body">
            <h5 class="card-title">
              {{ $t("nav.Scrutineering") }}
            </h5>
            <p class="card-text">{{ $t("nav.Scrut_report") }}</p>
            <button class="btn btn-primary" @click="changePage('Scrutineering')">
              Go
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-1">
      <img alt="Vue logo" src="../assets/logo.png" width="100" height="100">
    </div>

  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Home',
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
