import idbs from '@/services/idbStorage'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Games from '@/store/models/Games'

const logoutMixin = {
  data () {
    return {
      showLoginForm: false
    }
  },
  methods: {
    async logOut () {
      await idbs.dbClear('user')
      User.deleteAll()
      await idbs.dbClear('games')
      Games.deleteAll()
      document.cookie = 'kpi_app=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      this.showLoginForm = true
    }
  }
}

const prefsMixin = {
  computed: {
    prefs () {
      return Preferences.query().first()
    }
  },
  methods: {
    async getPrefs () {
      if (Preferences.query().count() === 0) {
        const result = await idbs.dbGetAll('preferences')
        if (result.length === 1) {
          Preferences.insertOrUpdate({
            data: result
          })
        } else {
          Preferences.insertOrUpdate({
            data: {
              id: 1
            }
          })
          idbs.dbPut('preferences', Preferences.query().first())
        }
      }
    },
    scrollTop () {
      document.body.scrollTop = 0 // For Safari
      document.documentElement.scrollTop = 0 // For Chrome, Firefox, IE and Opera
    }
  },
  async mounted () {
    await this.getPrefs()
  }
}

const userMixin = {
  computed: {
    user () {
      return User.query().first()
    }
  },
  methods: {
    async getUser () {
      if (User.query().count() === 0) {
        const result = await idbs.dbGetAll('user')
        if (result.length === 1) {
          User.insertOrUpdate({
            data: result
          })
        }
      }
    }
  },
  created () {
    this.getUser()
  }
}

const gamesMixin = {
  methods: {
    gameEncode (gameCode, codeNumber) {
      const readCode = gameCode ? gameCode.split(/[[\]]/)[1].split(/[-/*,;]/g)[codeNumber - 1] : null
      if (!readCode) {
        return null
      }
      const resultLetter = readCode.match(/([A-Z]+)/)[0]
      const resultNumberArray = readCode.match(/([0-9]+)/)
      const resultNumber = resultNumberArray[0]
      const resultNumberIndex = resultNumberArray.index
      if (resultNumberIndex === 0) {
        return '¤|' + resultNumber + '|Group|' + resultLetter
      }

      let result
      switch (resultLetter) {
        case 'W': // Winner
        case 'V': // Vainqueur
        case 'G': // Gagnant
          result = '¤||Winner|' + resultNumber
          break
        case 'L': // Looser
        case 'P': // Perdant
          result = '¤||Looser|' + resultNumber
          break
        case 'D': // Draw
        case 'T': // Tirage
          result = '¤||Team|' + resultNumber
          break
        default:
          result = null
          break
      }
      return result
    }
  }
}

const gamesDisplayMixin = {
  methods: {
    showCode (val) {
      if (val && val[0] === '¤') {
        const resultArray = val.split('|')
        const result = this.ordinalNumber(resultArray[1]) + this.$t('Games.Code.' + resultArray[2]) + resultArray[3]
        return result
      }
      return val
    },
    ordinalNumber (val) {
      const test = ('' + val).slice(-1)
      if (test === '') {
        return val
      }
      let result
      switch (val) {
        case '1':
          result = val + this.$t('Games.Numbers.first')
          break
        case '2':
          result = val + this.$t('Games.Numbers.second')
          break
        case '3':
          result = val + this.$t('Games.Numbers.third')
          break
        case '11':
          result = val + this.$t('Games.Numbers.eleven')
          break
        case '12':
          result = val + this.$t('Games.Numbers.twelve')
          break
        case '13':
          result = val + this.$t('Games.Numbers.thirteen')
          break
        case test === '1':
          result = val + this.$t('Games.Numbers.twentyone')
          break
        case test === '2':
          result = val + this.$t('Games.Numbers.twentytwo')
          break
        case test === '3':
          result = val + this.$t('Games.Numbers.twentythree')
          break
        default:
          result = val + this.$t('Games.Numbers.th')
      }
      return result
    }
  }
}

export {
  logoutMixin,
  prefsMixin,
  userMixin,
  gamesMixin,
  gamesDisplayMixin
}
