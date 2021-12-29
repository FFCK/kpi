<template>
  <div class="mb-3">
    <Title
      :text="$t('Scrutineering.Scrutineering')"
      icon="bi bi-clipboard-check"
    />

    <div v-if="user">
      <div v-if="authorized && user.profile <= 3" class="container-fluid mb-5">
        <i
          role="button"
          class="float-start bi bi-caret-left-square-fill me-1 btn btn-secondary"
          @click="$router.push({ name: 'Login' })"
        />
        <team-selector @changeTeam="loadPlayers" />
        <!-- <i
          role="button"
          class="if-portrait float-end bi bi-phone-landscape ms-2"
        /> -->
        <div v-if="prefs.scr_team_id">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>
                  {{ $t("Scrutineering.Player") }}
                </th>
                <th class="text-center">
                  {{ $t("Scrutineering.Kayak") }}
                </th>
                <th class="text-center">
                  {{ $t("Scrutineering.Vest") }}
                </th>
                <th class="text-center">
                  {{ $t("Scrutineering.Helmet") }}
                </th>
                <th class="text-center">
                  {{ $t("Scrutineering.Paddles") }}
                </th>
                <th class="text-center">
                  <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    @click="loadPlayers"
                  >
                    <i class="bi bi-arrow-clockwise" />
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="player in players" :key="player.id">
                <td>
                  <span v-if="player.cap !== 'E'" class="badge bg-dark">{{
                    player.num
                  }}</span>
                  <span v-if="player.cap === 'E'" class="badge bg-dark">{{
                    $t("Scrutineering.Coach")
                  }}</span>
                  {{ player.last_name }} {{ player.first_name }}
                  <span
                    v-if="player.cap === 'C'"
                    class="badge bg-warning text-dark"
                  >
                    C
                  </span>
                </td>
                <td v-if="player.cap !== 'E'" class="text-center border-end">
                  <button
                    type="button"
                    :class="{
                      btn: true,
                      'btn-sm': true,
                      'btn-success': player.kayak_status === 1,
                      'btn-danger': player.kayak_status > 1
                    }"
                    @click="
                      updatePlayer(
                        player.id,
                        player.player_id,
                        'kayak_status',
                        player.kayak_status
                      )
                    "
                  >
                    <i
                      :class="{
                        bi: true,
                        'bi-square': player.kayak_status < 1,
                        'bi-check-square': player.kayak_status === 1,
                        'bi-exclamation-circle': player.kayak_status === 2,
                        'bi-exclamation-triangle': player.kayak_status === 3,
                        'bi-exclamation-diamond': player.kayak_status === 4
                      }"
                    />
                  </button>
                </td>
                <td v-if="player.cap !== 'E'" class="text-center">
                  <button
                    type="button"
                    :class="{
                      btn: true,
                      'btn-sm': true,
                      'btn-success': player.vest_status === 1,
                      'btn-danger': player.vest_status > 1
                    }"
                    @click="
                      updatePlayer(
                        player.id,
                        player.player_id,
                        'vest_status',
                        player.vest_status
                      )
                    "
                  >
                    <i
                      :class="{
                        bi: true,
                        'bi-square': player.vest_status < 1,
                        'bi-check-square': player.vest_status === 1,
                        'bi-exclamation-circle': player.vest_status === 2,
                        'bi-exclamation-triangle': player.vest_status === 3,
                        'bi-exclamation-diamond': player.vest_status === 4
                      }"
                    />
                  </button>
                </td>
                <td v-if="player.cap !== 'E'" class="text-center">
                  <button
                    type="button"
                    :class="{
                      btn: true,
                      'btn-sm': true,
                      'btn-success': player.helmet_status === 1,
                      'btn-danger': player.helmet_status > 1
                    }"
                    @click="
                      updatePlayer(
                        player.id,
                        player.player_id,
                        'helmet_status',
                        player.helmet_status
                      )
                    "
                  >
                    <i
                      :class="{
                        bi: true,
                        'bi-square': player.helmet_status < 1,
                        'bi-check-square': player.helmet_status === 1,
                        'bi-exclamation-circle': player.helmet_status === 2,
                        'bi-exclamation-triangle': player.helmet_status === 3,
                        'bi-exclamation-diamond': player.helmet_status === 4
                      }"
                    />
                  </button>
                </td>
                <td v-if="player.cap !== 'E'" class="text-center">
                  <button
                    type="button"
                    :class="{
                      btn: true,
                      'btn-sm': true,
                      'btn-success': player.paddle_count > 0,
                      'text-light': player.paddle_count > 0,
                      'btn-light': player.paddle_count <= 0
                    }"
                    @click="
                      updatePlayer(
                        player.id,
                        player.player_id,
                        'paddle_count',
                        player.paddle_count
                      )
                    "
                  >
                    <b>
                      {{ player.paddle_count || 0 }}
                    </b>
                  </button>
                </td>
                <td v-if="player.cap !== 'E'" class="text-center">
                  <button type="button" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer" />
                  </button>
                </td>
                <td v-if="player.cap === 'E'" colspan="5"></td>
              </tr>
            </tbody>
          </table>
          <div class="float-end">
            <button type="button" class="btn btn-sm btn-primary">
              <i class="bi bi-printer" />
              {{ $t("Scrutineering.PrintAll") }}
            </button>
          </div>
          <div class="float-start mt-1">
            <label class="form-label me-1"
              ><i>{{ $t("Scrutineering.Issues") }}:</i></label
            >
            <br />
            <button class="btn btn-danger btn-sm" disabled>
              <i class="bi-exclamation-circle"
                >&nbsp; {{ $t("Scrutineering.Cosmetic") }}</i
              >
            </button>
            <button class="btn btn-danger btn-sm ms-1" disabled>
              <i class="bi-exclamation-triangle"
                >&nbsp; {{ $t("Scrutineering.Safety") }}</i
              >
            </button>
            <button class="btn btn-danger btn-sm ms-1" disabled>
              <i class="bi-exclamation-diamond"
                >&nbsp; {{ $t("Scrutineering.Technical") }}</i
              >
            </button>
          </div>
        </div>
      </div>

      <div v-else>
        <div
          class="alert alert-warning alert-dismissible fade show"
          role="alert"
        >
          <strong>{{ $t("Scrutineering.ChangeEvent") }} </strong>
          <button
            type="button"
            class="btn btn-sm btn-warning float-end"
            @click="$router.push({ name: 'Home' })"
          >
            <span class="bi bi-box-arrow-left" />
            {{ $t("nav.ChangeEvent") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import userMixin from '@/mixins/userMixin'
import statusMixin from '@/mixins/statusMixin'
import TeamSelector from '@/components/TeamSelector.vue'
import Players from '@/store/models/Players'
import privateApi from '@/network/privateApi'
import Title from '@/components/design/Title.vue'

export default {
  components: { TeamSelector, Title },
  name: 'Scrutineering',
  mixins: [prefsMixin, userMixin, statusMixin],
  computed: {
    players () {
      return Players.query().all()
    }
  },
  async mounted () {
    if (!this.user) {
      this.$router.replace({ name: 'Login' })
    }
    await this.checkAuthorized()
    await this.getPrefs()
    if (this.prefs?.scr_team_id) {
      this.loadPlayers()
    }
  },
  created () {
    this.getUser()
  },
  methods: {
    async loadPlayers () {
      if (!(await this.checkOnline())) {
        return
      }
      await privateApi
        .getPlayers(this.prefs.event, this.prefs.scr_team_id)
        .then(result => {
          Players.deleteAll()
          Players.insertOrUpdate({
            data: result.data
          })
        })
    },
    async updatePlayer (id, player, equipt, val) {
      var max = equipt === 'paddle_count' ? 6 : 4
      val = val >= max ? 0 : val + 1

      if (!(await this.checkOnline())) {
        return
      }
      await privateApi
        .putPlayer(
          this.prefs.event,
          this.prefs.scr_team_id,
          player,
          equipt,
          val
        )
        .then(result => {
          var updateObject = {}
          switch (equipt) {
            case 'kayak_status':
              updateObject = { kayak_status: result.data }
              break
            case 'vest_status':
              updateObject = { vest_status: result.data }
              break
            case 'helmet_status':
              updateObject = { helmet_status: result.data }
              break
            case 'paddle_count':
              updateObject = { paddle_count: result.data }
              break
          }
          Players.update({
            where: id,
            data: updateObject
          })
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
    }
  }
}
</script>

<style scoped>
@media screen and (orientation: landscape) {
  .if-portrait {
    display: none;
  }
}
</style>
