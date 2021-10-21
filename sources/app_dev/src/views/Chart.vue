<template>
  <div class="container-fluid">
    <div class="filters">
      <div class="row">
        <div class="col">
          <i
            role="button"
            class="bi bi-caret-left-square-fill me-2"
            @click="changePage('Games')"
          />
        </div>
        <div class="col text-end">
          <div class="text-nowrap">
            <button
              v-show="visibleButton"
              class="btn btn-light m-1"
              @click="loadCharts"
            >
              <i class="bi bi-arrow-clockwise"></i>
            </button>
            <i
              role="button"
              class="float-end bi bi-caret-right-square-fill ms-2"
              @click="changePage('About')"
            />
          </div>
        </div>
      </div>
    </div>

    <charts :key="chartIndex" :chart-data="chartData" :show-flags="showFlags" />

    <el-backtop />
  </div>
</template>

<script>
import Charts from '@/components/Charts.vue'
import { prefsMixin, gamesMixin } from '@/mixins/mixins'
import publicApi from '@/network/publicApi'
import idbs from '@/services/idbStorage'
import { ElBacktop } from 'element-plus'
import statusMixin from '@/mixins/statusMixin'

export default {
  name: 'Chart',
  components: {
    ElBacktop,
    Charts
  },
  mixins: [prefsMixin, gamesMixin, statusMixin],
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
