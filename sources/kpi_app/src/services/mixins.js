import idbs from '@/services/idbStorage'
import User from '@/store/models/User'

const mixin = {
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

export {
  mixin
}
