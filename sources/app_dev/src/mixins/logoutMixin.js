import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

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
      await idbs.dbClear('games')
      Games.deleteAll()
      Preferences.update({
        where: 1,
        data: {
          scr_team_id: null,
          scr_team_label: null,
          scr_team_club: null,
          scr_team_logo: null
        }
      })
      document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      this.showLoginForm = true
    }
  }
}
