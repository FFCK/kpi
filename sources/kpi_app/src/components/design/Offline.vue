<template>
  <span class="online-view">
    <i class="bi bi-wifi text-success" v-if="online" />
    <i class="bi bi-wifi-off bg-danger rounded" v-else />
  </span>
</template>

<script>
import Errors from '@/store/models/Errors'

export default {
  name: 'Offline',
  data () {
    return {
      online: navigator.onLine
    }
  },
  created () {
    if (Errors.find(1) === null) {
      Errors.insert({ data: [{ id: 1 }] })
    }
  },
  mounted () {
    window.addEventListener('online', this.onchange)
    window.addEventListener('offline', this.onchange)
    this.onchange()
  },
  beforeUnmount () {
    window.removeEventListener('online', this.onchange)
    window.removeEventListener('offline', this.onchange)
  },
  methods: {
    onchange () {
      this.online = navigator.onLine
      if (this.online) {
        Errors.update({
          data: [{ id: 1, offline: false }]
        })
      } else {
        Errors.update({
          data: [{ id: 1, offline: true }]
        })
      }
      this.$emit(this.online ? 'online' : 'offline')
    }
  }
}
</script>
