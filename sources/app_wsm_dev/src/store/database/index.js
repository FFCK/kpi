import VuexORM from '@vuex-orm/core'
import Status from '@/store/models/Status'
import Preferences from '@/store/models/Preferences'

const database = new VuexORM.Database()

database.register(Status)
database.register(Preferences)

export default database
