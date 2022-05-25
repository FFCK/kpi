import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'

export default {
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
  async mounted () {
    await this.getPrefs()
  }
}
