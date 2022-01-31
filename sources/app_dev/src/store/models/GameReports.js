import { Model } from '@vuex-orm/core'
import GameReportEvents from '@/store/models/GameReportEvents'
import GameReportPlayers from '@/store/models/GameReportPlayers'

export default class GameReports extends Model {
  static entity = 'gameReports'

  static primaryKey = 'g_id'

  static fields () {
    return {
      g_id: this.number(0),
      local: this.boolean(false),
      c_code: this.string(''),
      c_label: this.string(''),
      c_season: this.number(0),
      c_type: this.string(''),
      d_id: this.number(0),
      d_label: this.string(''),
      d_level: this.number(0),
      d_phase: this.string(''),
      d_place: this.string(''),
      d_type: this.string(''),
      g_code: this.string(''),
      g_coef_a: this.number(0),
      g_coef_b: this.number(0),
      g_date: this.string(''),
      g_number: this.number(0),
      g_period: this.string('').nullable(),
      g_pitch: this.string(''),
      g_score_a: this.string('').nullable(),
      g_score_b: this.string('').nullable(),
      g_score_detail_a: this.number(0).nullable(),
      g_score_detail_b: this.number(0).nullable(),
      g_status: this.string(''),
      g_time: this.string(''),
      g_validation: this.string('N'),
      t_a_club: this.string('').nullable(),
      t_a_color1: this.string('#ffffff'),
      t_a_color2: this.string('#ffffff'),
      t_a_id: this.number(0).nullable(),
      t_a_label: this.string('').nullable(),
      t_a_logo: this.string('').nullable(),
      t_a_number: this.number(0).nullable(),
      t_b_club: this.string('').nullable(),
      t_b_color1: this.string('#ffffff'),
      t_b_color2: this.string('#ffffff'),
      t_b_id: this.number(0).nullable(),
      t_b_label: this.string('').nullable(),
      t_b_logo: this.string('').nullable(),
      t_b_number: this.number(0).nullable(),
      g_events: this.hasMany(GameReportEvents, 'g_id'),
      t_members: this.hasMany(GameReportPlayers, 'g_id')
    }
  }
}
