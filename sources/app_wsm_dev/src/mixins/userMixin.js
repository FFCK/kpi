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
      authorized: false,
      userEvents: []
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
        this.userEvents = this.user.events.split('|').map(e => { return parseInt(e) })
        // console.log(this.userEvents)
      } else {
        this.$router.push({ name: 'Login' })
      }
    }
  },
  created () {
    this.getUser()
  }
}
