<template>
  <footer class="fixed bottom-0 left-0 right-0 bg-gray-100 border-t border-gray-200 py-1 text-end pe-2">
    <span class="text-xs text-gray-600 italic">v{{ version }}</span>
  </footer>
</template>

<script setup>
import { ref } from 'vue'
import pkg from '../package.json'

const version = ref(pkg.version)
</script>
