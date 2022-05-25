<template>
  <div class="container-fluid">
    <h1>KPI WebSocket Manager (WSM)</h1>
    <div class="row" v-if="events">
      <div>
        Event:
        <select v-model="selectedEvent" :disabled="startedCount > 0" @change="saveEvent">
            <option v-for="event in events" :value="event.id" :key="event.id">{{ event.libelle }}</option>
        </select>
      </div>
    </div>

    <div v-if="selectedEvent">
      <div class="row" v-show="startedCount === 0">
        <div>
          Pitches: {{ pitches }}
          <input type="range" class="form-range" min="1" max="8" v-model.number="pitches" />
        </div>
      </div>

      <div class="row my-3">
        <div class="card text-white bg-secondary col-md-6">
          <div class="card-body">
            <h5 class="card-title">Global</h5>
            <div class="card-text">
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <label class="col-form-label">Url </label>
                </div>
                <div class="col-md-7">
                  <input class="form-control form-control-sm" type="text" placeholder="ws://192.168.0.1:2000" :disabled="startedUrl[0]" v-model.trim="url[0]" @change="changeUrl(0)">
                </div>
                <div class="col-md-auto">
                  <input class="form-check-input" type="checkbox" :disabled="startedUrl[0]" v-model.trim="stomp[0]">
                  <label class="form-check-label ms-1">Stomp</label>
                </div>
              </div>
              <div class="row g-2 align-items-center" v-if="stomp[0]">
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="text" placeholder="login" :disabled="startedUrl[0]" v-model.trim="login[0]">
                </div>
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="password" placeholder="password" :disabled="startedUrl[0]" v-model.trim="password[0]">
                </div>
              </div>
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <button class="btn btn-sm btn-primary" :disabled="!validUrl[0]" v-if="!startedUrl[0]" @click="startUrl(0)">Start</button>
                  <button class="btn btn-sm btn-danger" v-if="validUrl[0] && startedUrl[0]" @click="stopUrl(0)">Stop</button>
                </div>
                <div class="col-md">
                  <div class="bg-light text-secondary fade show p-1 small" role="alert" v-if="message[0]">
                    {{ message[0] }}
                    <button type="button" class="btn-close float-end" aria-label="Close" @click="message[0] = ''"></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card text-white bg-secondary col-md-6">
          <div class="card-body">
            <p :id="'flow-' + 0" class="small text-muted" style="max-height: 120px; overflow-y: auto;"></p>
          </div>
          <div class="card-footer">
            <div class="row g-2 align-items-center">
              <div class="col-md-auto">
                <label class="col-form-label">Topic</label>
              </div>
              <div class="col-md-auto">
                <input type="text" class="form-control form-control-sm" placeholder="lws-minimal" v-model.trim="topic[0]">
              </div>
            </div>
            <div class="row g-2 align-items-center">
              <div class="col-md-12">
                <div class="input-group">
                  <input type="text" class="form-control form-control-sm" placeholder="To send..." v-if="startedUrl[0]" v-model.trim="toSend[0]">
                  <button class="btn btn-sm btn-success" v-if="validUrl[0] && startedUrl[0]" @click="sendMessage(0)">Send</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-1" v-for="(n, index) in pitches" :key="index">
        <div class="card col-md-6">
          <div class="card-body">
            <h5 class="card-title">
              Pitch {{ n }}
              <button class="btn btn-sm btn-dark float-end" v-if="validUrl[n] && startedUrl[n]" @click="saveConnection(n)">Save</button>
            </h5>
            <div class="card-text">
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <label class="col-form-label">Url </label>
                </div>
                <div class="col-md-7">
                  <input class="form-control form-control-sm" type="text" placeholder="ws://192.168.0.1:2000" :disabled="startedUrl[n]" v-model.trim="url[n]" @change="changeUrl(n)">
                </div>
                <div class="col-md-auto">
                  <input class="form-check-input" type="checkbox" :disabled="startedUrl[n]" v-model.trim="stomp[n]">
                  <label class="form-check-label ms-1">Stomp</label>
                </div>
              </div>
              <div class="row g-2 align-items-center" v-if="stomp[n]">
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="text" placeholder="login" :disabled="startedUrl[n]" v-model.trim="login[n]">
                </div>
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="password" placeholder="password" :disabled="startedUrl[n]" v-model.trim="password[n]">
                </div>
              </div>
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <label class="col-form-label">Topic</label>
                </div>
                <div class="col-md-auto">
                  <input type="text" class="form-control form-control-sm" placeholder="lws-minimal" :disabled="startedUrl[n]" v-model.trim="topic[n]">
                </div>
              </div>
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <button class="btn btn-sm btn-primary" :disabled="!validUrl[n]" v-if="!startedUrl[n]" @click="startUrl(n)">Start</button>
                  <button class="btn btn-sm btn-danger" v-if="validUrl[n] && startedUrl[n]" @click="stopUrl(n)">Stop</button>
                </div>
                <div class="col-md">
                  <div class="bg-secondary text-light fade show p-1 small" role="alert" v-if="message[n]">
                    {{ message[n] }}
                    <button type="button" class="btn-close btn-close-white float-end" aria-label="Close" @click="message[n] = ''"></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card col-md-6">
          <div class="card-header">
            <div class="row text-center mb-1">
              <div class="col text-end">{{ game[n]?.equipe1.nom }} <i class="badge bg-secondary">{{ scoreA[n] }}</i></div>
              <div class="col text-start"><i class="badge bg-secondary">{{ scoreB[n] }}</i> {{ game[n]?.equipe2.nom }}</div>
            </div>
            <div class="text-center">
              <div class="btn btn-sm btn-secondary" v-if="startedUrl[n]">{{ period[n] }}</div>
              <div
                :class="{btn: true, 'btn-sm': true, 'fw-bold': true, 'btn-success': statutChrono[n], 'btn-warning': !statutChrono[n]}"
                v-if="startedUrl[n]"
                >{{ tpsJeu[n] }}</div>
              <div class="badge bg-info text-dark" v-if="startedUrl[n]">{{ posses[n] }}</div>
            </div>
          </div>
          <div class="card-body">
            <p :id="'flow-' + n" class="small text-muted" style="max-height: 120px; overflow-y: auto;"></p>
          </div>
          <div class="card-footer row g-1 align-items-center">
            <div class="col-md-7">
              <div class="input-group">
                <input type="text" class="form-control form-control-sm" placeholder="To send..." v-if="startedUrl[n]" v-model.trim="toSend[n]">
                <button class="btn btn-sm btn-success" v-if="validUrl[n] && startedUrl[n]" @click="sendMessage(n)">Send</button>
              </div>
            </div>
            <div class="col-md-auto">
              <button class="btn btn-sm btn-dark" v-if="validUrl[n] && startedUrl[n]" @click="setTeams(n)">Set Teams</button>
            </div>
            <div class="col-md-auto">
              <button class="btn btn-sm btn-dark" v-if="validUrl[n] && startedUrl[n]" @click="sync(n)">Sync</button>
            </div>
          </div>
        </div>
      </div>
      <p class="text-muted">
        <i>wss://javascript.info/article/websocket/demo/hello</i>
        <br>
        <i>ws://localhost:7681 lws-minimal</i>
        <br>
        <i>ws://localhost:61614</i>
      </p>
    </div>
  </div>
</template>

<script>
import routeMixin from '@/mixins/routeMixin'
import gameMixin from '@/mixins/gameMixin'
import wsMixin from '@/mixins/wsMixin'

export default {
  name: 'Management',
  mixins: [routeMixin, gameMixin, wsMixin],
  data () {
    return {
    }
  },
  methods: {
  },
  mounted () {
    this.loadConnections()
  },
  created () {
    this.fetchEvents()
    this.loadEvent()
  }
}
</script>
