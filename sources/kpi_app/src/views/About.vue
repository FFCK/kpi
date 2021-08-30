<template>
  <div class="container">
    <title-component :text="$t('nav.About')" />

    <div class="text-center my-3">
      <span class="btn btn-secondary">{{ version }}</span>
    </div>

    <div class="text-center">
      Cette application vous plait ?<br />
      Je la développe sur mon temps libre.<br />
      Vous pouvez m'encourager à continuer et me soutenir sur Tipeee :
    </div>

    <div class="my-1 text-center">
      <a href="https://fr.tipeee.com/lga" target="blank">
        <img
          alt="Vue logo"
          src="../assets/bocal_tipeee.gif"
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
