<template>
  <div class="container-sm">
    <title-component :text="$t('nav.Games')" />

    <button class="btn btn-primary btn-sm" @click="loadGames">Reload</button>

    <div>count: {{ gamesCount }}</div>
    <table v-if="games">
      <thead>
        <tr>
          <th>#</th>
          <th>date</th>
          <th>time</th>
          <th>pitch</th>
          <th>A</th>
          <th>B</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="game in games" :key="game.g_id">
          <td>{{ game.g_number }}</td>
          <td>{{ game.g_date }}</td>
          <td>{{ game.g_time }}</td>
          <td>{{ game.g_pitch }}</td>
          <td>{{ game.t_a_label }}</td>
          <td>{{ game.t_b_label }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import { prefsMixin } from '@/services/mixins'
import { api } from '@/services/api'
import idbs from '@/services/idbStorage'
import Games from '@/store/models/Games'

export default {
  name: 'Games',
  mixins: [prefsMixin],
  components: {
    TitleComponent
  },
  computed: {
    games () {
      return Games.all()
    },
    gamesCount () {
      return Games.query().count()
    }
  },
  methods: {
    async getGames () {
      if (Games.query().count() === 0) {
        const result = await idbs.dbGetAll('games')
        if (result.length > 0) {
          Games.insertOrUpdate({
            data: result
          })
        } else {
          this.loadGames()
        }
      }
    },
    async loadGames () {
      await api.get('/games/' + this.prefs.event)
        .then(result => {
          Games.deleteAll()
          Games.insertOrUpdate({
            data: result.data
          })
          idbs.dbClear('games')
          result.data.forEach(element => {
            idbs.dbPut('games', element)
          })
        }).catch(error => {
          console.log('Erreur:', error)
        })
    }
  },
  created () {
    this.getGames()
  }
}
</script>
