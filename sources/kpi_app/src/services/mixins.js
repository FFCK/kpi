import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

const logoutMixin = {
  data () {
    return {
      showLoginForm: false
    }
  },
  methods: {
    async logOut () {
      await idbs.dbClear('user')
      User.deleteAll()
      await idbs.dbClear('games')
      Games.deleteAll()
      document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      this.showLoginForm = true
    }
  }
}

const prefsMixin = {
  computed: {
    prefs () {
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
    },
    scrollTop () {
      document.body.scrollTop = 0 // For Safari
      document.documentElement.scrollTop = 0 // For Chrome, Firefox, IE and Opera
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
