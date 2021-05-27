<template>
  <div class="container-md">
    <title-component :text="$t('nav.Games')" />

    <form>
      <div class="form-row align-items-center">
        <div class="col-2">
          <select v-model="fav_categories" class="form-control selectpicker" multiple :data-header="$t('Games.Categories')" :title="$t('Games.Categories')" @change="changeFav">
            <option v-for="(categorie, index) in categories" :key="index">{{ categorie }}</option>
          </select>
        </div>
        <div class="col-6">
          <select v-model="fav_teams" class="form-control selectpicker" multiple
            data-live-search="true"
            :data-header="$t('Games.Teams') + ' & ' + $t('Games.Refs')"
            :title="$t('Games.Teams') + ' & ' + $t('Games.Refs')"
            @change="changeFav">
            <optgroup :label="$t('Games.Teams')">
              <option v-for="(team, index) in teams" :key="index">{{ team }}</option>
            </optgroup>
            <optgroup :label="$t('Games.Refs')">
              <option v-for="(ref, index) in refs" :key="index">{{ ref }}</option>
            </optgroup>
          </select>
        </div>
        <div class="col-3">
          <select v-model="fav_dates" class="form-control selectpicker" :data-header="$t('Games.Dates')" :title="$t('Games.Dates')" @change="changeFav">
            <option :data-content="$t('Games.All')"></option>
            <option data-divider="true"></option>
            <option v-for="(game_date, index) in game_dates" :key="index" :data-content="$d(new Date(game_date), 'short')">{{ game_date }}</option>
            <option data-divider="true"></option>
            <option :data-content="$t('Games.Today')">Today</option>
            <option :data-content="$t('Games.Tomorow')">Tomorow</option>
            <option :data-content="$t('Games.Prev')">Prev</option>
            <option :data-content="$t('Games.Next')">Next</option>
          </select>
        </div>
        <button class="btn btn-primary btn-sm col-1" @click="loadGames"><i class="bi bi-arrow-clockwise"></i></button>
      </div>
    </form>

    <div>{{ filteredGamesCount }}/{{ gamesCount }} {{ $t('Games.games') }}</div>

    <table v-if="games">
      <thead>
        <tr>
          <th>#</th>
          <th>date</th>
          <th>time</th>
          <th>Cat</th>
          <th>pitch</th>
          <th>A</th>
          <th>B</th>
          <th>Ref1</th>
          <th>Ref2</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="game in games" :key="game.g_id">
          <td>{{ game.g_number }}</td>
          <td>{{ game.g_date }}</td>
          <td>{{ game.g_time }}</td>
          <td>{{ game.c_code }}</td>
          <td>{{ game.g_pitch }}</td>
          <td>{{ game.t_a_label }}</td>
          <td>{{ game.t_b_label }}</td>
          <td>{{ game.r_1 }}</td>
          <td>{{ game.r_2 }}</td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-sm btn-light my-3 float-right" @click="scrollTop"><i class="bi bi-caret-up-square"></i></button>
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import { prefsMixin } from '@/services/mixins'
import { api } from '@/services/api'
import idbs from '@/services/idbStorage'
import Games from '@/store/models/Games'
import Preferences from '@/store/models/Preferences'
import $ from 'jquery'
import 'bootstrap-select/dist/js/bootstrap-select.min.js'
import dayjs from 'dayjs'

export default {
  name: 'Games',
  mixins: [prefsMixin],
  components: {
    TitleComponent
  },
  computed: {
    gamesCount () {
      return Games.query().count()
    }
  },
  data () {
    return {
      games: null,
      filteredGamesCount: 0,
      categories: null,
      game_dates: null,
      teams: null,
      refs: null,
      fav_categories: [],
      fav_teams: [],
      fav_refs: [],
      fav_dates: ''
    }
  },
  methods: {
    async loadCategories () {
      const allGames = await Games.all()
      this.categories = [...new Set(allGames.map(x => x.c_code))].sort()
      this.game_dates = [...new Set(allGames.map(x => x.g_date))].sort()
      this.teams = [...new Set(
        allGames.map(x => x.t_a_label).concat(allGames.map(x => x.t_b_label))
      )].sort()
      this.refs = [...new Set(
        allGames.map(x => x.r_1_name).concat(allGames.map(x => x.r_2_name))
      )].sort()
    },
    async getFav () {
      await this.prefs
      this.fav_categories = JSON.parse(this.prefs.fav_categories)
      this.fav_teams = JSON.parse(this.prefs.fav_teams)
      this.fav_dates = this.prefs.fav_dates
      this.filterGames()
    },
    async changeFav () {
      await Preferences.update({
        where: 1,
        data: {
          fav_categories: JSON.stringify(this.fav_categories),
          fav_teams: JSON.stringify(this.fav_teams),
          fav_dates: this.fav_dates
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
      this.filterGames()
    },
    filterGames () {
      let filteredGames = Games.query()
      if (this.fav_teams.length > 0) {
        filteredGames.where('t_a_label', (value) => this.fav_teams.includes(value))
        filteredGames.orWhere('t_b_label', (value) => this.fav_teams.includes(value))
        filteredGames.orWhere('r_1', (value) => this.fav_teams.includes(value.split(' (')[0]))
        filteredGames.orWhere('r_2', (value) => this.fav_teams.includes(value.split(' (')[0]))
        filteredGames.orWhere('r_1', (value) => this.fav_teams.includes(value.split('(').pop().split(')')[0]))
        filteredGames.orWhere('r_2', (value) => this.fav_teams.includes(value.split('(').pop().split(')')[0]))
        filteredGames.orWhere('r_1_name', (value) => this.fav_teams.includes(value))
        filteredGames.orWhere('r_2_name', (value) => this.fav_teams.includes(value))
      }
      filteredGames = filteredGames.get()
      switch (this.fav_dates) {
        case '':
          break
        case 'Next':
          filteredGames = filteredGames.filter(value => value.g_status !== 'END')
          break
        case 'Prev':
          filteredGames = filteredGames.filter(value => value.g_status !== 'ATT')
          break
        case 'Today':
          filteredGames = filteredGames.filter(value => value.g_date === dayjs().format('YYYY-MM-DD'))
          break
        case 'Tomorow':
          filteredGames = filteredGames.filter(value => value.g_date === dayjs().add(1, 'day').format('YYYY-MM-DD'))
          break
        default:
          filteredGames = filteredGames.filter(value => value.g_date === this.fav_dates)
          break
      }
      if (this.fav_categories.length > 0) {
        filteredGames = filteredGames.filter(value => this.fav_categories.includes(value.c_code))
      }
      this.games = filteredGames
      this.filteredGamesCount = this.games.length
    },
    async getGames () {
      if (Games.query().count() === 0) {
        const result = await idbs.dbGetAll('games')
        if (result.length > 0) {
          await Games.insertOrUpdate({
            data: result
          })
        } else {
          this.loadGames()
        }
        this.filterGames()
      }
    },
    async loadGames () {
      await this.prefs
      await api.get('/games/' + this.prefs.event)
        .then(async result => {
          await Games.deleteAll()
          await Games.insertOrUpdate({
            data: result.data
          })
          idbs.dbClear('games')
          result.data.forEach(element => {
            idbs.dbPut('games', element)
          })
          this.loadCategories()
          this.filterGames()
        }).catch(error => {
          console.log('Erreur:', error)
        })
    }
  },
  created () {
    this.getGames()
    this.getFav()
  },
  mounted () {
    this.loadGames()
  },
  updated () {
    $('.selectpicker').selectpicker('refresh')
  }
}
</script>

<style lang="scss" scoped>
table {
  border: 1px solid grey;
  // margin-left: -15px;
  // margin-right: -15px;
}
</style>
