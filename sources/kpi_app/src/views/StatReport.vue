<template>
  <div>
    <title-component :text="$t('nav.StatReport')" />

    <span class="bi bi-bar-chart-line h1 text-primary"></span>

  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'StatReport',
  components: {
    TitleComponent
  }
}
</script>
