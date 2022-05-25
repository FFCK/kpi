<template>
  <div
    class="alert alert-dismissible fade show"
    :class="[status.messageClass]"
    role="button"
    v-if="status.messageText"
    @click="closeAlert"
  >
    <strong>{{ status.messageText }}</strong>
    <button type="button" class="btn-close"></button>
  </div>
</template>

<script>
import Status from '@/store/models/Status'

export default {
  name: 'Update',
  computed: {
    status () {
      return Status.find(1)
    }
  },
  methods: {
    async closeAlert () {
      await Status.update({
        where: 1,
        data: {
          messageText: ''
        }
      })
    }
  }
}
</script>
