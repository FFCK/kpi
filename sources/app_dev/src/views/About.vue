<template>
  <div class="container">
    <div class="filters">
      <div class="row">
        <div class="col">
          <i
            role="button"
            class="bi bi-caret-left-square-fill me-2 btn btn-secondary"
            @click="changePage('Chart')"
          />
        </div>
        <div class="col text-end">
          <span class="btn btn-sm btn-secondary disabled">{{ version }}</span>
        </div>
      </div>
    </div>

    <div>
      <p>
        {{ $t("About.DoYouLike") }}
        <br />
        {{ $t("About.IDevelopIt") }}
      </p>

      <div class="mb-2">
        {{ $t("About.Rating") }}
        <rating
          :thanks="thanks"
          :grade="stars"
          @rated="rated"
          :key="key"
          class="text-center"
        />
        <div v-if="currentRating" class="text-center fst-italic small">
          <i
            >{{ $t("About.Average") }} {{ currentRating }}/5 ({{
              currentVoters
            }}
            {{ $t("About.voters") }})</i
          >
        </div>
      </div>

      <p>
        {{ $t("About.FeedbackOnTwitter") }}
        <a
          class="btn btn-primary btn-sm"
          href="https://twitter.com/kayakpolo_info"
          target="blank"
        >
          <i class="bi bi-twitter" /> Twitter
        </a>
      </p>
      <p>
        {{ $t("About.SupportMeOnUtip") }}
        <a
          href="https://utip.io/kayakpoloinfo"
          target="blank"
          class="btn btn-light"
          v-if="$i18n.locale === 'fr'"
        >
          <img alt="logo uTip" src="../assets/logo-utip.png" height="50" />
          <img
            alt="Dablicorne"
            src="../assets/dablicorne-utip.png"
            height="50"
          />
        </a>
        <a
          href="https://utip.io/kayakpoloinfo"
          target="blank"
          v-else
          class="btn btn-light"
        >
          <img alt="logo uTip" src="../assets/logo-utip.png" height="50" />
          <img
            alt="Dablicorne"
            src="../assets/dablicorne-utip.png"
            height="50"
          />
        </a>
      </p>
    </div>
    <hr />
    <p>
      {{ $t("About.OwnCompetition") }}
      <br />
      {{ $t("About.HelpMe") }}
      <br />
      {{ $t("About.ContactMe") }}
      <a href="mailto:contact@kayak-polo.info">contact@kayak-polo.info</a>
    </p>
    <p class="text-end mt-4 me-5">
      Laurent.
    </p>
  </div>
</template>

<script>
import prefsMixin from '@/mixins/prefsMixin'
import statusMixin from '@/mixins/statusMixin'
import Rating from '@/components/design/Rating.vue'
import Preferences from '@/store/models/Preferences'
import idbs from '@/services/idbStorage'
import publicApi from '@/network/publicApi'

export default {
  name: 'About',
  mixins: [prefsMixin, statusMixin],
  components: {
    Rating
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  },
  data () {
    return {
      stars: 0,
      thanks: false,
      key: 0,
      currentRating: null,
      currentVoters: null
    }
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    },
    async getUserStars () {
      await this.getPrefs()
      await this.prefs
      this.stars = this.prefs.stars
      this.key++
    },
    async getCurrentRating () {
      if (!(await this.checkOnline())) {
        return
      }
      await publicApi
        .getStars()
        .then(async result => {
          this.currentRating = parseFloat(result.data.average).toFixed(2)
          this.currentVoters = result.data.count
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
    },
    async rated (stars) {
      if (!(await this.checkOnline())) {
        return
      }
      this.thanks = true
      this.stars = stars
      this.key++
      await publicApi
        .postRating(this.prefs.uid, this.stars)
        .then(async result => {
          if (result.data === true) {
            await Preferences.update({
              where: 1,
              data: {
                stars: stars
              }
            })
            idbs.dbPut('preferences', Preferences.find(1))
          }
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
    }
  },
  mounted () {
    this.getUserStars()
    this.getCurrentRating()
  }
}
</script>
