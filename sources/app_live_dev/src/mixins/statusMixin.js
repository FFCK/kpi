import Status from '@/store/models/Status'

export default {
  methods: {
    async checkOnline () {
      const status = await Status.find(1)
      if (!status.online) {
        Status.update({
          where: 1,
          data: {
            messageText: this.$t('status.Offline'),
            messageClass: 'alert-danger'
          }
        })
        return false
      }
      return true
    }
  }
}
