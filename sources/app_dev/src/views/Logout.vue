<template>
  <div></div>
</template>

<script>
import logoutMixin from '@/mixins/logoutMixin'

export default {
  name: 'Logout',
  mixins: [logoutMixin],
  mounted () {
    this.logOut()
    this.$router.push('Login')
  }
}
</script>
