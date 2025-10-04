import { useI18n } from 'vue-i18n'

export const useGameDisplay = () => {
  const { t } = useI18n()

  const showCode = (val) => {
    if (val && val[0] === '¤') {
      const resultArray = val.split('|')
      const result = ordinalNumber(resultArray[1]) + t('Games.Code.' + resultArray[2]) + resultArray[3]
      return result
    }
    return val
  }

  const ordinalNumber = (val) => {
    const test = ('' + val).slice(-1)
    if (test === '') {
      return val
    }
    let result
    switch (val) {
      case '1':
        result = val + t('Games.Numbers.first')
        break
      case '2':
        result = val + t('Games.Numbers.second')
        break
      case '3':
        result = val + t('Games.Numbers.third')
        break
      case '11':
        result = val + t('Games.Numbers.eleven')
        break
      case '12':
        result = val + t('Games.Numbers.twelve')
        break
      case '13':
        result = val + t('Games.Numbers.thirteen')
        break
      case test === '1':
        result = val + t('Games.Numbers.twentyone')
        break
      case test === '2':
        result = val + t('Games.Numbers.twentytwo')
        break
      case test === '3':
        result = val + t('Games.Numbers.twentythree')
        break
      default:
        result = val + t('Games.Numbers.th')
    }
    return result
  }

  const teamNameResize = (name) => {
    if (name && name.length >= 25) {
      return (
        name.substring(0, 15) + name.substring(15).replace(/\s|-|_/, '<br>')
      )
    }
    return name
  }

  return { showCode, teamNameResize }
}
