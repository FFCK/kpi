<template>
  <div class="container mt-1">
    <div
      v-if="prefs.scr_team_id !== null"
      role="button"
      class="h5 text-center"
      @click="loadTeams"
    >
      <img
        v-if="prefs.scr_team_logo"
        class="mb-2 team_logo"
        :src="`${baseUrl}/img/${prefs.scr_team_logo}`"
        alt="Logo"
      />
      {{ prefs.scr_team_label }}
      <button v-if="!showSelector" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left-right" /> {{ $t("Teams.Change") }}
      </button>
    </div>
    <div v-else class="text-center">
      <button v-if="!showSelector" class="btn btn-primary" @click="loadTeams">
        {{ $t("Teams.SelectTeam") }}
      </button>
    </div>

    <form v-if="showSelector" class="align-items-center">
      <div class="row mb-2">
        <div class="col-xs-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 row">
          <div class="text-center">
            <select
              v-model="teamSelected"
              class="form-select"
              @change="changeButton = true"
            >
              <option disabled value="0">
                ▼ {{ $t("Teams.PleaseSelectOne") }} ▼
              </option>
              <option
                v-for="team in teams"
                :key="team.id"
                :value="team.team_id"
              >
                {{ team.label }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-3 offset-3 text-center">
          <button class="btn btn-secondary btn-sm" @click.prevent="cancelTeam">
            {{ $t("Teams.Cancel") }}
          </button>
        </div>
        <div class="col-3 text-center">
          <button
            v-if="changeButton"
            class="btn btn-primary btn-sm"
            @click.prevent="changeTeam"
          >
            {{ $t("Teams.Confirm") }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { prefsMixin, userMixin } from '@/mixins/mixins'
import idbs from '@/services/idbStorage'
import privateApi from '@/network/privateApi'
import Teams from '@/store/models/Teams'
import Preferences from '@/store/models/Preferences'
import statusMixin from '@/mixins/statusMixin'

export default {
  name: 'TeamSelector',
  mixins: [prefsMixin, userMixin, statusMixin],
  data () {
    return {
      baseUrl: process.env.VUE_APP_BASE_URL,
      showSelector: false,
      teamSelected: 0,
      changeButton: false
    }
  },
  computed: {
    teams () {
      return Teams.query()
        .orderBy('id', 'desc')
        .get()
    }
  },
  methods: {
    async loadTeams () {
      if (!(await this.checkOnline())) {
        return
      }
      await privateApi.getTeams(this.prefs.event).then(result => {
        Teams.deleteAll()
        Teams.insertOrUpdate({
          data: result.data
        })
        this.teamSelected = this.prefs.scr_team_id || 0
        this.showSelector = true
      })
    },
    async changeTeam () {
      if (!(await this.checkOnline())) {
        return
      }
      const t = Teams.query()
        .where('team_id', this.teamSelected)
        .first()
      Preferences.update({
        where: 1,
        data: {
          scr_team_id: t.team_id,
          scr_team_label: t.label,
          scr_team_club: t.club,
          scr_team_logo: t.logo
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
      this.showSelector = false
      this.changeButton = false
      this.$emit('changeTeam')
    },
    cancelTeam () {
      this.showSelector = false
      this.changeButton = false
    }
  }
}
</script>

<style scoped>
.team_logo {
  max-height: 55px;
  max-width: 55px;
}
</style>
