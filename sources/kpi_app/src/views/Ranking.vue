<template>
  <div class="container-sm">
    <title-component
      :text="$t('nav.Ranking')"
    />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'Ranking',
  components: {
    TitleComponent
  }
}
</script>
