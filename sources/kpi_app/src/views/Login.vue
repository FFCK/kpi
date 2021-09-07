<template>
  <div>
    <div v-if="user">
      <div class="text-center my-3">
        <span class="btn btn-secondary"
          >{{ user.firstname }} {{ user.name }}</span
        >

        <button
          class="btn btn-sm btn-btn-warning"
          :title="$t('Login.Logout')"
          @click="logOut"
        >
          <span class="bi bi-box-arrow-right" />
        </button>
      </div>

      <div class="row justify-content-around my-5">
        <button
          class="btn btn-outline-dark btn-lg col-3"
          @click="changePage('GameReport')"
        >
          {{ $t("nav.GameReport") }}
        </button>
        <button
          class="btn btn-outline-dark btn-lg col-3"
          @click="changePage('StatReport')"
        >
          {{ $t("nav.StatReport") }}
        </button>
        <button
          class="btn btn-outline-dark btn-lg col-3"
          @click="changePage('Scrutineering')"
        >
          {{ $t("nav.Scrutineering") }}
        </button>
      </div>
    </div>

    <div
      v-if="message"
      class="alert alert-danger alert-dismissible fade show"
      role="alert"
    >
      {{ message }}
      <button
        type="button"
        class="close"
        data-dismiss="alert"
        aria-label="Close"
        @click="dataDismiss"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <div v-if="!user" class="row justify-content-center mb-4">
      <form class="col-6" @submit.prevent="formSubmit">
        <div class="form-group">
          <label>{{ $t("Login.Login") }}</label>
          <input
            v-model.number="input.login"
            type="tel"
            class="form-control"
            aria-describedby="loginHelp"
          />
          <small id="loginHelp" class="form-text text-muted">{{
            $t("Login.LoginHelp")
          }}</small>
        </div>
        <div class="form-group">
          <label>{{ $t("Login.Password") }}</label>
          <input
            v-model="input.password"
            type="password"
            class="form-control"
          />
        </div>
        <button type="submit" class="btn btn-primary">
          {{ $t("Login.Submit") }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import privateApi from '@/network/privateApi'
import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import { logoutMixin } from '@/mixins/mixins'
import Status from '@/store/models/Status'

export default {
  name: 'Login',
  mixins: [logoutMixin],
  data () {
    return {
      input: {
        login: '',
        password: ''
      },
      message: '',
      status: {}
    }
  },
  computed: {
    user () {
      return User.query().first()
    }
  },
  created () {
    this.getUser()
  },
  methods: {
    async getUser () {
      // User absent du store ?
      if (User.query().count() === 0) {
        // Chargement de user depuis IndexedDB
        const result = await idbs.dbGetAll('user')
        // Un seul résultat ?
        if (result.length === 1) {
          // Ajout dans le store
          User.insertOrUpdate({
            data: result
          })
        } else {
          // Formulaire de login
          this.logOut()
          this.input.login = ''
          this.input.password = ''
        }
      } else {
        // User déjà présent dans le store
      }
    },
    async formSubmit () {
      this.status = await Status.find(1)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        this.message = ''
        if (this.input.login !== '' && this.input.password !== '') {
          // Création du token d'authentification
          const authToken = Buffer.from(`${this.input.login}:${this.input.password}`, 'utf8').toString('base64')
          // Requête API
          await privateApi.getToken(authToken)
            .then((response) => {
              this.message = ''
              // Insertion dans le store
              User.insertOrUpdate({
                data: response.data.user
              })
              // Insertion dans IndexedDB
              idbs.dbPut('user', response.data.user)
              // Masquage du formulaire
              this.showLoginForm = false
              // Enregistrement cookie
              const date = new Date()
              date.setTime(date.getTime() + (10 * 24 * 60 * 60 * 1000))
              const expires = 'expires=' + date.toUTCString()
              document.cookie = 'kpi_app=' + response.data.user.token + '; ' + expires + '; path=/'
            }).catch((error) => {
              // Erreur dans la réponse ?
              if (error.response) {
                if (error.response.status === 401) {
                  this.message = this.$t('Login.UnauthorizedMsg')
                }
              // Erreur dans la requête ?
              } else if (error.request) {
                this.message = this.$t('Login.ErrorMsg')
                console.log('Offline !')
              } else if (error.message === 'Network Error') {
                console.log('Offline !')
              }
            })
        } else {
          // Formulaire vide
          this.message = this.$t('Login.EmptyMsg')
        }
      }
    },
    dataDismiss () {
      this.message = ''
    },
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
