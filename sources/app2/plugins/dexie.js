// plugins/dexie.js
import Dexie from 'dexie'

const createDb = (dbName, storeName) => {
  const db = new Dexie(dbName)
  db.version(1).stores({
    [storeName]: '&id',
  })
  return db
}

export default createDb