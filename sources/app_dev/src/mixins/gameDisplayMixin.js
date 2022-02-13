export default {
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
    },
    teamHover (event) {
      const SelectedTeamName = event.target.innerText
      var elems = document.querySelectorAll('.team_name')
      elems.forEach((el) => {
        if (el.innerText === SelectedTeamName) {
          el.classList.add('bg-warning', 'text-dark')
        }
      })
    },
    teamOut (event) {
      var elems = document.querySelectorAll('.team_name.bg-warning.text-dark')
      elems.forEach((el) => {
        el.classList.remove('bg-warning', 'text-dark')
      })
    },
    teamNameResize (name) {
      if (name && name.length >= 20) {
        return (
          name.substring(0, 12) + name.substring(12).replace(/\s|-|_/, '<br>')
        )
      }
      return name
    }
  }
}
