<template>
  <div>
    <title-component :text="$t('nav.Games')" />

    <div class="fixed-top filters">
      <el-row :gutter="4">
        <el-col :span="4">
          <el-select
            v-model="fav_categories"
            multiple
            collapse-tags
            :placeholder="$t('Games.Categories')"
            @change="changeFav"
          >
            <el-option
              v-for="(categorie, index) in categories"
              :key="index"
              :label="categorie"
              :value="categorie"
            />
          </el-select>
        </el-col>
        <el-col :span="10">
          <el-select
            :key="favTeamsSelectKey"
            v-model="fav_teams"
            multiple
            filterable
            collapse-tags
            :placeholder="$t('Games.Teams') + ' & ' + $t('Games.Refs')"
            @change="changeFav"
          >
            <el-option-group :label="$t('Games.Teams')">
              <el-option
                v-for="(team, index) in teams"
                :key="index"
                :value="team"
              />
            </el-option-group>
            <el-option-group :label="$t('Games.Refs')">
              <el-option
                v-for="(ref, index) in refs"
                :key="index"
                :value="ref"
              />
            </el-option-group>
          </el-select>
        </el-col>
        <el-col :span="5">
          <el-select
            v-model="fav_dates"
            collapse-tags
            :placeholder="$t('Games.Dates')"
            @change="changeFav"
          >
            <el-option :label="$t('Games.All')" value="" />
            <el-option
              v-for="(game_date, index) in game_dates"
              :key="index"
              :label="$d(new Date(game_date), 'short')"
              :value="game_date"
            />
            <el-divider />
            <el-option :label="$t('Games.Today')" value="Today" />
            <el-option :label="$t('Games.Tomorow')" value="Tomorow" />
            <el-option :label="$t('Games.Prev')" value="Prev" />
            <el-option :label="$t('Games.Next')" value="Next" />
          </el-select>
        </el-col>
        <el-col :span="3">
          <el-button plain size="small">
            Refs <el-switch v-model="showRefs" />
          </el-button>
        </el-col>
        <el-col :span="2">
          <el-button icon="el-icon-refresh-right" plain @click="loadGames" />
        </el-col>
      </el-row>
    </div>

    <game-list
      :games="games"
      :show-refs="showRefs"
      :games-count="gamesCount"
      :filtered-games-count="filteredGamesCount"
    />

    <el-backtop />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import { prefsMixin, gamesMixin } from '@/services/mixins'
import { api } from '@/services/api'
import idbs from '@/services/idbStorage'
import Games from '@/store/models/Games'
import Preferences from '@/store/models/Preferences'
import dayjs from 'dayjs'
import GameList from '@/components/GameList.vue'
import {
  ElBacktop, ElButton, ElRow, ElCol,
  ElSelect, ElOption, ElSwitch, ElDivider,
  ElOptionGroup
} from 'element-plus'
import Status from '@/store/models/Status'

export default {
  name: 'Games',
  components: {
    TitleComponent,
    GameList,
    ElBacktop,
    ElButton,
    ElRow,
    ElCol,
    ElSelect,
    ElOption,
    ElSwitch,
    ElDivider,
    ElOptionGroup
  },
  mixins: [prefsMixin, gamesMixin],
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
      favTeamsSelectKey: 0,
      fav_refs: [],
      fav_dates: '',
      status: {}
    }
  },
  computed: {
    gamesCount () {
      return Games.query().count()
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
  },
  methods: {
    async loadCategories () {
      let allGames = await Games.all()
      allGames = [...new Set(allGames)]
      this.categories = [...new Set(allGames.map(x => x.c_code))].sort()
      this.game_dates = [...new Set(allGames.map(x => x.g_date))].sort()
      this.teams = [...new Set(
        allGames.map(x => x.t_a_label).concat(allGames.map(x => x.t_b_label))
      )].filter(value => value !== null).sort()
      this.refs = [...new Set(
        allGames.map(x => x.r_1_name).concat(allGames.map(x => x.r_2_name))
      )].filter(value => value !== null).sort()
      this.favTeamsSelectKey++
    },
    async getFav () {
      await this.getPrefs()
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
        filteredGames.orWhere('r_1', (value) => value ? this.fav_teams.includes(value.split(' (')[0]) : false)
        filteredGames.orWhere('r_2', (value) => value ? this.fav_teams.includes(value.split(' (')[0]) : false)
        filteredGames.orWhere('r_1', (value) => value ? this.fav_teams.includes(value.split('(').pop().split(')')[0]) : false)
        filteredGames.orWhere('r_2', (value) => value ? this.fav_teams.includes(value.split('(').pop().split(')')[0]) : false)
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
        if (value.r_1) {
          value.r_1 = (this.fav_teams.includes(value.r_1.split(' (')[0])) ? value.r_1.replace(value.r_1.split(' (')[0], '<mark>' + value.r_1.split(' (')[0] + '</mark>') : value.r_1
          value.r_1 = (this.fav_teams.includes(value.r_1.split('(').pop().split(')')[0])) ? value.r_1.replace(value.r_1.split('(').pop().split(')')[0], '<mark>' + value.r_1.split('(').pop().split(')')[0] + '</mark>') : value.r_1
          value.r_1 = (this.fav_teams.includes(value.r_1_name)) ? value.r_1.replace(value.r_1.split(' (')[0], '<mark>' + value.r_1.split(' (')[0] + '</mark>') : value.r_1
        }
        if (value.r_2) {
          value.r_2 = (this.fav_teams.includes(value.r_2.split(' (')[0])) ? value.r_2.replace(value.r_2.split(' (')[0], '<mark>' + value.r_2.split(' (')[0] + '</mark>') : value.r_2
          value.r_2 = (this.fav_teams.includes(value.r_2.split('(').pop().split(')')[0])) ? value.r_2.replace(value.r_2.split('(').pop().split(')')[0], '<mark>' + value.r_2.split('(').pop().split(')')[0] + '</mark>') : value.r_2
          value.r_2 = (this.fav_teams.includes(value.r_2_name)) ? value.r_2.replace(value.r_2.split(' (')[0], '<mark>' + value.r_2.split(' (')[0] + '</mark>') : value.r_2
        }
        return value
      })

      this.filteredGamesCount = filteredGames.length

      const filteredGamesDates = [...new Set(filteredGames.map(x => x.g_date))]
      const games = []
      filteredGamesDates.forEach(goupDate => {
        const filtered = filteredGames.filter(value => value.g_date === goupDate)
        games.push({
          goupDate: goupDate,
          filtered: filtered
        })
      })
      this.games = games
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
      await this.getPrefs()
      await this.prefs
      this.status = await Status.find(1)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        await api.get('/games/' + this.prefs.event)
          .then(async result => {
            const gamelist = await result.data.map(game => {
              game.r_1 = game.r_1 && game.r_1 !== '-1' ? game.r_1.replace(/\) (INT-|NAT-|REG-|REG|OTM|JO)[ABCS]{0,1}/, ')') : null
              game.r_2 = game.r_2 && game.r_2 !== '-1' ? game.r_2.replace(/\) (INT-|NAT-|REG-|REG|OTM|JO)[ABCS]{0,1}/, ')') : null
              game.t_a_label ??= this.gameEncode(game.g_code, 1)
              game.t_b_label ??= this.gameEncode(game.g_code, 2)
              game.r_1 ??= this.gameEncode(game.g_code, 3)
              game.r_2 ??= this.gameEncode(game.g_code, 4)
              return game
            })
            await Games.deleteAll()
            await Games.insertOrUpdate({
              data: gamelist
            })
            idbs.dbClear('games')
            gamelist.forEach(element => {
              idbs.dbPut('games', element)
            })
            this.loadCategories()
            this.filterGames()
          }).catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            }
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.filters {
  margin-top: 62px;
  margin-left: 10px;
}
</style>
