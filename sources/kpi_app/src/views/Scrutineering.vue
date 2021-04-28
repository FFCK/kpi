<template>
  <div class="container-sm">
    <h1>{{ $t('nav.Scrutineering') }}</h1>
  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: 'Scrutineering',
  methods: {
    changePage (pageName) {
      this.$router.push({ name: pageName })
    }
  }
}
</script>
