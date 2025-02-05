<template>
  <div v-if="prefs" class="container mt-3">
    <div
      v-if="prefs.event > 0"
      role="button"
      class="h5 text-center"
      @click="loadEvents"
    >
      <img
        class="mb-2 event_logo"
        :src="`${baseUrl}/img/${prefs.event_logo}`"
        alt="Logo"
        v-if="prefs.event_logo"
      />
      <br />
      {{ prefs.event_name }} - {{ prefs.event_place }}
      <button v-if="!showSelector" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left-right" /> {{ $t("Event.Change") }}
      </button>
    </div>
    <div v-else class="text-center">
      <button v-if="!showSelector" class="btn btn-primary" @click="loadEvents">
        {{ $t("Event.SelectEvent") }}
      </button>
    </div>

    <form v-if="showSelector" class="align-items-center">
      <div class="text-center mb-1">
        <div class="btn-group" role="group" aria-label="Event mode">
          <button
            type="button"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-primary': eventMode === 'std',
              active: eventMode === 'std',
              'btn-outline-primary': eventMode !== 'std'
            }"
            @click="changeEventMode('std')"
          >
            {{ $t("Event.StdEvents") }}
          </button>
          <button
            type="button"
            :class="{
              btn: true,
              'btn-sm': true,
              'btn-primary': eventMode === 'champ',
              active: eventMode === 'champ',
              'btn-outline-primary': eventMode !== 'champ'
            }"
            @click="changeEventMode('champ')"
          >
            {{ $t("Event.LocalChamp") }}
          </button>
        </div>
      </div>
      <div class="row mb-2">
        <div class="col-xs-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 row">
          <div class="text-center">
            <select
              v-model="eventSelected"
              class="form-select"
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
      </div>
      <div class="row">
        <div class="col-3 offset-3 text-center">
          <button class="btn btn-secondary btn-sm" @click.prevent="cancelEvent">
            {{ $t("Event.Cancel") }}
          </button>
        </div>
        <div class="col-3 text-center">
          <button
            v-if="changeButton"
            class="btn btn-primary btn-sm"
            @click.prevent="changeEvent"
          >
            {{ $t("Event.Confirm") }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import userMixin from '@/mixins/userMixin'
import statusMixin from '@/mixins/statusMixin'
import idbs from '@/services/idbStorage'
import publicApi from '@/network/publicApi'
import Events from '@/store/models/Events'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

export default {
  name: 'EventSelector',
  mixins: [prefsMixin, userMixin, statusMixin],
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      showSelector: false,
      eventSelected: 0,
      changeButton: false
    }
  },
  computed: {
    eventMode () {
      return Preferences.find(1).events
    },
    events () {
      if (this.eventMode === 'std') {
        return Events.query()
          .orderBy('id', 'desc')
          .get()
      } else {
        return Events.query().get()
      }
    }
  },
  methods: {
    changeEventMode (mode) {
      if (mode !== this.eventMode) {
        Preferences.update({
          where: 1,
          data: {
            events: mode
          }
        })
        idbs.dbPut('preferences', Preferences.find(1))
        this.loadEvents()
      }
    },
    async loadEvents () {
      if (!(await this.checkOnline())) {
        return
      }
      await publicApi
        .getEvents(this.eventMode)
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
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
    },
    async changeEvent () {
      if (!(await this.checkOnline())) {
        return
      }
      const e = Events.find(this.eventSelected)
      Preferences.update({
        where: 1,
        data: {
          event: e.id,
          event_name: e.libelle,
          event_place: e.place,
          event_logo: e.logo,
          fav_categories: '[]',
          fav_teams: '[]',
          fav_refs: '[]',
          fav_dates: '',
          fav_flags: true
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
      Games.deleteAll()
      idbs.dbClear('games')
      idbs.dbClear('charts')
      this.showSelector = false
      this.changeButton = false
      this.$emit('changeEvent')
    },
    cancelEvent () {
      this.showSelector = false
      this.changeButton = false
    }
  }
}
</script>

<style scoped>
.event_logo {
  max-height: 55px;
  max-width: 100%;
}
</style>
