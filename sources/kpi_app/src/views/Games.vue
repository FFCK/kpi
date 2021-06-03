<template>
  <div>
    <title-component :text="$t('nav.Games')" />

    <el-main>

      <el-row :gutter="4">
        <el-col :span="4">
          <el-select v-model="fav_categories" multiple collapse-tags
            :placeholder="$t('Games.Categories')" @change="changeFav">
            <el-option v-for="(categorie, index) in categories" :key="index" :label="categorie" :value="categorie" />
          </el-select>
        </el-col>
        <el-col :span="10">
          <el-select v-model="fav_teams" multiple filterable collapse-tags
            :placeholder="$t('Games.Teams') + ' & ' + $t('Games.Refs')"
            @change="changeFav">
            <el-option-group :label="$t('Games.Teams')">
              <el-option v-for="(team, index) in teams" :key="index" :value="team" />
            </el-option-group>
            <el-option-group :label="$t('Games.Refs')">
              <el-option v-for="(ref, index) in refs" :key="index" :value="ref" />
            </el-option-group>
          </el-select>
        </el-col>
        <el-col :span="5">
          <el-select v-model="fav_dates" collapse-tags :placeholder="$t('Games.Dates')" @change="changeFav">
            <el-option :label="$t('Games.All')" value="" />
            <el-divider></el-divider>
            <el-option v-for="(game_date, index) in game_dates" :key="index" :label="$d(new Date(game_date), 'short')" :value="game_date" />
            <el-divider></el-divider>
            <el-option :label="$t('Games.Today')" value="Today" />
            <el-option :label="$t('Games.Tomorow')" value="Tomorow" />
            <el-option :label="$t('Games.Prev')" value="Prev" />
            <el-option :label="$t('Games.Next')" value="Next" />
          </el-select>
        </el-col>
        <el-col :span="3">
          Refs <el-switch v-model="showRefs" />
        </el-col>
        <el-col :span="2">
          <el-button icon="el-icon-refresh-right" plain @click="loadGames"></el-button>
        </el-col>
      </el-row>

      <div class="mt-2">
        <div>
          <div class="content-table d-none d-sm-block">
            <table class="table table-sm table-striped">
              <caption>{{ filteredGamesCount }}/{{ gamesCount }} {{ $t('Games.games') }}</caption>
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>{{ $t('Games.Date') }}</th>
                  <th>{{ $t('Games.Cat') }}</th>
                  <th>{{ $t('Games.Group') }}</th>
                  <th>{{ $t('Games.Pitch') }}</th>
                  <th class="cliquableNomEquipe">{{ $t('Games.Team') }} A</th>
                  <th class="cliquableScore">{{ $t('Games.Score') }}</th>
                  <th class="cliquableNomEquipe">{{ $t('Games.Team') }} B</th>
                  <th v-if="showRefs">{{ $t('Games.Referee') }}</th>
                </tr>
              </thead>
              <tbody v-for="(game_group, index) in games" :key="index">
                <tr class="thead-light">
                  <th colspan="8" class="text-left">{{ $d(new Date(game_group.goupDate), 'short') }}</th>
                  <th class="text-right">
                    <button class="btn btn-sm btn-light" @click="scrollTop">
                      <i class="bi bi-caret-up-square"></i>
                    </button>
                  </th>
                </tr>
                <tr v-for="game in game_group.filtered" :key="game.g_id">
                  <td class="align-middle">
                    <span class="text-center badge">
                      {{ game.g_number }}
                    </span>
                  </td>
                  <td>
                    <span class="float-right badge badge-light">{{ game.g_time }}</span>
                  </td>
                  <td class="align-middle">
                    <span class="text-center badge">
                      {{ game.c_code }}
                    </span>
                  </td>
                  <td class="align-middle">
                    <span class="text-center badge">
                      {{ game.d_phase }}
                    </span>
                  </td>
                  <td class="align-middle">
                    <span class="text-center badge badge-secondary">{{ game.g_pitch }}</span>
                  </td>
                  <td class="text-center align-middle">
                    <a href="" class="btn btn-sm btn-outline-dark text-nowrap">
                      <span class="team" v-html="game.t_a_label"></span>
                    </a>
                  </td>
                  <td>
                    <div class="row text-center">
                      <img
                        class="img2 col d-none d-lg-block img-responsive"
                        :src="'/img/KIP/logo/'+game.t_a_club+'-logo.png'"
                        :alt="game.t_a_club"
                        onerror="this.onerror=null; this.src='/kpi_app/assets/logo.png'"
                        width="30">
                      <span class="col btn btn-sm btn-outline-dark text-nowrap">{{ game.g_score_a }} - {{ game.g_score_b }}</span>
                      <img
                        class="img2 col d-none d-lg-block img-responsive"
                        :src="'/img/KIP/logo/'+game.t_b_club+'-logo.png'"
                        :alt="game.t_b_club"
                        onerror="this.onerror=null; this.src='/kpi_app/assets/logo.png'"
                        width="30">
                    </div>
                  </td>
                  <td class="text-center align-middle">
                    <a href="" class="btn btn-sm btn-outline-dark text-nowrap">
                      <span class="team" v-html="game.t_b_label"></span>
                    </a>
                  </td>
                  <td v-if="showRefs">
                    <div>
                      <small v-html="game.r_1"></small>
                    </div>
                    <div>
                      <small v-html="game.r_2"></small>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="content-table d-block d-sm-none">
            <table class="table table-sm table-striped" style="table-layout: auto; width: 100%">
              <tbody>
                <tr v-for="game in games" :key="game.g_id">
                  <td class="text-center">
                    <div class="col-xs-6">
                      <span class="float-left badge badge-pill badge-secondary mx-1">
                        {{ game.g_time }}
                      </span>
                      <span class="float-left badge badge-pill badge-secondary">
                        {{ $t('Games.Pitch') }} {{ game.g_pitch }}
                      </span>
                    </div>
                    <div class="col-xs-6">
                      <span class="float-right badge badge-pill badge-light mx-1">
                        {{ game.c_code }}
                      </span>
                      <span class="float-right badge badge-pill">
                        {{ game.d_phase }}
                      </span>
                    </div>
                    <div class="col-12">
                      <div class="btn-group btn-block row" role="group">
                        <a class="col-5 text-right btn btn-sm">
                          <b><span class="team" v-html="game.t_a_label"></span></b>
                        </a>
                        <span class="col-2 btn btn-sm btn-success">
                          {{ game.g_score_a }} - {{ game.g_score_b }}
                        </span>
                        <a class="col-5 text-left btn btn-sm">
                          <b><span class="team" v-html="game.t_b_label"></span></b>
                        </a>
                      </div>
                    </div>
                    <div v-if="showRefs" class="row">
                      <div class="col text-left">
                        <small><em v-html="game.r_1"></em></small>
                      </div>
                      <div class="col text-right">
                        <small><em v-html="game.r_2"></em></small>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </el-main>

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
      games: [],
      filteredGamesCount: 0,
      categories: null,
      game_dates: null,
      teams: null,
      refs: null,
      showRefs: true,
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
      // highlight
      filteredGames = filteredGames.map(value => {
        value.t_a_label = (this.fav_teams.includes(value.t_a_label)) ? '<mark>' + value.t_a_label + '</mark>' : value.t_a_label
        value.t_b_label = (this.fav_teams.includes(value.t_b_label)) ? '<mark>' + value.t_b_label + '</mark>' : value.t_b_label
        value.r_1 = (this.fav_teams.includes(value.r_1.split(' (')[0])) ? value.r_1.replace(value.r_1.split(' (')[0], '<mark>' + value.r_1.split(' (')[0] + '</mark>') : value.r_1
        value.r_2 = (this.fav_teams.includes(value.r_2.split(' (')[0])) ? value.r_2.replace(value.r_2.split(' (')[0], '<mark>' + value.r_2.split(' (')[0] + '</mark>') : value.r_2
        value.r_1 = (this.fav_teams.includes(value.r_1.split('(').pop().split(')')[0])) ? value.r_1.replace(value.r_1.split('(').pop().split(')')[0], '<mark>' + value.r_1.split('(').pop().split(')')[0] + '</mark>') : value.r_1
        value.r_2 = (this.fav_teams.includes(value.r_2.split('(').pop().split(')')[0])) ? value.r_2.replace(value.r_2.split('(').pop().split(')')[0], '<mark>' + value.r_2.split('(').pop().split(')')[0] + '</mark>') : value.r_2
        value.r_1 = (this.fav_teams.includes(value.r_1_name)) ? value.r_1.replace(value.r_1.split(' (')[0], '<mark>' + value.r_1.split(' (')[0] + '</mark>') : value.r_1
        value.r_2 = (this.fav_teams.includes(value.r_2_name)) ? value.r_2.replace(value.r_2.split(' (')[0], '<mark>' + value.r_2.split(' (')[0] + '</mark>') : value.r_2
        return value
      })

      this.filteredGamesCount = filteredGames.length

      const filteredGamesDates = [...new Set(filteredGames.map(x => x.g_date))]
      this.games = []
      filteredGamesDates.forEach(goupDate => {
        const filtered = filteredGames.filter(value => value.g_date === goupDate)
        this.games.push({
          goupDate: goupDate,
          filtered: filtered
        })
      })
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
div.content-table {
  margin-left: -14px;
  margin-right: -14px;
}

table {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  table-layout: auto;
  width: 100%;
}

</style>
