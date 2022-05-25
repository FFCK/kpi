import { Model } from '@vuex-orm/core'

export default class GameReportEvents extends Model {
  static entity = 'gameReportEvents'

  static primaryKey = 'e_id'

  static fields () {
    return {
      e_id: this.string(''),
      g_id: this.number(0),
      e_firstname: this.string(''),
      e_licence: this.number(0),
      e_motif: this.string('').nullable(),
      e_name: this.string(''),
      e_number: this.string(''),
      e_period: this.string(''),
      e_status: this.string(''),
      e_team: this.string(''),
      e_time: this.string(''),
      e_type: this.string('')
    }
  }
}
