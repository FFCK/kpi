<template>
  <div class="container">
    <div class="text-center my-3">
      <span class="btn btn-secondary">{{ version }}</span>
    </div>

    <div class="text-center">
      <div>{{ $t("About.DoYouLike") }}</div>
      <div class="mb-1">{{ $t("About.IDevelopIt") }}</div>
      <div class="mt-2">{{ $t("About.Rating") }}</div>

      <rating :thanks="thanks" :grade="stars" @rated="rated" :key="key" />

      <div class="mt-2">
        {{ $t("About.FeedbackOnTwitter") }}
        <a
          class="btn btn-primary btn-sm"
          href="https://twitter.com/kayakpolo_info"
          target="blank"
        >
          <i class="bi bi-twitter" /> Twitter
        </a>
      </div>
      <div class="mt-2">
        {{ $t("About.SupportMeOnUtip") }}
        <a
          class="btn btn-danger btn-sm"
          href="https://utip.io/kayakpoloinfo"
          target="blank"
        >
          <i class="bi bi-piggy-bank" /> uTip
        </a>
      </div>
    </div>

    <div class="text-center">
      <a
        href="https://utip.io/kayakpoloinfo"
        target="blank"
        v-if="$i18n.locale === 'fr'"
      >
        <img alt="logo uTip" src="../assets/logo-utip.png" height="90" />
        <img alt="Dablicorne" src="../assets/dablicorne-utip.png" height="90" />
      </a>
      <a href="https://utip.io/kayakpoloinfo" target="blank" v-else>
        <img alt="logo uTip" src="../assets/logo-utip.png" height="90" />
        <img alt="Dablicorne" src="../assets/dablicorne-utip.png" height="90" />
      </a>
    </div>

    <div class="text-end mt-4 me-5">
      Laurent.
    </div>
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
