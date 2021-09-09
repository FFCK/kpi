<template>
  <div class="container">
    <div class="text-center my-3">
      <span class="btn btn-secondary">{{ version }}</span>
    </div>

    <div class="text-center">
      {{ $t("About.DoYouLike") }}<br />
      {{ $t("About.IDevelopIt") }}<br />
      {{ $t("About.FeedbackOnTwitter") }}<br />
      <a
        class="btn btn-primary btn-sm"
        href="https://twitter.com/kayakpolo_info"
        target="blank"
      >
        <i class="bi bi-twitter" /> Twitter
      </a>
      <br />
      {{ $t("About.SupportMeOnUtip") }}<br />
      <a
        class="btn btn-danger btn-sm"
        href="https://utip.io/kayakpoloinfo"
        target="blank"
      >
        <i class="bi bi-piggy-bank" /> uTip
      </a>
    </div>

    <div class="text-center">
      <a
        href="https://utip.io/kayakpoloinfo"
        target="blank"
        v-if="$i18n.locale === 'fr'"
      >
        <img alt="logo uTip" src="../assets/logo-utip.png" height="90" />
        <br />
        <img alt="Dablicorne" src="../assets/dablicorne-utip.png" width="150" />
      </a>
      <a href="https://utip.io/kayakpoloinfo" target="blank" v-else>
        <img alt="logo uTip" src="../assets/logo-utip.png" height="90" />
        <br />
        <img alt="Dablicorne" src="../assets/dablicorne-utip.png" width="150" />
      </a>
    </div>
  </div>
</template>

<script>

export default {
  name: 'About',
  computed: {
    version () {
      return 'v' + process.env.VUE_APP_VERSION
    }
  }
}
</script>
