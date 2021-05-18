import axios from 'axios'
import VuexORM from '@vuex-orm/core'
import VuexORMAxios from '@vuex-orm/plugin-axios'
import Photo from '@/store/models/Photo'
import User from '@/store/models/User'

VuexORM.use(VuexORMAxios, {
  axios,
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8087/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json'
  }
})

const database = new VuexORM.Database()

database.register(Photo)
database.register(User)

export default database
