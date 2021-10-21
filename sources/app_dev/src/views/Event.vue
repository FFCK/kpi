<template>
  <div></div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import statusMixin from '@/mixins/statusMixin'
import idbs from '@/services/idbStorage'
import publicApi from '@/network/publicApi'
import Events from '@/store/models/Events'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

export default {
  name: 'Event',
  mixins: [prefsMixin, statusMixin],
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      eventSelected: 0
    }
  },
  methods: {
    async loadEvent () {
      await this.getPrefs()
      const eventId = parseInt(this.$route.params.event_id)
      if (!(await this.checkOnline())) {
        return
      }
      await publicApi
        .checkEvent(eventId)
        .then(result => {
          if (result.data.length === 1) {
            const eventResult = result.data[0]
            eventResult.id = parseInt(eventResult.id)
            Events.insertOrUpdate({
              data: eventResult
            })
            const eventMode = eventResult.id < 3000 ? 'std' : 'champ'
            Preferences.update({
              where: 1,
              data: {
                events: eventMode,
                event: eventResult.id,
                event_name: eventResult.libelle,
                event_place: eventResult.place,
                event_logo: eventResult.logo
              }
            })
            idbs.dbPut('preferences', Preferences.find(1))
            Games.deleteAll()
            idbs.dbClear('games')
            idbs.dbClear('charts')
          } else {
            console.log('No result')
          }
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
      this.$router.push({ name: 'Home' })
    }
  },
  mounted () {
    this.loadEvent()
  }
}
</script>
