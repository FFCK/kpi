<template>
  <div class="container-fluid" v-if="prefs">
    <h1>KPI WebSocket Manager (WSM)</h1>
    <div class="row text-center" v-if="events">
      <div>
        {{ $t("Event") }}
        <select v-model="selectedEvent" :disabled="startedCount > 0" @change="changeEvent">
            <option v-for="event in events" :value="event.id" :key="event.id">{{ event.id }} - {{ event.libelle }}</option>
        </select>
      </div>
    </div>

    <div v-if="prefs.selectedEvent > 0">
      <div class="d-flex justify-content-around" v-show="startedCount === 0">
        <div class="row">
          <div class="col">{{ $t("Pitches") }}: {{ pitches }}</div>
          <input type="range" class="form-range col" min="1" max="8" v-model.number="pitches" @change="changePitches" />
        </div>
        <div class="form-check form-switch">
          <label class="form-check-label">Database sync</label>
          <input class="form-check-input" type="checkbox" role="button" :checked="prefs.databaseSync" @change="changeDatabaseSync">
        </div>
        <!-- <div>
          <div class="btn btn-sm btn-secondary" @click="savePrefs">
            <i class="bi bi-save2" title="Save"></i> Prefs
          </div>
        </div> -->
      </div>
      <hr>

      <div class="my-3" v-show="!broker && !startedUrl[0]">
        <button class="btn btn-sm btn-outline-dark" @click="broker = !broker">KPI Broker <i class="bi bi-caret-down-fill"></i></button>
      </div>
      <div class="row my-3" v-show="broker || startedUrl[0]">
        <div class="card text-white bg-secondary col-md-6">
          <div class="card-body">
            <h5 class="card-title">
              <button class="btn btn-sm btn-outline-dark" v-if="!startedUrl[0]" @click="broker = !broker">KPI Broker <i class="bi bi-caret-up-fill"></i></button>
              <button class="btn btn-sm btn-outline-dark" v-else>KPI Broker</button>
              <button class="btn btn-sm btn-dark float-end m-1" v-if="startedUrl[0]" @click="generateJson()" title="Generate Json"><i class="bi bi-filetype-json"></i></button>
              <button class="btn btn-sm btn-dark float-end m-1" v-if="validUrl[0]" @click="saveConnection(0)"><i class="bi bi-save2" title="Save"></i></button>
            </h5>
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
                  <label class="col-form-label">Topic</label>
                </div>
                <div class="col-md-auto">
                  <input type="text" class="form-control form-control-sm" placeholder="lws-minimal" v-model.trim="topic[0]">
                </div>
              </div>
              <div class="row g-2 align-items-center mt-1">
                <div class="col-md-auto">
                  <button class="btn btn-sm btn-primary" :disabled="!validUrl[0]" v-if="!startedUrl[0]" @click="startUrl(0)"><i class="bi bi-play-fill"></i></button>
                  <button class="btn btn-sm btn-danger" v-if="validUrl[0] && startedUrl[0]" @click="stopUrl(0)"><i class="bi bi-stop-fill"></i></button>
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
            <p :id="'flow-' + 0" class="small text-white" style="max-height: 120px; overflow-y: auto;"></p>
          </div>
          <div class="card-footer">
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

      <div class="my-3" v-show="!faker && !startedUrl[20]">
        <button class="btn btn-sm btn-outline-secondary" @click="faker = !faker">Faker <i class="bi bi-caret-down-fill"></i></button>
      </div>
      <div class="row my-3" v-show="faker || startedUrl[20]">
        <div class="card text-white bg-dark col-md-6">
          <div class="card-body">
            <h5 class="card-title">
              <button class="btn btn-sm btn-outline-secondary" v-if="!startedUrl[20]" @click="faker = !faker">Faker <i class="bi bi-caret-up-fill"></i></button>
              <button class="btn btn-sm btn-outline-secondary" v-else>Faker</button>
              <button class="btn btn-sm btn-secondary float-end" v-if="validUrl[20]" @click="saveConnection(20)"><i class="bi bi-save2" title="Save"></i></button>
            </h5>
            <div class="card-text">
              <div class="row g-2 align-items-center">
                <div class="col-md-auto">
                  <label class="col-form-label">Url </label>
                </div>
                <div class="col-md-7">
                  <input class="form-control form-control-sm" type="text" placeholder="ws://192.168.0.1:2000" :disabled="startedUrl[20]" v-model.trim="url[20]" @change="changeUrl(20)">
                </div>
                <div class="col-md-auto">
                  <input class="form-check-input" type="checkbox" :disabled="startedUrl[20]" v-model.trim="stomp[20]">
                  <label class="form-check-label ms-1">Stomp</label>
                </div>
              </div>
              <div class="row g-2 align-items-center" v-if="stomp[20]">
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="text" placeholder="login" :disabled="startedUrl[20]" v-model.trim="login[20]">
                </div>
                <div class="col-md-6">
                  <input class="form-control form-control-sm" type="password" placeholder="password" :disabled="startedUrl[20]" v-model.trim="password[20]">
                </div>
              </div>
              <div class="row g-2 align-items-center mt-1">
                <div class="col-md-auto">
                  <button class="btn btn-sm btn-primary" :disabled="!validUrl[20]" v-if="!startedUrl[20]" @click="startUrl(20)"><i class="bi bi-play-fill"></i></button>
                  <button class="btn btn-sm btn-danger" v-if="validUrl[20] && startedUrl[20]" @click="stopUrl(20)"><i class="bi bi-stop-fill"></i></button>
                </div>
                <div class="col-md">
                  <div class="bg-light text-secondary fade show p-1 small" role="alert" v-if="message[20]">
                    {{ message[20] }}
                    <button type="button" class="btn-close float-end" aria-label="Close" @click="message[20] = ''"></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card text-white bg-dark col-md-6">
          <div class="card-header">
            <p :id="'flow-' + 20" class="small text-muted" style="max-height: 120px; overflow-y: auto;"></p>
          </div>
          <div class="card-body">
            <button class="badge bg-secondary m-1" @click="fake(1)">Ready</button>
            <button class="badge bg-secondary m-1" @click="fake(2)">Success</button>
            <button class="badge bg-secondary m-1" @click="fake(3)">Running</button>
            <button class="badge bg-secondary m-1" @click="fake(4)">Quit</button>
            <button class="badge bg-secondary m-1" @click="fake(41)">Desactivate players</button>
            <button class="badge bg-secondary m-1" @click="fake(42)">Reactivate players</button>
            <button class="badge bg-secondary m-1" @click="fake(5)">M1</button>
            <button class="badge bg-secondary m-1" @click="fake(6)">M2</button>
            <button class="badge bg-secondary m-1" @click="fake(7)">P1</button>
            <button class="badge bg-secondary m-1" @click="fake(8)">P2</button>
            <button class="badge bg-secondary m-1" @click="fake(9)">09:41.71 On</button>
            <button class="badge bg-secondary m-1" @click="fake(10)">08:07.66 Off</button>
            <button class="badge bg-secondary m-1" @click="fake(101)">00:08.66 On</button>
            <button class="badge bg-secondary m-1" @click="fake(102)">10:00.00 On</button>
            <button class="badge bg-secondary m-1" @click="fake(103)">09:59.99 On</button>
            <button class="badge bg-secondary m-1" @click="fake(111)">Poss 60.00</button>
            <button class="badge bg-secondary m-1" @click="fake(11)">Poss 12.41</button>
            <button class="badge bg-secondary m-1" @click="fake(12)">Poss 6.71</button>
            <button class="badge bg-secondary m-1" @click="fake(121)">Poss 0.00</button>
            <button class="badge bg-secondary m-1" @click="fake(13)">A2</button>
            <button class="badge bg-secondary m-1" @click="fake(14)">B4</button>
            <button class="badge bg-secondary m-1" @click="fake(15)">A3 pen 2</button>
            <button class="badge bg-secondary m-1" @click="fake(151)">B5 pen 1</button>
            <button class="badge bg-secondary m-1" @click="fake(16)">Goal #2A</button>
            <button class="badge bg-secondary m-1" @click="fake(161)">2d Goal #2A</button>
            <button class="badge bg-secondary m-1" @click="fake(17)">Yellow #7B</button>
            <button class="badge bg-secondary m-1" @click="fake(171)">Red #7B</button>
            <button class="badge bg-secondary m-1" @click="fake(172)">-Red #7B</button>
          </div>
          <div class="card-footer">
            <div class="row g-2 align-items-center">
              <div class="col-md-auto">
                <label class="col-form-label">Topic</label>
              </div>
              <div class="col-md-auto">
                <input type="text" class="form-control form-control-sm" placeholder="/topic/name" v-model.trim="topic[20]">
              </div>
            </div>
            <div class="row g-2 align-items-center">
              <div class="col-md-12">
                <div class="input-group">
                  <input type="text" class="form-control form-control-sm" placeholder="To send..." v-if="startedUrl[20]" v-model.trim="toSend[20]">
                  <button class="btn btn-sm btn-success" v-if="validUrl[20] && startedUrl[20]" @click="sendMessage(20)">Send</button>
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
              {{ $t("Pitch") }} {{ n }}
              <button class="btn btn-sm btn-dark float-end" v-if="validUrl[n]" @click="saveConnection(n)"><i class="bi bi-save2" title="Save"></i></button>
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
                  <button class="btn btn-sm btn-primary" :disabled="!validUrl[n]" v-if="!startedUrl[n]" @click="startUrl(n)"><i class="bi bi-play-fill"></i></button>
                  <button class="btn btn-sm btn-danger" v-if="validUrl[n]" @click="stopUrl(n)"><i class="bi bi-stop-fill"></i></button>
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
              <div class="col text-end">
                <i v-for="i in penA[n] || 0" :key="i" class="bi bi-circle-fill text-warning" />
                {{ game[n]?.equipe1.nom }}
                <i class="badge bg-secondary">{{ scoreA[n] }}</i>
              </div>
              <div class="col text-start">
                <i class="badge bg-secondary">{{ scoreB[n] }}</i>
                {{ game[n]?.equipe2.nom }}
                <i v-for="i in penB[n] || 0" :key="i" class="bi bi-circle-fill text-warning" />
              </div>
            </div>
            <div class="text-center">
              <div class="badge bg-secondary" v-if="startedUrl[n]">{{ period[n] }}</div>
              <div
                :class="{badge: true, 'fw-bold': true, 'bg-success': statutChrono[n], 'bg-warning': !statutChrono[n]}"
                v-if="startedUrl[n]"
                >{{ tpsJeuFormated[n] }}</div>
              <div class="badge rounded-pill bg-info text-dark ms-1" v-if="startedUrl[n]">{{ possesFormated[n] }}</div>
            </div>
            <div class="text-center">
              <div class="float-start badge bg-secondary">{{ game[n]?.heure }}</div>
              <div
                :class="{
                  badge: true,
                  'text-dark': true,
                  'bg-secondary': statutMatch[n] === 'ATT',
                  'bg-primary': statutMatch[n] === 'ON',
                  'bg-success': statutMatch[n] === 'END',
                }"
                v-if="startedUrl[n]"
              >{{ statutMatch[n] }}</div>
              <div class="float-end badge bg-secondary">{{ game[n]?.numero_ordre ? '#' + game[n]?.numero_ordre : '' }}</div>
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
              <button class="btn btn-sm btn-dark" v-if="validUrl[n] && startedUrl[n]" @click="syncRequest(n)">Sync</button>
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
import prefsMixin from '@/mixins/prefsMixin'

export default {
  name: 'Management',
  mixins: [routeMixin, gameMixin, wsMixin, prefsMixin],
  data () {
    return {
      pitches: 4,
      selectedEvent: 0
    }
  },
  methods: {
    changeEvent () {
      this.prefs.selectedEvent = this.selectedEvent
      this.savePrefs()
    },
    changePitches () {
      this.prefs.pitches = this.pitches
      this.savePrefs()
    },
    changeDatabaseSync () {
      this.prefs.databaseSync = !this.prefs.databaseSync
      this.savePrefs()
    },
    fake (i) {
      if (!this.startedUrl[20]) {
        console.log('Faker not started!')
        return
      }
      switch (i) {
        case 1:
          this.sendMessage(20, '', '/game/ready-to-start-game')
          break
        case 2:
          this.sendMessage(20, '{"success": true, "message": ""}', '/game/set-teams')
          break
        case 3:
          this.sendMessage(20, '{"matchState": "MATCH_RUNNING"}', '/game/game-state')
          break
        case 4:
          this.sendMessage(20, '{"matchState": "QUIT_MATCH"}', '/game/game-state')
          break
        case 41:
          this.sendMessage(20, '{"teamGameHome":{"playersGame":[{"idPlayer":2,"score":"0","faults":0,"shirtNumber":"1","selected":true,"card":"NONE","isBlink":false},{"idPlayer":3,"score":"0","faults":0,"shirtNumber":"2","selected":true,"card":"NONE","isBlink":false},{"idPlayer":4,"score":"0","faults":0,"shirtNumber":"3","selected":false,"card":"NONE","isBlink":false},{"idPlayer":5,"score":"0","faults":0,"shirtNumber":"4","selected":false,"card":"NONE","isBlink":false},{"idPlayer":6,"score":"0","faults":0,"shirtNumber":"5","selected":true,"card":"NONE","isBlink":false},{"idPlayer":7,"score":"0","faults":0,"shirtNumber":"6","selected":true,"card":"NONE","isBlink":false},{"idPlayer":8,"score":"0","faults":0,"shirtNumber":"7","selected":true,"card":"NONE","isBlink":false},{"idPlayer":9,"score":"0","faults":0,"shirtNumber":"8","selected":true,"card":"NONE","isBlink":false}],"idBlinkPlayer":-1,"listPenaltyGames":[{"order":5,"penaltyTime":120000,"chronoNamePenalty":"PEN_H1","penaltyInitTime":120000,"idPlayer":-2,"typePenalty":"GREEN_CARD_MINOR"}],"listCoachGame":[{"idCoach":1,"faults":0,"selected":true,"card":"NONE","nameCoach":"Coach "}]},"teamGameGuest":{"playersGame":[{"idPlayer":2,"score":"0","faults":0,"shirtNumber":"1","selected":true,"card":"NONE","isBlink":false},{"idPlayer":3,"score":"0","faults":0,"shirtNumber":"2","selected":true,"card":"NONE","isBlink":false},{"idPlayer":4,"score":"0","faults":0,"shirtNumber":"4","selected":true,"card":"NONE","isBlink":false},{"idPlayer":5,"score":"0","faults":0,"shirtNumber":"5","selected":false,"card":"NONE","isBlink":false},{"idPlayer":6,"score":"0","faults":0,"shirtNumber":"6","selected":false,"card":"NONE","isBlink":false},{"idPlayer":7,"score":"0","faults":0,"shirtNumber":"7","selected":false,"card":"NONE","isBlink":false},{"idPlayer":8,"score":"0","faults":0,"shirtNumber":"9","selected":true,"card":"NONE","isBlink":false}],"idBlinkPlayer":-1,"listPenaltyGames":[],"listCoachGame":[{"idCoach":1,"faults":0,"selected":true,"card":"NONE","nameCoach":"Coach "}]}}', '/game/team-game')
          break
        case 42:
          this.sendMessage(20, '{"teamGameHome":{"playersGame":[{"idPlayer":2,"score":"0","faults":0,"shirtNumber":"1","selected":true,"card":"NONE","isBlink":false},{"idPlayer":3,"score":"0","faults":0,"shirtNumber":"2","selected":true,"card":"NONE","isBlink":false},{"idPlayer":4,"score":"0","faults":0,"shirtNumber":"3","selected":true,"card":"NONE","isBlink":false},{"idPlayer":5,"score":"0","faults":0,"shirtNumber":"4","selected":true,"card":"NONE","isBlink":false},{"idPlayer":6,"score":"0","faults":0,"shirtNumber":"5","selected":true,"card":"NONE","isBlink":false},{"idPlayer":7,"score":"0","faults":0,"shirtNumber":"6","selected":true,"card":"NONE","isBlink":false},{"idPlayer":8,"score":"0","faults":0,"shirtNumber":"7","selected":true,"card":"NONE","isBlink":false},{"idPlayer":9,"score":"0","faults":0,"shirtNumber":"8","selected":true,"card":"NONE","isBlink":false}],"idBlinkPlayer":-1,"listPenaltyGames":[{"order":5,"penaltyTime":120000,"chronoNamePenalty":"PEN_H1","penaltyInitTime":120000,"idPlayer":-2,"typePenalty":"GREEN_CARD_MINOR"}],"listCoachGame":[{"idCoach":1,"faults":0,"selected":true,"card":"NONE","nameCoach":"Coach "}]},"teamGameGuest":{"playersGame":[{"idPlayer":2,"score":"0","faults":0,"shirtNumber":"1","selected":true,"card":"NONE","isBlink":false},{"idPlayer":3,"score":"0","faults":0,"shirtNumber":"2","selected":true,"card":"NONE","isBlink":false},{"idPlayer":4,"score":"0","faults":0,"shirtNumber":"4","selected":true,"card":"NONE","isBlink":false},{"idPlayer":5,"score":"0","faults":0,"shirtNumber":"5","selected":true,"card":"NONE","isBlink":false},{"idPlayer":6,"score":"0","faults":0,"shirtNumber":"6","selected":true,"card":"NONE","isBlink":false},{"idPlayer":7,"score":"0","faults":0,"shirtNumber":"7","selected":true,"card":"NONE","isBlink":false},{"idPlayer":8,"score":"0","faults":0,"shirtNumber":"9","selected":true,"card":"NONE","isBlink":false}],"idBlinkPlayer":-1,"listPenaltyGames":[],"listCoachGame":[{"idCoach":1,"faults":0,"selected":true,"card":"NONE","nameCoach":"Coach "}]}}', '/game/team-game')
          break
        case 5:
          this.sendMessage(20, '{"currentPeriod": 1, "prolongation": false, "nbPeriodInGame": 4}', '/game/period')
          break
        case 6:
          this.sendMessage(20, '{"currentPeriod": 2, "prolongation": false, "nbPeriodInGame": 4}', '/game/period')
          break
        case 7:
          this.sendMessage(20, '{"currentPeriod": 3, "prolongation": true, "nbPeriodInGame": 4}', '/game/period')
          break
        case 8:
          this.sendMessage(20, '{"currentPeriod": 4, "prolongation": true, "nbPeriodInGame": 4}', '/game/period')
          break
        case 9:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "TPS-JEU","value": 581712,"initValue": 600000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 10:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "TPS-JEU","value": 487660,"initValue": 600000,"chronoMode": "COUNTDOWN","started": false}', '/game/chrono')
          break
        case 101:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "TPS-JEU","value": 8660,"initValue": 600000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 102:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "TPS-JEU","value": 600000,"initValue": 600000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 103:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "TPS-JEU","value": 599990,"initValue": 600000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 111:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "POSSES","value": 59990,"initValue": 60000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 11:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "POSSES","value": 12412,"initValue": 60000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 12:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "POSSES","value": 6712,"initValue": 60000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 121:
          this.sendMessage(20, '{"idChrono": 0,"chronoName": "POSSES","value": 0,"initValue": 60000,"chronoMode": "COUNTDOWN","started": true}', '/game/chrono')
          break
        case 13:
          this.sendMessage(20, '{"typeTeam": "HOME","score": "2","nbPenalities": 0,"fault": 0,"timeOut": 0}', '/game/data-game')
          break
        case 14:
          this.sendMessage(20, '{"typeTeam": "GUEST","score": "4","nbPenalities": 0,"fault": 0,"timeOut": 0}', '/game/data-game')
          break
        case 15:
          this.sendMessage(20, '{"typeTeam": "HOME","score": "3","nbPenalities": 2,"fault": 0,"timeOut": 0}', '/game/data-game')
          break
        case 151:
          this.sendMessage(20, '{"typeTeam": "GUEST","score": "5","nbPenalities": 1,"fault": 0,"timeOut": 0}', '/game/data-game')
          break
        case 16:
          this.sendMessage(20, '{"type": "HOME","idPlayer": 3,"score": "1","fault": 0,"card": "NONE"}', '/game/player-info')
          break
        case 161:
          this.sendMessage(20, '{"type": "HOME","idPlayer": 3,"score": "2","fault": 0,"card": "NONE"}', '/game/player-info')
          break
        case 17:
          this.sendMessage(20, '{"type": "GUEST","idPlayer": 5,"score": "0","fault": 0,"card": "YELLOW"}', '/game/player-info')
          break
        case 171:
          this.sendMessage(20, '{"type": "GUEST","idPlayer": 5,"score": "0","fault": 0,"card": "RED"}', '/game/player-info')
          break
        case 172:
          this.sendMessage(20, '{"type": "GUEST","idPlayer": 5,"score": "0","fault": 0,"card": "NONE"}', '/game/player-info')
          break
        default:
          break
      }
    }
  },
  mounted () {
    this.loadConnections()
  },
  created () {
    this.fetchEvents()
  }
}
</script>
