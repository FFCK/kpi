<template>
  <div v-if="prefs" class="my-2">
    <div v-if="prefs.event > 0">
      {{ prefs.event_name }} ({{ prefs.event_place }})
      <button class="btn btn-secondary btn-sm" @click="loadEvents" v-if="!showSelector">
        <i class="bi bi-arrow-left-right"></i>
      </button>
    </div>
    <div v-else>
      <button class="btn btn-primary" @click="loadEvents" v-if="!showSelector">
        {{ $t('Event.SelectEvent') }}
      </button>
    </div>

    <form class="form-inline" v-if="showSelector">
      <select class="form-control col-8" v-model="eventSelected" @change="changeButton = true">
        <option disabled value="0">▼ {{ $t('Event.PleaseSelectOne') }} ▼</option>
        <option :value="event.id" v-for="event in events" :key="event.id">{{ event.libelle }} - {{ event.place }}</option>
      </select>
      <button class="btn btn-primary col-2" @click.prevent="changeEvent" v-if="changeButton">{{ $t('Event.Change') }}</button>
      <button class="btn btn-secondary col-2" @click.prevent="cancelEvent">{{ $t('Event.Cancel') }}</button>
    </form>
  </div>
</template>

<script>
import { prefsMixin } from '@/services/mixins'
import idbs from '@/services/idbStorage'
import { api } from '@/services/api'
import Events from '@/store/models/Events'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

export default {
  name: 'EventSelector',
  mixins: [prefsMixin],
  computed: {
    events () {
      return Events.query().orderBy('id', 'desc').get()
    }
  },
  data () {
    return {
      showSelector: false,
      eventSelected: '0',
      changeButton: false
    }
  },
  methods: {
    async loadEvents () {
      await api.get('/events')
        .then(result => {
          Events.deleteAll()
          Events.insertOrUpdate({
            data: result.data
          })
          this.eventSelected = this.prefs.event
          this.showSelector = true
        }).catch(error => {
          console.log('Erreur:', error)
        })
    },
    changeEvent () {
      const e = Events.find(this.eventSelected)
      Preferences.update({
        where: 1,
        data: {
          event: e.id,
          event_name: e.libelle,
          event_place: e.place
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
      Games.deleteAll()
      idbs.dbClear('games')
      this.showSelector = false
      this.changeButton = false
    },
    cancelEvent () {
      this.showSelector = false
      this.changeButton = false
    }
  }
}
</script>
