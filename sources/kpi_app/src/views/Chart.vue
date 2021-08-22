<template>
  <div>
    <title-component :text="$t('nav.Chart')" />

    <h1>
      Charts
      <el-button
        class="float-end"
        icon="el-icon-refresh-right"
        plain
        @click="loadCharts"
      />
    </h1>

    <charts
      :key="chartIndex"
      :chart-data="chartData"
    />

    <el-backtop />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import Charts from '@/components/Charts.vue'
import { prefsMixin, gamesMixin } from '@/services/mixins'
import { api } from '@/services/api'

export default {
  name: 'Chart',
  components: {
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
        }).catch(error => {
          console.log('Erreur:', error)
        })
    }
  }
}
</script>
