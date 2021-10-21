export default {
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
