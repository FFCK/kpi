<template>
  <div>
    <title-component :text="$t('nav.About')" />

    <HelloWorld :msg="$t('message')" />

    <div class="row" v-if="user">
      <div class="my-2 mx-auto">
        <button class="btn btn-secondary" @click="ajaxTest">Test API</button>
        <div>{{ content }}</div>
      </div>
    </div>

  </div>
</template>

<script>
import { api } from '@/services/api'
import HelloWorld from '@/components/HelloWorld.vue'
import { userMixin } from '@/services/mixins'
import TitleComponent from '@/components/design/Title'

export default {
  name: 'About',
  mixins: [userMixin],
  components: {
    HelloWorld,
    TitleComponent
  },
  methods: {
    async ajaxTest () {
      await api.get('/staff/test')
        .then((response) => {
          console.log(response.data)
          if (response.data === 'KO') {
            this.logOut()
          }
        }).catch((error) => {
          // Erreur dans la réponse ?
          if (error.response) {
            if (error.response.status === 401) {
              this.message = this.$t('Login.UnauthorizedMsg')
              console.log('Unauthorized')
            }
          // Erreur dans la requête ?
          } else if (error.request) {
            this.message = this.$t('Login.ErrorMsg')
            console.log('ErrorMsg')
          }
        })
    }
  }
}
</script>
