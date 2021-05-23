<template>
  <div class="container-sm">

    <event-selector />

    <div v-if="prefs">
      <div v-if="prefs.event > 0" class="row justify-content-around my-5">
        <button class="btn btn-primary btn-lg col-5" @click="changePage('Games')">
          {{ $t("nav.Games") }}
        </button>
        <button class="btn btn-primary btn-lg col-5" @click="changePage('Ranking')">
          {{ $t("nav.Ranking") }}
        </button>
      </div>
    </div>

    <div class="row justify-content-around my-5" v-if="user">
      <button class="btn btn-primary btn-lg col-3" @click="changePage('GameReport')">
        {{ $t("nav.GameReport") }}
      </button>
      <button class="btn btn-primary btn-lg col-3" @click="changePage('StatReport')">
        {{ $t("nav.StatReport") }}
      </button>
      <button class="btn btn-primary btn-lg col-3" @click="changePage('Scrutineering')">
        {{ $t("nav.Scrutineering") }}
      </button>
    </div>

    <div class="my-1">
      <img alt="Vue logo" src="../assets/logo.png" width="100" height="100">
    </div>
  </div>
</template>

<script>
import { logoutMixin, prefsMixin, userMixin } from '@/services/mixins'
import EventSelector from '@/components/EventSelector.vue'

export default {
  name: 'Home',
  mixins: [logoutMixin, prefsMixin, userMixin],
  components: {
    EventSelector
  },
  data () {
    return {
      content: ''
    }
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
