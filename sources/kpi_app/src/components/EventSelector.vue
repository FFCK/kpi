<template>
  <div v-if="prefs" class="container mt-3">
    <div
      v-if="prefs.event > 0"
      role="button"
      class="h5 text-center"
      @click="loadEvents"
    >
      {{ prefs.event_name }} - {{ prefs.event_place }}
      <button v-if="!showSelector" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left-right" />
      </button>
    </div>
    <div v-else>
      <button v-if="!showSelector" class="btn btn-primary" @click="loadEvents">
        {{ $t("Event.SelectEvent") }}
      </button>
    </div>

    <form v-if="showSelector" class="align-items-center">
      <div class="row mb-2">
        <div class="col-8 offset-2 text-center">
          <select
            v-model="eventSelected"
            class="form-control"
            @change="changeButton = true"
          >
            <option disabled value="0">
              ▼ {{ $t("Event.PleaseSelectOne") }} ▼
            </option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.id }} | {{ event.libelle }} - {{ event.place }}
            </option>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-3 offset-3 text-center">
          <button class="btn btn-secondary" @click.prevent="cancelEvent">
            {{ $t("Event.Cancel") }}
          </button>
        </div>
        <div class="col-3 text-center">
          <button
            v-if="changeButton"
            class="btn btn-primary"
            @click.prevent="changeEvent"
          >
            {{ $t("Event.Change") }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { prefsMixin } from '@/services/mixins'
import idbs from '@/services/idbStorage'
import publicApi from '@/network/publicApi'
import Events from '@/store/models/Events'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'
import Status from '@/store/models/Status'

export default {
  name: 'EventSelector',
  mixins: [prefsMixin],
  data () {
    return {
      showSelector: false,
      eventSelected: 0,
      changeButton: false,
      status: {}
    }
  },
  computed: {
    events () {
      return Events.query().orderBy('id', 'desc').get()
    }
  },
  methods: {
    async loadEvents () {
      this.status = await Status.find(1)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        await publicApi.getEvents()
          .then(result => {
            const eventsResult = result.data.map(event => {
              event.id = parseInt(event.id)
              return event
            })
            Events.deleteAll()
            Events.insertOrUpdate({
              data: eventsResult
            })
            this.eventSelected = this.prefs.event
            this.showSelector = true
          }).catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            }
          })
      }
    },
    async changeEvent () {
      this.status = await Status.find(1)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        const e = Events.find(this.eventSelected)
        Preferences.update({
          where: 1,
          data: {
            event: e.id,
            event_name: e.libelle,
            event_place: e.place,
            fav_categories: '[]',
            fav_teams: '[]',
            fav_refs: '[]',
            fav_dates: ''

          }
        })
        idbs.dbPut('preferences', Preferences.find(1))
        Games.deleteAll()
        idbs.dbClear('games')
        this.showSelector = false
        this.changeButton = false
      }
    },
    cancelEvent () {
      this.showSelector = false
      this.changeButton = false
    }
  }
}
</script>
