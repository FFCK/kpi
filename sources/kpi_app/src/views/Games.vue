<template>
  <div class="container-sm">
    <title-component
      :text="$t('nav.Games')"
    />
  </div>
</template>

<script>
import TitleComponent from '@/components/design/Title'

export default {
  name: 'Games',
  components: {
    TitleComponent
  }
}
</script>
