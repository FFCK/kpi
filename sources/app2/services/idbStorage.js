import { openDB } from 'idb'

const dbPromise = async () => {
  if (typeof window === 'undefined' || !('indexedDB' in window)) {
    throw new Error('Browser does not support IndexedDB')
  }

  return openDB('kpi', 6, {
    upgrade: (db, oldVersion, newVersion, transaction) => {
      switch (oldVersion) {
        case 0:
        case 1:
        case 2:
        case 3:
        case 4:
          upgradeDBfromV0toV4()
          upgradeDBfromV4toV5()
          upgradeDBfromV5toV6()
          break
        case 5:
          upgradeDBfromV5toV6()
          break
        default:
          console.error('unknown db version')
      }

      function upgradeDBfromV0toV4 () {
        try { db.deleteObjectStore('Photo') } catch (e) {}
        try { db.deleteObjectStore('preferences') } catch (e) {}
        try { db.deleteObjectStore('user') } catch (e) {}
        try { db.deleteObjectStore('games') } catch (e) {}
        try { db.deleteObjectStore('charts') } catch (e) {}
      }

      function upgradeDBfromV4toV5 () {
        db.createObjectStore('preferences', { keyPath: 'id' })
        db.createObjectStore('user', { keyPath: 'id' })
        db.createObjectStore('games', { keyPath: 'g_id' })
        db.createObjectStore('charts', { keyPath: 'code' })
      }

      function upgradeDBfromV5toV6 () {
        db.createObjectStore('reports', { keyPath: 'g_id' })
      }
    }
  })
}

export const dbCount = async (storeName) => {
  try {
    const db = await dbPromise()
    return db.count(storeName)
  } catch (error) {
    return error
  }
}

export const dbGet = async (storeName, id) => {
  try {
    const db = await dbPromise()
    return db.get(storeName, id)
  } catch (error) {
    return error
  }
}

export const dbGetAll = async (storeName) => {
  try {
    const db = await dbPromise()
    return db.getAll(storeName)
  } catch (error) {
    return error
  }
}

export const dbPut = async (storeName, tasks) => {
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

export const dbDelete = async (storeName, id) => {
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

export const dbClear = async (storeName) => {
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
