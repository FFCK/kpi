import axios from 'axios'
import VuexORM from '@vuex-orm/core'
import VuexORMAxios from '@vuex-orm/plugin-axios'
import Photo from '@/store/models/Photo'

VuexORM.use(VuexORMAxios, { axios })

const database = new VuexORM.Database()

database.register(Photo)

export default database
