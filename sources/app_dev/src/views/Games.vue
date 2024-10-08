<template>
  <div class="container-fluid">
    <div class="filters">
      <div class="input-group input-group-sm">
        <div class="btn btn-outline-secondary" @click="changePage('Home')">
          <i class="bi bi-caret-left-square-fill" />
        </div>
        <button
          class="btn btn-secondary text-nowrap"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#collapseFilters"
          aria-expanded="false"
          aria-controls="collapseFilters"
        >
          {{ $t("nav.Filters") }} <i class="bi bi-filter"></i>
        </button>
        <select
          class="form-select"
          aria-label="Date select"
          v-model="fav_dates"
          :placeholder="$t('Games.Dates')"
          @change="changeFav"
        >
          <option value="">{{ $t("Games.AllDates") }}</option>
          <option
            v-for="(game_date, index) in game_dates"
            :key="index"
            :value="game_date"
            >{{ $d(new Date(game_date), "short") }}</option
          >
          <option disabled>──────</option>
          <option value="Today">{{ $t("Games.Today") }}</option>
          <option value="Tomorow">{{ $t("Games.Tomorow") }}</option>
          <option value="Prev">{{ $t("Games.Prev") }}</option>
          <option value="Next">{{ $t("Games.Next") }}</option>
        </select>
        <button
          :disabled="!visibleButton"
          class="btn btn-secondary"
          @click="loadGames"
        >
          <i class="bi bi-arrow-clockwise"></i>
        </button>
        <div class="btn btn-outline-secondary" @click="changePage('Chart')">
          <i class="bi bi-caret-right-square-fill" />
        </div>
      </div>
    </div>

    <div class="row collapse container-fluid mt-1" id="collapseFilters">
      <div class="col-md-9 col-lg-6">
        <div class="card card-body bg-secondary text-light">
          {{ $t("Games.Categories") }}
          <el-select
            class="mb-2"
            v-model="fav_categories"
            multiple
            @change="changeFav"
          >
            <el-option
              v-for="(categorie, index) in categories"
              :key="index"
              :label="categorie"
              :value="categorie"
            />
          </el-select>
          {{ $t("Games.Teams") }} & {{ $t("Games.Refs") }}
          <el-select
            class="mb-2"
            :key="favTeamsSelectKey"
            v-model="fav_teams"
            multiple
            filterable
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

          <div>{{ $t("Games.ShowRefs") }} <el-switch v-model="showRefs" /></div>
          <div>
            {{ $t("Games.ShowFlags") }}
            <el-switch v-model="showFlags" @change="changeFav" />
          </div>
        </div>
      </div>
    </div>

    <game-list
      :games="games"
      :show-refs="showRefs"
      :show-flags="showFlags"
      :games-count="gamesCount"
      :filtered-games-count="filteredGamesCount"
    />

    <el-backtop />
  </div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import gamesMixin from '@/mixins/gamesMixin'
import statusMixin from '@/mixins/statusMixin'
import idbs from '@/services/idbStorage'
import Games from '@/store/models/Games'
import Preferences from '@/store/models/Preferences'
import dayjs from 'dayjs'
import GameList from '@/components/GameList.vue'
import {
  ElBacktop,
  ElSwitch,
  ElSelect,
  ElOption,
  ElOptionGroup
} from 'element-plus'

export default {
  name: 'Games',
  components: {
    GameList,
    ElBacktop,
    ElSelect,
    ElOption,
    ElSwitch,
    ElOptionGroup
  },
  mixins: [prefsMixin, gamesMixin, statusMixin],
  data () {
    return {
      games: [],
      filteredGamesCount: 0,
      categories: null,
      game_dates: null,
      teams: null,
      refs: null,
      showRefs: true,
      showFlags: true,
      fav_categories: [],
      fav_teams: [],
      favTeamsSelectKey: 0,
      fav_refs: [],
      fav_dates: '',
      visibleButton: true
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
  updated () {},
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    },
    async loadCategories () {
      let allGames = await Games.all()
      allGames = [...new Set(allGames)]
      this.categories = [...new Set(allGames.map(x => x.c_code))].sort()
      this.game_dates = [...new Set(allGames.map(x => x.g_date))].sort()
      this.teams = [
        ...new Set(
          allGames
            .map(x =>
              x.t_a_label && x.t_a_label[0] !== '¤' ? x.t_a_label : null
            )
            .concat(
              allGames.map(x =>
                x.t_b_label && x.t_b_label[0] !== '¤' ? x.t_b_label : null
              )
            )
        )
      ]
        .filter(value => value !== null)
        .sort()
      this.refs = [
        ...new Set(
          allGames.map(x => x.r_1_name).concat(allGames.map(x => x.r_2_name))
        )
      ]
        .filter(value => value !== null)
        .sort()
      this.favTeamsSelectKey++
    },
    async getFav () {
      await this.getPrefs()
      await this.prefs
      this.fav_categories = JSON.parse(this.prefs.fav_categories)
      this.fav_teams = JSON.parse(this.prefs.fav_teams)
      this.fav_dates = this.prefs.fav_dates
      this.showFlags = this.prefs.show_flags
      this.filterGames()
    },
    async changeFav () {
      await Preferences.update({
        where: 1,
        data: {
          fav_categories: JSON.stringify(this.fav_categories),
          fav_teams: JSON.stringify(this.fav_teams),
          fav_dates: this.fav_dates,
          show_flags: this.showFlags
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
      this.filterGames()
    },
    filterGames () {
      let filteredGames = Games.query()
      if (this.fav_teams.length > 0) {
        filteredGames.where('t_a_label', value =>
          this.fav_teams.includes(value)
        )
        filteredGames.orWhere('t_b_label', value =>
          this.fav_teams.includes(value)
        )
        filteredGames.orWhere('r_1', value =>
          value ? this.fav_teams.includes(value.split(' (')[0]) : false
        )
        filteredGames.orWhere('r_2', value =>
          value ? this.fav_teams.includes(value.split(' (')[0]) : false
        )
        filteredGames.orWhere('r_1', value =>
          value
            ? this.fav_teams.includes(
              value
                .split('(')
                .pop()
                .split(')')[0]
            )
            : false
        )
        filteredGames.orWhere('r_2', value =>
          value
            ? this.fav_teams.includes(
              value
                .split('(')
                .pop()
                .split(')')[0]
            )
            : false
        )
        filteredGames.orWhere('r_1_name', value =>
          this.fav_teams.includes(value)
        )
        filteredGames.orWhere('r_2_name', value =>
          this.fav_teams.includes(value)
        )
      }
      filteredGames = filteredGames.get()
      switch (this.fav_dates) {
        case '':
          break
        case 'Next':
          filteredGames = filteredGames.filter(
            value => value.g_status !== 'END'
          )
          break
        case 'Prev':
          filteredGames = filteredGames.filter(
            value => value.g_status !== 'ATT'
          )
          break
        case 'Today':
          filteredGames = filteredGames.filter(
            value => value.g_date === dayjs().format('YYYY-MM-DD')
          )
          break
        case 'Tomorow':
          filteredGames = filteredGames.filter(
            value =>
              value.g_date ===
              dayjs()
                .add(1, 'day')
                .format('YYYY-MM-DD')
          )
          break
        default:
          filteredGames = filteredGames.filter(
            value => value.g_date === this.fav_dates
          )
          break
      }
      if (this.fav_categories.length > 0) {
        filteredGames = filteredGames.filter(value =>
          this.fav_categories.includes(value.c_code)
        )
      }
      // highlight
      filteredGames = filteredGames.map(value => {
        value.t_a_label = this.fav_teams.includes(value.t_a_label)
          ? '<mark>' + value.t_a_label + '</mark>'
          : value.t_a_label
        value.t_b_label = this.fav_teams.includes(value.t_b_label)
          ? '<mark>' + value.t_b_label + '</mark>'
          : value.t_b_label
        if (value.r_1) {
          value.r_1 = this.fav_teams.includes(value.r_1.split(' (')[0])
            ? value.r_1.replace(
              value.r_1.split(' (')[0],
              '<mark>' + value.r_1.split(' (')[0] + '</mark>'
            )
            : value.r_1
          value.r_1 = this.fav_teams.includes(
            value.r_1
              .split('(')
              .pop()
              .split(')')[0]
          )
            ? value.r_1.replace(
              value.r_1
                .split('(')
                .pop()
                .split(')')[0],
              '<mark>' +
                  value.r_1
                    .split('(')
                    .pop()
                    .split(')')[0] +
                  '</mark>'
            )
            : value.r_1
          value.r_1 = this.fav_teams.includes(value.r_1_name)
            ? value.r_1.replace(
              value.r_1.split(' (')[0],
              '<mark>' + value.r_1.split(' (')[0] + '</mark>'
            )
            : value.r_1
        }
        if (value.r_2) {
          value.r_2 = this.fav_teams.includes(value.r_2.split(' (')[0])
            ? value.r_2.replace(
              value.r_2.split(' (')[0],
              '<mark>' + value.r_2.split(' (')[0] + '</mark>'
            )
            : value.r_2
          value.r_2 = this.fav_teams.includes(
            value.r_2
              .split('(')
              .pop()
              .split(')')[0]
          )
            ? value.r_2.replace(
              value.r_2
                .split('(')
                .pop()
                .split(')')[0],
              '<mark>' +
                  value.r_2
                    .split('(')
                    .pop()
                    .split(')')[0] +
                  '</mark>'
            )
            : value.r_2
          value.r_2 = this.fav_teams.includes(value.r_2_name)
            ? value.r_2.replace(
              value.r_2.split(' (')[0],
              '<mark>' + value.r_2.split(' (')[0] + '</mark>'
            )
            : value.r_2
        }
        return value
      })

      this.filteredGamesCount = filteredGames.length

      const filteredGamesDates = [...new Set(filteredGames.map(x => x.g_date))]
      const games = []
      filteredGamesDates.forEach(goupDate => {
        const filtered = filteredGames.filter(
          value => value.g_date === goupDate
        )
        games.push({
          goupDate: goupDate,
          filtered: filtered
        })
      })
      this.games = games
    }
  }
}
</script>
