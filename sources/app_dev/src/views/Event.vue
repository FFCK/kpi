<template>
  <div></div>
</template>

<script>
import { prefsMixin } from '@/mixins/mixins'
import idbs from '@/services/idbStorage'
import publicApi from '@/network/publicApi'
import Events from '@/store/models/Events'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'
import Status from '@/store/models/Status'

export default {
  name: 'Event',
  mixins: [prefsMixin],
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      eventSelected: 0,
      status: {}
    }
  },
  methods: {
    async loadEvent () {
      await this.getPrefs()
      this.status = await Status.find(1)
      const eventId = parseInt(this.$route.params.event_id)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        await publicApi.checkEvent(eventId)
          .then(result => {
            if (result.data.length === 1) {
              const eventResult = result.data[0]
              eventResult.id = parseInt(eventResult.id)
              Events.insertOrUpdate({
                data: eventResult
              })
              Preferences.update({
                where: 1,
                data: {
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
          }).catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            }
          })
      }
      this.$router.push({ name: 'Home' })
    }
  },
  mounted () {
    this.loadEvent()
  }
}
</script>
