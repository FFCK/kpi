<template>
  <div>
    <title-component text="KPI APPLICATION" />

    <event-selector @changeEvent="checkAuthorized" />

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

    <div v-if="user && authorized" class="container my-5">
      <div class="row">
        <button
          class="btn btn-outline-dark btn-lg"
          @click="changePage('Login')"
        >
          {{ $t("nav.StaffJobs") }}
        </button>
      </div>
    </div>

    <div class="my-3 text-center">
      <img alt="Vue logo" src="../assets/logo.png" width="100" height="100" />
    </div>
    <div class="text-center my-3" @click="changePage('About')">
      <span class="btn btn-secondary me-1">{{ version }}</span>
      <i class="star bi bi-star" :title="$t('Rating.RateThisApp')"></i>
    </div>
  </div>
</template>

<script>
import { logoutMixin, prefsMixin, userMixin } from '@/mixins/mixins'
import EventSelector from '@/components/EventSelector.vue'
import TitleComponent from '@/components/design/Title'

export default {
  name: 'Home',
  components: {
    TitleComponent,
    EventSelector
  },
  mixins: [logoutMixin, prefsMixin, userMixin],
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
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
  },
  mounted () {
    this.checkAuthorized()
  }
}
</script>

<style scoped lang="scss">
.star {
  display: inline-block;
  font-size: 25px;
  transition: all 0.3s ease-in-out;
  cursor: pointer;
  color: #6c757d;
  &:hover {
    color: #ffe100;
  }
}
</style>
