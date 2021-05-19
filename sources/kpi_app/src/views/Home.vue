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

      <div class="my-2 mx-auto">
        <button class="btn btn-secondary" @click="ajaxTest">Test API</button>
        <div>{{ content }}</div>
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
import { api } from '@/services/api'
import { mixin } from '@/services/mixins'

export default {
  name: 'Home',
  mixins: [mixin],
  components: {
    Login
  },
  computed: {
    user () {
      return User.query().first()
    }
  },
  data () {
    return {
      content: ''
    }
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    },
    async ajaxTest () {
      await api.get('/staff/test')
        .then((response) => {
          console.log(response.data)
          if (response.data === 'KO') {
            this.logOut()
          }
        }).catch((error) => {
          // Erreur dans la réponse ?
          if (error.response) {
            if (error.response.status === 401) {
              this.message = this.$t('Login.UnauthorizedMsg')
              console.log('Unauthorized')
            }
          // Erreur dans la requête ?
          } else if (error.request) {
            this.message = this.$t('Login.ErrorMsg')
            console.log('ErrorMsg')
          }
        })
    }
  }
}
</script>
