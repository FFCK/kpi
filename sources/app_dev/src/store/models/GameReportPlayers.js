import { Model } from '@vuex-orm/core'

export default class GameReportPlayers extends Model {
  static entity = 'gameReportPlayers'

  static primaryKey = ['g_id', 'tm_licence']

  static fields () {
    return {
      g_id: this.number(0),
      team: this.string(''),
      tm_birthdate: this.string(''),
      tm_firstname: this.string(''),
      tm_gender: this.string(''),
      tm_licence: this.number(0),
      tm_name: this.string(''),
      tm_number: this.number(0).nullable(),
      tm_status: this.string('')
    }
  }
}
