import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'

const logoutMixin = {
  data () {
    return {
      showLoginForm: false
    }
  },
  methods: {
    async logOut () {
      // Suppression d'IndexedDB
      await idbs.dbClear('user')
      // Suppression du store
      User.deleteAll()
      // Suppression du cookie
      document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      // Affichage du formulaire
      this.showLoginForm = true
    }
  }
}

const prefsMixin = {
  computed: {
    prefs () {
      // Récupération depuis le store
      return Preferences.query().first()
    }
  },

  methods: {
    async getPrefs () {
      if (Preferences.query().count() === 0) {
        const result = await idbs.dbGetAll('preferences')
        if (result.length === 1) {
          Preferences.insertOrUpdate({
            data: result
          })
        } else {
          Preferences.insertOrUpdate({
            data: {
              id: 1
            }
          })
          idbs.dbPut('preferences', Preferences.query().first())
        }
      }
    }
  },

  created () {
    this.getPrefs()
  }
}

const userMixin = {
  computed: {
    user () {
      return User.query().first()
    }
  },
  methods: {
    async getUser () {
      if (User.query().count() === 0) {
        const result = await idbs.dbGetAll('user')
        if (result.length === 1) {
          User.insertOrUpdate({
            data: result
          })
        }
      }
    }
  },
  created () {
    this.getUser()
  }
}

export {
  logoutMixin,
  prefsMixin,
  userMixin
}
