<template>
  <span class="online-view">
    <i class="bi bi-wifi text-success" v-if="online" />
    <i class="bi bi-wifi-off bg-danger rounded" v-else />
  </span>
</template>

<script>
import Status from '@/store/models/Status'

export default {
  name: 'Offline',
  data () {
    return {
      online: navigator.onLine
    }
  },
  created () {
    if (Status.find(1) === null) {
      Status.insert({ data: [{ id: 1 }] })
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
      Status.update({
        data: [{ id: 1, online: this.online }]
      })
      this.$emit(this.online ? 'online' : 'offline')
    }
  }
}
</script>
