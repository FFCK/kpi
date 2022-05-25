import { Model } from '@vuex-orm/core'

export default class Games extends Model {
  static entity = 'games'

  static fields () {
    return {
      g_id: this.number(0),
      g_number: this.number(0),
      g_coef_a: this.number(0),
      g_coef_b: this.number(0),
      g_date: this.string(''),
      g_time: this.string(''),
      g_pitch: this.string(''),
      g_status: this.string(''),
      g_period: this.string('').nullable(),
      g_code: this.string(''),
      g_score_a: this.string('').nullable(),
      g_score_b: this.string('').nullable(),
      g_score_detail_a: this.number(0).nullable(),
      g_score_detail_b: this.number(0).nullable(),
      g_validation: this.string('N'),
      d_id: this.number(0),
      d_label: this.string(''),
      d_place: this.string(''),
      d_level: this.number(0),
      d_phase: this.string(''),
      c_code: this.string(''),
      c_season: this.number(0),
      c_label: this.string(''),
      r_1: this.string('').nullable(),
      r_1_id: this.number(0).nullable(),
      r_1_name: this.string('').nullable(),
      r_2: this.string('').nullable(),
      r_2_id: this.number(0).nullable(),
      r_2_name: this.string('').nullable(),
      t_a_id: this.number(0).nullable(),
      t_a_number: this.number(0).nullable(),
      t_a_label: this.string('').nullable(),
      t_a_club: this.string('').nullable(),
      t_a_logo: this.string('').nullable(),
      t_b_id: this.number(0).nullable(),
      t_b_number: this.number(0).nullable(),
      t_b_label: this.string('').nullable(),
      t_b_club: this.string('').nullable(),
      t_b_logo: this.string('').nullable()
    }
  }
}
