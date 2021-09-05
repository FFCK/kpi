<template>
  <div class="container">
    <div class="text-center my-3">
      <span class="btn btn-secondary">{{ version }}</span>
    </div>

    <div class="text-center">
      {{ $t("About.DoYouLike") }}<br />
      {{ $t("About.IDevelopIt") }}<br />
      {{ $t("About.SupportMeOnUtip") }}
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
