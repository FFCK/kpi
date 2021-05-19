<template>
    <div class="locale-switcher nav-link">
        🌐
        <select v-model="$i18n.locale" @change="changeLocale">
            <option value="en">English</option>
            <option value="fr">Français</option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery'
import idbs from '@/services/idbStorage'

export default {
  name: 'LocaleSwitcher',
  methods: {
    changeLocale () {
      $('.collapse').collapse('toggle')
      idbs.dbPut('preferences', {
        id: 1,
        locale: this.$i18n.locale
      })
    },
    defineLocale () {
      idbs.dbGet('preferences', 1)
        .then(result => {
          this.$i18n.locale = result.locale
        }).catch(_ => {
          const navLanguage = navigator.language.substring(0, 2)
          if (this.$i18n.availableLocales.includes(navLanguage)) {
            this.$i18n.locale = navLanguage
          }
        })
    }
  },
  created () {
    this.defineLocale()
  }
}
</script>
