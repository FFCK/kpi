import idbs from '@/services/idbStorage'
import User from '@/store/models/User'

export default {
  computed: {
    user () {
      return User.query().first()
    }
  },
  data () {
    return {
      authorized: false
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
    },
    async checkAuthorized () {
      await this.getUser()
      if (this.user) {
        const userEvents = this.user.events.split('|').map(e => { return parseInt(e) })
        this.authorized = userEvents.includes(this.prefs.event)
      } else {
        this.authorized = false
      }
    }
  },
  created () {
    this.getUser()
  }
}
