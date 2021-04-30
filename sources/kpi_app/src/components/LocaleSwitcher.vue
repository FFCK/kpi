<template>
    <div class="locale-switcher nav-link">
        🌐
        <select v-model="$i18n.locale" @change="collapse">
            <option value="en">English</option>
            <option value="fr">Français</option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery'

export default {
  name: 'LocaleSwitcher',
  methods: {
    collapse () {
      $('.collapse').collapse('toggle')
    }
  }
}
</script>
