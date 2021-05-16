<template>
  <div class="container-sm">

    <login />

    <div class="row" v-if="user">
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

    <div class="my-1">
      <img alt="Vue logo" src="../assets/logo.png" width="100" height="100">
    </div>

  </div>
</template>

<script>
import Login from '@/components/Login'
import User from '@/store/models/User'

export default {
  name: 'Home',
  components: {
    Login
  },
  computed: {
    user () {
      return User.query().first()
    }
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
