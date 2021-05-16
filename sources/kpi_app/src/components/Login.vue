<template>
  <div class="container">
    <div v-if="user">
      {{ $t("Login.Welcome") }} {{ user.firstname }} {{ user.name }}
      <button class="btn btn-sm btn-btn-warning" @click="logOut" :title="$t('Login.Logout')">
        <span class="bi bi-box-arrow-right"></span>
      </button>
    </div>

    <div
      class="alert alert-danger alert-dismissible fade show"
      role="alert"
      v-if="message"
    >
      {{ message }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="dataDismiss">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>

    <div v-if="showLoginForm">
      <title-component
        :text="$t('Login.Authentication')"
      />

      <div class="row justify-content-center mb-4">
        <form class="col-6" @submit.prevent="formSubmit">
          <div class="form-group">
            <label>{{ $t("Login.Login") }}</label>
            <input
              type="tel"
              class="form-control"
              v-model.number="input.login"
              aria-describedby="loginHelp"
            />
            <small id="loginHelp" class="form-text text-muted">{{
              $t("Login.LoginHelp")
            }}</small>
          </div>
          <div class="form-group">
            <label>{{ $t("Login.Password") }}</label>
            <input
              type="password"
              class="form-control"
              v-model="input.password"
            />
          </div>
          <button
            type="submit"
            class="btn btn-primary"
          >
            {{ $t("Login.Submit") }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import TitleComponent from '@/components/Title'

export default {
  name: 'Login',
  components: {
    TitleComponent
  },
  computed: {
    user () {
      // Récupération depuis le store
      return User.query().first()
    }
  },
  data () {
    return {
      input: {
        login: '',
        password: ''
      },
      message: '',
      showLoginForm: false
    }
  },
  methods: {
    async getUser () {
      // User absent du store ?
      if (User.query().count() === 0) {
        // Chargement de user depuis IndexedDB
        const result = await idbs.dbFindAll('user')
        // Un seul résultat ?
        if (result.length === 1) {
          // Ajout dans le store
          User.insertOrUpdate({
            data: result
          })
        } else {
          // Formulaire de login
          this.logOut()
        }
      } else {
        // User déjà présent dans le store
      }
    },
    async logOut () {
      // Suppression d'IndexedDB
      await idbs.dbDelete('user', this.user.id)
      // Suppression du store
      User.deleteAll()
      // Affichage du formulaire
      this.showLoginForm = true
    },
    async formSubmit () {
      this.message = ''
      if (this.input.login !== '' && this.input.password !== '') {
        // Création du token d'authentification
        const authToken = Buffer.from(`${this.input.login}:${this.input.password}`, 'utf8').toString('base64')
        // Requête API
        await axios.get('/login.php', {
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
          }).catch((error) => {
            // Erreur dans la réponse ?
            if (error.response) {
              if (error.response.status === 401) {
                this.message = this.$t('Login.UnauthorizedMsg')
              }
            // Erreur dans la requête ?
            } else if (error.request) {
              this.message = this.$t('Login.ErrorMsg')
            }
          })
      } else {
        // Formulaire vide
        this.message = this.$t('Login.EmptyMsg')
      }
    },
    dataDismiss () {
      this.message = ''
    }
  },
  created () {
    this.getUser()
  }
}
</script>
