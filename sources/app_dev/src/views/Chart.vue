<template>
  <div class="container-fluid">
    <div class="filters mb-1">
      <div class="input-group input-group-sm">
        <div class="btn btn-outline-secondary" @click="changePage('Games')">
          <i class="bi bi-caret-left-square-fill" />
        </div>
        <input
          type="text"
          class="form-control text-center"
          readonly
          :value="$t('nav.Chart')"
        />
        <button
          :disabled="!visibleButton"
          class="btn btn-secondary"
          @click="loadCharts"
        >
          <i class="bi bi-arrow-clockwise"></i>
        </button>
        <div class="btn btn-outline-secondary" @click="changePage('About')">
          <i class="bi bi-caret-right-square-fill" />
        </div>
      </div>
    </div>

    <charts :key="chartIndex" :chart-data="chartData" :show-flags="showFlags" />

    <el-backtop />
  </div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import gamesMixin from '@/mixins/gamesMixin'
import statusMixin from '@/mixins/statusMixin'
import Charts from '@/components/Charts.vue'
import publicApi from '@/network/publicApi'
import idbs from '@/services/idbStorage'
import { ElBacktop } from 'element-plus'

export default {
  name: 'Chart',
  mixins: [prefsMixin, gamesMixin, statusMixin],
  components: {
    ElBacktop,
    Charts
  },
  data () {
    return {
      chartData: null,
      chartIndex: 0,
      status: {},
      visibleButton: true,
      showFlags: true
    }
  },
  mounted () {
    this.loadCharts()
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    },
    async loadCharts () {
      await this.getPrefs()
      await this.prefs
      this.showFlags = this.prefs.show_flags
      await idbs.dbGetAll('charts').then(result => {
        this.chartData = result
        this.chartIndex++
      })
      if (!(await this.checkOnline())) {
        return
      }
      this.visibleButton = false
      setTimeout(() => {
        this.visibleButton = true
      }, 3000)
      await publicApi
        .getCharts(this.prefs.event)
        .then(async result => {
          this.chartData = result.data
          this.chartIndex++
          idbs.dbClear('charts')
          this.chartData.forEach(element => {
            idbs.dbPut('charts', JSON.parse(JSON.stringify(element)))
          })
        })
        .catch(async error => {
          if (error.response) {
            console.log(error.response.data)
            console.log(error.response.status)
            console.log(error.response.headers)
          } else if (error.request) {
            console.log(error.request, error.message)
          } else {
            console.log('Error', error.message)
            if (error.message === 'Network Error') {
              console.log('Offline !')
            }
          }
        })
    }
  }
}
</script>
