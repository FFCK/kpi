<template>
  <div>
    <title-component :text="$t('nav.Chart')" />

    <div class="fixed-top filters">
      <el-button
        class="float-end"
        icon="el-icon-refresh-right"
        plain
        @click="loadCharts"
      />
    </div>

    <charts :key="chartIndex" :chart-data="chartData" />

    <el-backtop />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import Charts from '@/components/Charts.vue'
import { prefsMixin, gamesMixin } from '@/services/mixins'
import { api } from '@/services/api'
import idbs from '@/services/idbStorage'
import {
  ElBacktop, ElButton
} from 'element-plus'

export default {
  name: 'Chart',
  components: {
    ElBacktop,
    ElButton,
    TitleComponent,
    Charts
  },
  mixins: [prefsMixin, gamesMixin],
  data () {
    return {
      chartData: null,
      chartIndex: 0
    }
  },
  mounted () {
    this.loadCharts()
  },
  methods: {
    async loadCharts () {
      await this.getPrefs()
      await this.prefs
      await api.get('/charts/' + this.prefs.event + '/force')
        .then(async result => {
          this.chartData = result.data
          this.chartIndex++
          idbs.dbClear('charts')
          this.chartData.forEach(element => {
            idbs.dbPut('charts', JSON.parse(JSON.stringify(element)))
          })
        }).catch(async error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
            await idbs.dbGetAll('charts')
              .then(result => {
                this.chartData = result
                this.chartIndex++
              })
          }
          // if (error.response) {
          //   // Request made and server responded
          //   console.log(error.response.data)
          //   console.log(error.response.status)
          //   console.log(error.response.headers)
          // } else if (error.request) {
          //   // The request was made but no response was received
          //   console.log(error.request, error.message)
          // } else {
          //   // Something happened in setting up the request that triggered an Error
          //   console.log('Error', error.message)
          // }
        })
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
