import VuexORM from '@vuex-orm/core'
import User from '@/store/models/User'
import Preferences from '@/store/models/Preferences'
import Events from '@/store/models/Events'
import Games from '@/store/models/Games'
import Status from '@/store/models/Status'

const database = new VuexORM.Database()

database.register(User)
database.register(Preferences)
database.register(Events)
database.register(Games)
database.register(Status)

export default database
