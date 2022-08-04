import idbs from '@/services/idbStorage'
import User from '@/store/models/User'

export default {
  data () {
    return {
      showLoginForm: false
    }
  },
  methods: {
    async logOut () {
      await idbs.dbClear('user')
      User.deleteAll()
      document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Strict;'
      this.showLoginForm = true
    }
  }
}
