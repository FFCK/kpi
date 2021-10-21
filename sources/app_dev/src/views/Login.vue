<template>
  <div class="container-fluid mb-5">
    <i
      role="button"
      class="float-start bi bi-caret-left-square-fill me-2"
      @click="$router.push({ name: 'Home' })"
    />
    <div v-if="user">
      <div class="text-center my-3">
        <span class="btn btn-secondary"
          >{{ user.firstname }} {{ user.name }}</span
        >
        <br />
        <button
          class="btn btn-sm btn-warning mt-1"
          :title="$t('Login.Logout')"
          @click="logOut"
        >
          {{ $t("Login.Logout") }} <span class="bi bi-box-arrow-right" />
        </button>
      </div>
    </div>

    <div v-if="user && authorized">
      <div class="row container my-5">
        <button
          class="btn btn-outline-dark btn-lg col-12 my-1"
          @click="changePage('Scrutineering')"
        >
          {{ $t("nav.Scrutineering") }}
        </button>
        <button
          class="btn btn-outline-dark btn-lg col-12 my-1 disabled"
          @click="changePage('StatReport')"
        >
          {{ $t("nav.StatReport") }}
        </button>
        <button
          class="btn btn-outline-dark btn-lg col-12 my-1 disabled"
          @click="changePage('GameReport')"
        >
          {{ $t("nav.GameReport") }}
        </button>
      </div>
    </div>

    <div v-if="user && !authorized" class="text-center">
      <button class="btn btn-outline-dark btn-lg" @click="changePage('Home')">
        <span class="bi bi-box-arrow-left" />
        {{ $t("nav.ChangeEvent") }}
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
        class="btn-close"
        data-bs-dismiss="alert"
        aria-label="Close"
      ></button>
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
import { prefsMixin, userMixin, logoutMixin } from '@/mixins/mixins'
import statusMixin from '@/mixins/statusMixin'

export default {
  name: 'Login',
  mixins: [prefsMixin, userMixin, logoutMixin, statusMixin],
  data () {
    return {
      input: {
        login: '',
        password: ''
      },
      message: ''
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
  mounted () {
    this.checkAuthorized()
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
        this.authorized = true
      }
    },
    async formSubmit () {
      if (!(await this.checkOnline())) {
        return
      }
      this.message = ''
      if (this.input.login !== '' && this.input.password !== '') {
        // Création du token d'authentification
        const authToken = Buffer.from(
          `${this.input.login}:${this.input.password}`,
          'utf8'
        ).toString('base64')
        // Requête API
        await privateApi
          .getToken(authToken)
          .then(response => {
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
            date.setTime(date.getTime() + 10 * 24 * 60 * 60 * 1000)
            const expires = 'expires=' + date.toUTCString()
            document.cookie =
              'kpi_app=' +
              response.data.user.token +
              '; ' +
              expires +
              '; path=/'
            this.checkAuthorized()
          })
          .catch(error => {
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
