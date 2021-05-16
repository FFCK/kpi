import { openDB } from 'idb'

const dbPromise = _ => {
  if (!('indexedDB' in window)) {
    throw new Error('Browser does not support IndexedDB')
  }

  return openDB('kpi', 2, {
    // mise à jour de la structure de base en fonction de la version
    upgrade: (db, oldVersion, newVersion, transaction) => {
      switch (oldVersion) {
        case 0:
          upgradeDBfromV0toV1()
          upgradeDBfromV1toV2()
          break
        case 1:
          upgradeDBfromV1toV2()
          break
        default:
          console.error('unknown db version')
      }

      function upgradeDBfromV0toV1 () {
        db.createObjectStore('preferences', { keyPath: 'id' })
        db.createObjectStore('user', { keyPath: 'id' })
      }

      function upgradeDBfromV1toV2 () {
        db.createObjectStore('Photo', { keyPath: 'id' })
      }
    }
  })
}

const dbCount = async (storeName) => {
  try {
    const db = await dbPromise()
    return db.count(storeName)
  } catch (error) {
    return error
  }
}

const dbFind = async (storeName, id) => {
  try {
    const db = await dbPromise()
    return db.get(storeName, id)
  } catch (error) {
    return error
  }
}

const dbFindAll = async storeName => {
  try {
    const db = await dbPromise()
    return db.getAll(storeName)
  } catch (error) {
    return error
  }
}

const dbPut = async (storeName, tasks) => {
  try {
    const db = await dbPromise()
    const tx = db.transaction(storeName, 'readwrite')
    const store = tx.objectStore(storeName)
    store.put(tasks)
    return tx.done
  } catch (error) {
    return error
  }
}

const dbDelete = async (storeName, id) => {
  try {
    const db = await dbPromise()
    const tx = db.transaction(storeName, 'readwrite')
    const store = tx.objectStore(storeName)
    store.delete(id)
    return id
  } catch (error) {
    return error
  }
}

export default {
  dbCount,
  dbFind,
  dbFindAll,
  dbPut,
  dbDelete
}
