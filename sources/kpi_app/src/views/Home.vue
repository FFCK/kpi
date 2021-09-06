<template>
  <div>
    <title-component text="KPI APPLICATION" />

    <event-selector />

    <div v-if="prefs">
      <XyzTransitionGroup
        v-if="prefs.event > 0"
        appear
        class="row justify-content-around my-5"
        xyz="fade flip-up"
      >
        <button
          key="Game"
          class="btn btn-outline-primary btn-lg col-5"
          @click="changePage('Games')"
        >
          {{ $t("nav.Games") }}
        </button>
        <button
          key="Chart"
          class="btn btn-outline-primary btn-lg col-5"
          @click="changePage('Chart')"
        >
          {{ $t("nav.Chart") }}
        </button>
      </XyzTransitionGroup>
    </div>

    <div v-if="user" class="row justify-content-around my-5">
      <button
        class="btn btn-outline-dark btn-lg col-3"
        @click="changePage('GameReport')"
      >
        {{ $t("nav.GameReport") }}
      </button>
      <button
        class="btn btn-outline-dark btn-lg col-3"
        @click="changePage('StatReport')"
      >
        {{ $t("nav.StatReport") }}
      </button>
      <button
        class="btn btn-outline-dark btn-lg col-3"
        @click="changePage('Scrutineering')"
      >
        {{ $t("nav.Scrutineering") }}
      </button>
    </div>

    <div class="my-3 text-center">
      <img alt="Vue logo" src="../assets/logo.png" width="100" height="100" />
    </div>
  </div>
</template>

<script>
import { logoutMixin, prefsMixin, userMixin } from '@/services/mixins'
import EventSelector from '@/components/EventSelector.vue'
import TitleComponent from '@/components/design/Title'

export default {
  name: 'Home',
  components: {
    TitleComponent,
    EventSelector
  },
  mixins: [logoutMixin, prefsMixin, userMixin],
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
