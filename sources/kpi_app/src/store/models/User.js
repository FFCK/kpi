import { Model } from '@vuex-orm/core'

export default class User extends Model {
  static entity = 'user'

  static fields () {
    return {
      id: this.number(0),
      name: this.string(''),
      firstname: this.string(''),
      profile: this.number('')
    }
  }
}
