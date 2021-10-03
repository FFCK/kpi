<template>
  <div class="container">
    <div class="filters">
      <div class="row">
        <div class="col">
          <i
            role="button"
            class="bi bi-caret-left-square-fill me-2"
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

      <p>
        {{ $t("About.Rating") }}
        <rating
          :thanks="thanks"
          :grade="stars"
          @rated="rated"
          :key="key"
          class="text-center"
        />
      </p>

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
      {{ $t("About.ContactMe") }}
      <a href="mailto:contact@kayak-polo.info">contact@kayak-polo.info</a>
    </p>
    <p class="text-end mt-4 me-5">
      Laurent.
    </p>
  </div>
</template>

<script>
import Rating from '@/components/design/Rating.vue'
import Preferences from '@/store/models/Preferences'
import { prefsMixin } from '@/mixins/mixins'
import idbs from '@/services/idbStorage'
import Status from '@/store/models/Status'
import publicApi from '@/network/publicApi'

export default {
  name: 'About',
  mixins: [prefsMixin],
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
      status: {}
    }
  },
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    },
    async rated (stars) {
      this.thanks = true
      this.stars = stars
      this.key++
      this.status = await Status.find(1)
      if (!this.status.online) {
        console.log('Offline process...')
      } else {
        await publicApi.postRating(this.prefs.uid, this.stars)
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
          }).catch(error => {
            if (error.message === 'Network Error') {
              console.log('Offline !')
            }
          })
      }
    }
  },
  async mounted () {
    await this.getPrefs()
    await this.prefs
    this.stars = this.prefs.stars
    this.key++
  }
}
</script>
