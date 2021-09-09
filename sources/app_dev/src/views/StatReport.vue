<template>
  <div>
    <title-component :text="$t('nav.StatReport')" />

    <span class="bi bi-bar-chart-line h1 text-primary" />

    <coming-soon />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'
import ComingSoon from '@/components/design/ComingSoon.vue'

export default {
  name: 'StatReport',
  components: {
    TitleComponent,
    ComingSoon
  }
}
</script>
