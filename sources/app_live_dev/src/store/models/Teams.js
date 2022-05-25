import { Model } from '@vuex-orm/core'

export default class Teams extends Model {
  static entity = 'teams'

  static fields () {
    return {
      id: this.uid(),
      team_id: this.number(0),
      label: this.string(''),
      club: this.string(''),
      logo: this.string(null).nullable()
    }
  }
}
