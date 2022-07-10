<template>
  <div class="container-fluid" v-if="prefs">
    <h1>WebSocket Faker</h1>
    <div class="row text-center" v-if="events">
      <div>
        {{ $t("Event") }}
        <select v-model="event" :disabled="startedCount > 0" @change="changeEvent">
            <option v-for="event in events" :value="event.id" :key="event.id">{{ event.id }} - {{ event.libelle }}</option>
        </select>
        <div v-if="event > 0 && !socketFaker">
          <button class="btn btn-success" @click="connect">Connect</button>
        </div>
      </div>
    </div>

    <div v-if="socketFaker">
      <div class="row mt-4">
        <div class="col text-center" v-for="(n, index) in 4" :key="index">
          Pitch {{ n }}
          <button class="btn btn-success" @click="fake(n)" v-if="!faker[n]">
            <i class="bi bi-play-fill"></i>
          </button>
          <button class="btn btn-danger" @click="stop(n)" v-if="faker[n]">
            <i class="bi bi-stop-fill"></i>
          </button>
          <button class="btn btn-primary" @click="goal(n)" v-if="faker[n]">
            Goal
          </button>
          <button class="btn btn-primary" @click="green(n)" v-if="faker[n]">
            Green
          </button>
          <button class="btn btn-primary" @click="yellow(n)" v-if="faker[n]">
            Yellow
          </button>
          <button class="btn btn-primary" @click="red(n)" v-if="faker[n]">
            Red
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import routeMixin from '@/mixins/routeMixin'
import gameMixin from '@/mixins/gameMixin'
import wsMixin from '@/mixins/wsMixin'
import liveApi from '@/network/liveApi'

export default {
  name: 'Faker',
  mixins: [routeMixin, gameMixin, wsMixin],
  data () {
    return {
      event: 0,
      network: null,
      socketFaker: null,
      faker: [],
      periods: {
        0: 'M1',
        1: 'M2',
        2: 'P1'
      }
    }
  },
  methods: {
    async changeEvent () {
      if (this.socketFaker) {
        await this.socketFaker.close()
        this.socketFaker = null
      }
    },
    connect () {
      this.fetchNetwork()
    },
    async fetchNetwork () {
      if (this.event > 0) {
        await liveApi
          .getEventNetwork(this.event)
          .then(resultEventNetwork => {
            if (resultEventNetwork.data) {
              this.network = resultEventNetwork.data.network
              this.wsConnect()
            } else {
              console.log('No network')
            }
          })
          .catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            } else {
              console.log(error)
            }
          })
      }
    },
    wsConnect () {
      if (this.network.global.topic !== '') {
        this.socketFaker = new WebSocket(this.network.global.url, this.network.global.topic)
      } else {
        this.socketFaker = new WebSocket(this.network.global.url)
      }

      this.socketFaker.onopen = (e) => {
        console.log('ws open')
      }

      this.socketFaker.onmessage = (event) => {
        this.wsProcess(JSON.parse(event.data))
      }

      this.socketFaker.onclose = (event) => {
        if (event.wasClean) {
          console.log('Websocket Close.')
        } else {
          console.log('Websocket Died.')
        }
      }

      this.socketFaker.onerror = (error) => {
        console.log(error)
      }
    },
    fakeBroadcast (event, pitch, topic, value, id = 0) {
      const obj = {
        p: event + '_' + pitch,
        t: topic.substr(1),
        v: value
      }
      this.socketFaker.send(JSON.stringify(obj))
    },
    incrementTopic (topic, n, max) {
      if (this.faker[n][topic] >= max) {
        this.faker[n][topic] = 0
      } else {
        this.faker[n][topic]++
      }
      this.fakeBroadcast(this.event, n, '/' + topic, this.faker[n][topic])
    },
    incrementPeriod (topic, n, max) {
      if (this.faker[n][topic] >= max) {
        this.faker[n][topic] = 0
      } else {
        this.faker[n][topic]++
      }
      this.fakeBroadcast(this.event, n, '/' + topic, this.periods[this.faker[n][topic]])
    },
    decrementTimer (topic, n, max) {
      let timer = ''
      if (this.faker[n][topic] <= 0) {
        this.faker[n][topic] = max
      } else {
        this.faker[n][topic] -= 100
      }
      if (this.faker[n][topic] >= 10000) {
        timer = this.msToMMSS(this.faker[n][topic], true, false)
      } else {
        timer = this.msToMMSS(this.faker[n][topic], false, true)
      }
      this.fakeBroadcast(this.event, n, '/' + topic, { time: timer, run: true })
    },
    decrementPosses (topic, n, max) {
      let timer = ''
      if (this.faker[n][topic] <= 0) {
        this.faker[n][topic] = max
      } else {
        this.faker[n][topic] -= 100
      }
      if (this.faker[n][topic] >= 10000) {
        timer = this.msToSS(this.faker[n][topic], true, false)
      } else {
        timer = this.msToSS(this.faker[n][topic], false, true)
      }
      this.fakeBroadcast(this.event, n, '/' + topic, timer)
    },
    fake (n) {
      this.faker[n] = {}
      this.faker[n].scoreA = 0
      this.faker[n].ivScoreA = setInterval(this.incrementTopic, 3500, 'scoreA', n, 12)
      this.faker[n].scoreB = 0
      this.faker[n].ivScoreB = setInterval(this.incrementTopic, 2800, 'scoreB', n, 9)
      this.faker[n].penA = 0
      this.faker[n].ivPenA = setInterval(this.incrementTopic, 4300, 'penA', n, 2)
      this.faker[n].penB = 0
      this.faker[n].ivPenB = setInterval(this.incrementTopic, 6600, 'penB', n, 2)
      this.faker[n].period = 0
      this.faker[n].ivPeriod = setInterval(this.incrementPeriod, 8000, 'period', n, 2)
      this.faker[n].chrono = 30000
      this.faker[n].ivChrono = setInterval(this.decrementTimer, 100, 'chrono', n, 30000)
      this.faker[n].posses = 20000
      this.faker[n].ivPosses = setInterval(this.decrementPosses, 100, 'posses', n, 20000)
      this.faker[n].ivGreen = setInterval(this.green, 60000, n)
      setTimeout(() => {
        this.goal(n)
        this.faker[n].ivGoal = setInterval(this.goal, 60000, n)
      }, 15000)
      setTimeout(() => {
        this.yellow(n)
        this.faker[n].ivYellow = setInterval(this.yellow, 60000, n)
      }, 30000)
      setTimeout(() => {
        this.red(n)
        this.faker[n].ivRed = setInterval(this.red, 60000, n)
      }, 45000)
    },
    stop (n) {
      clearInterval(this.faker[n].ivScoreA)
      clearInterval(this.faker[n].ivScoreB)
      clearInterval(this.faker[n].ivPenA)
      clearInterval(this.faker[n].ivPenB)
      clearInterval(this.faker[n].ivPeriod)
      clearInterval(this.faker[n].ivChrono)
      clearInterval(this.faker[n].ivPosses)
      clearInterval(this.faker[n].ivGreen)
      clearInterval(this.faker[n].ivGoal)
      clearInterval(this.faker[n].ivYellow)
      clearInterval(this.faker[n].ivRed)
      this.faker[n] = null
    },
    goal (n) {
      this.fakeBroadcast(this.event, n, '/evt', {
        evt: 'B',
        team: 'A',
        matric: 252982,
        num: 4,
        nom: 'LE FLOCH DECORCHEMONT',
        prenom: 'Pierre-Antoine',
        cap: 'C'
      })
    },
    green (n) {
      this.fakeBroadcast(this.event, n, '/evt', {
        evt: 'V',
        team: 'B',
        matric: 62713,
        num: 10,
        nom: 'BRACKEZ',
        prenom: 'Virginie',
        cap: 'E'
      })
    },
    yellow (n) {
      this.fakeBroadcast(this.event, n, '/evt', {
        evt: 'J',
        team: 'A',
        matric: 25225,
        num: 10,
        nom: 'BELAT',
        prenom: 'Patrice',
        cap: ''
      })
    },
    red (n) {
      this.fakeBroadcast(this.event, n, '/evt', {
        evt: 'R',
        team: 'B',
        matric: 9926,
        num: 5,
        nom: 'JORDANO',
        prenom: 'Eric',
        cap: ''
      })
    }
  },
  mounted () {
  },
  created () {
    this.fetchEvents()
  }
}
</script>
