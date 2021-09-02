<template>
  <div class="container">
    <title-component :text="$t('nav.About')" />

    <div class="text-center my-3">
      <span class="btn btn-secondary">{{ version }}</span>
    </div>

    <div class="text-center">
      {{ $t("About.DoYouLike") }}<br />
      {{ $t("About.IDevelopIt") }}<br />
      {{ $t("About.SupportMeOnTipeee") }}
    </div>

    <div class="my-1 text-center">
      <a
        href="https://fr.tipeee.com/lga"
        target="blank"
        v-if="$i18n.locale === 'fr'"
      >
        <img
          alt="Vue logo"
          src="../assets/bocal_tipeee.gif"
          width="250"
          height="250"
        />
      </a>
      <a href="https://en.tipeee.com/lga" target="blank" v-else>
        <img
          alt="Vue logo"
          src="../assets/jar_tipeee.gif"
          width="250"
          height="250"
        />
      </a>
    </div>
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'About',
  components: {
    TitleComponent
  },
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  }
}
</script>
