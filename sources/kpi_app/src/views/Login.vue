<template>
  <div>
    <title-component
      :text="user ? $t('nav.MyAccount') : $t('Login.Authentication')"
    />

    <div v-if="user">
      {{ $t("Login.Welcome") }} {{ user.firstname }} {{ user.name }}
      <button
        class="btn btn-sm btn-btn-warning"
        :title="$t('Login.Logout')"
        @click="logOut"
      >
        <span class="bi bi-box-arrow-right" />
      </button>
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
import axios from 'axios'
import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import TitleComponent from '@/components/design/Title'
import { logoutMixin } from '@/services/mixins'
import Errors from '@/store/models/Errors'

export default {
  name: 'Login',
  components: {
    TitleComponent
  },
  mixins: [logoutMixin],
  data () {
    return {
      input: {
        login: '',
        password: ''
      },
      message: '',
      errors: {}
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
      this.errors = await Errors.find(1)
      if (this.errors.offline) {
        console.log('Offline process...')
      } else {
        this.message = ''
        if (this.input.login !== '' && this.input.password !== '') {
          // Création du token d'authentification
          const authToken = Buffer.from(`${this.input.login}:${this.input.password}`, 'utf8').toString('base64')
          this.errors = await Errors.find(1)
          // Requête API
          await axios.post('/login', {}, {
            baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087/api',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              Authorization: `Basic ${authToken}`
            }
          })
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
    }
  }
}
</script>
