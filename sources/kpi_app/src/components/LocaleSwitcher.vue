<template>
    <div class="locale-switcher nav-link">
        🌐
        <select v-model="$i18n.locale" @change="changeLocale">
            <option value="en-US">English</option>
            <option value="fr-FR">Français</option>
        </select>
    </div>
</template>

<script>
import $ from 'jquery'
import idbs from '@/services/idbStorage'
import Preferences from '@/store/models/Preferences'

export default {
  name: 'LocaleSwitcher',
  methods: {
    changeLocale () {
      $('.collapse').collapse('toggle')
      Preferences.update({
        where: 1,
        data: {
          locale: this.$i18n.locale
        }
      })
      idbs.dbPut('preferences', Preferences.find(1))
    },
    getLocale () {
      idbs.dbGet('preferences', 1)
        .then(result => {
          this.$i18n.locale = result.locale
        }).catch(_ => {
          const navLanguage = navigator.language
          if (this.$i18n.availableLocales.includes(navLanguage)) {
            this.$i18n.locale = navLanguage
          }
        })
    }
  },
  created () {
    this.getLocale()
  }
}
</script>
