<template>
  <!-- This component has no visible UI, it only initializes network monitoring -->
</template>

<script setup>
// Initialize network monitoring
const { isOnline } = useNetworkMonitor()
</script>
