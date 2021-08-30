import { openDB } from 'idb'

const dbPromise = _ => {
  if (!('indexedDB' in window)) {
    throw new Error('Browser does not support IndexedDB')
  }

  return openDB('kpi', 1, {
    // mise à jour de la structure de base en fonction de la version
    upgrade: (db, oldVersion, newVersion, transaction) => {
      switch (oldVersion) {
        case 0:
          upgradeDBfromV0toV1()
          break
        default:
          console.error('unknown db version')
      }

      function upgradeDBfromV0toV1 () {
        db.createObjectStore('preferences', { keyPath: 'id' })
        db.createObjectStore('user', { keyPath: 'id' })
        db.createObjectStore('games', { keyPath: 'g_id' })
        db.createObjectStore('charts', { keyPath: 'code' })
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

const dbGet = async (storeName, id) => {
  try {
    const db = await dbPromise()
    return db.get(storeName, id)
  } catch (error) {
    return error
  }
}

const dbGetAll = async storeName => {
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

const dbClear = async (storeName) => {
  try {
    const db = await dbPromise()
    const tx = db.transaction(storeName, 'readwrite')
    const store = tx.objectStore(storeName)
    store.clear()
    return tx.done
  } catch (error) {
    return error
  }
}

export default {
  dbCount,
  dbGet,
  dbGetAll,
  dbPut,
  dbDelete,
  dbClear
}
