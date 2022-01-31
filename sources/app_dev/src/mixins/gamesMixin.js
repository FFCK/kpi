import publicApi from '@/network/publicApi'
import idbs from '@/services/idbStorage'
import Games from '@/store/models/Games'

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
    },
    async getGames () {
      if (Games.query().count() === 0) {
        const result = await idbs.dbGetAll('games')
        if (result.length > 0) {
          await Games.insertOrUpdate({
            data: result
          })
        } else {
          this.loadGames()
        }
        this.filterGames()
      }
    },
    async loadGames () {
      await this.getPrefs()
      await this.prefs
      if (!(await this.checkOnline())) {
        return
      }
      this.visibleButton = false
      setTimeout(() => {
        this.visibleButton = true
      }, 3000)
      await publicApi
        .getGames(this.prefs.event)
        .then(async result => {
          const gamelist = await result.data.map(game => {
            game.g_score_a = game.g_score_a?.replace('?', '') || game.g_score_a
            game.g_score_b = game.g_score_b?.replace('?', '') || game.g_score_b
            game.g_score_detail_a = parseInt(game.g_score_detail_a) || 0
            game.g_score_detail_b = parseInt(game.g_score_detail_b) || 0
            game.r_1 =
              game.r_1 && game.r_1 !== '-1'
                ? game.r_1.replace(
                  /\) (INT-|NAT-|REG-|INT|REG|OTM|JO)[ABCS]{0,1}/,
                  ')'
                )
                : null
            game.r_2 =
              game.r_2 && game.r_2 !== '-1'
                ? game.r_2.replace(
                  /\) (INT-|NAT-|REG-|INT|REG|OTM|JO)[ABCS]{0,1}/,
                  ')'
                )
                : null
            game.t_a_label ??= this.gameEncode(game.g_code, 1)
            game.t_b_label ??= this.gameEncode(game.g_code, 2)
            game.r_1 ??= this.gameEncode(game.g_code, 3)
            game.r_2 ??= this.gameEncode(game.g_code, 4)
            return game
          })
          await Games.deleteAll()
          await Games.insertOrUpdate({
            data: gamelist
          })
          idbs.dbClear('games')
          gamelist.forEach(element => {
            idbs.dbPut('games', element)
          })
          this.loadCategories()
          this.filterGames()
        })
        .catch(error => {
          if (error.message === 'Network Error') {
            console.log('Offline !')
          }
        })
    }
  }
}
