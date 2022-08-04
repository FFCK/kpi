import VuexORM from '@vuex-orm/core'
import Status from '@/store/models/Status'
import Preferences from '@/store/models/Preferences'
import User from '@/store/models/User'

const database = new VuexORM.Database()

database.register(Status)
database.register(Preferences)
database.register(User)

export default database
