import VuexORM from '@vuex-orm/core'
import Status from '@/store/models/Status'

const database = new VuexORM.Database()

database.register(Status)

export default database
