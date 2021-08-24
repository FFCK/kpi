import { Model } from '@vuex-orm/core'

export default class Errors extends Model {
  static entity = 'errors'

  static fields () {
    return {
      id: this.number(1),
      offline: this.boolean(false),
      errorMessage: this.string('')
    }
  }
}
