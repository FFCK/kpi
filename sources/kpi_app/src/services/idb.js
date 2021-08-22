import { openDB } from 'idb'

// demo1: Getting started
export function demo1 () {
  openDB('db1', 1, {
    upgrade (db) {
      db.createObjectStore('store1')
      db.createObjectStore('store2')
    }
  })
  openDB('db2', 1, {
    upgrade (db) {
      db.createObjectStore('store3', { keyPath: 'id' })
      db.createObjectStore('store4', { autoIncrement: true })
    }
  })
}

// demo2: add some data into db1/store1/
export async function demo2 () {
  const db1 = await openDB('db1', 1)
  db1.add('store1', 'hello world', 'message')
  db1.add('store1', true, 'delivered')
  db1.close()
}

// demo3: error handling
export async function demo3 () {
  const db1 = await openDB('db1', 1)
  db1
    .add('store1', 'hello again!!', 'new message')
    .then(result => {
      console.log('success!', result)
    })
    .catch(err => {
      console.error('error: ', err)
    })
  db1.close()
}

// demo4: auto generate keys:
export async function demo4 () {
  const db2 = await openDB('db2', 1)
  db2.add('store3', { id: 'cat001', strength: 10, speed: 10 })
  db2.add('store3', { id: 'cat002', strength: 11, speed: 9 })
  db2.add('store4', { id: 'cat003', strength: 8, speed: 12 })
  db2.add('store4', { id: 'cat004', strength: 12, speed: 13 })
  db2.close()
}

// demo5: retrieve values:
export async function demo5 () {
  const db2 = await openDB('db2', 1)
  // retrieve by key:
  db2.get('store3', 'cat001').then(console.log)
  // retrieve all:
  db2.getAll('store3').then(console.log)
  // count the total number of items in a store:
  db2.count('store3').then(console.log)
  // get all keys:
  db2.getAllKeys('store3').then(console.log)
  db2.close()
}

// demo6: overwrite values with the same key
export async function demo6 () {
  // set db1/store1/delivered to be false:
  const db1 = await openDB('db1', 1)
  db1.put('store1', false, 'delivered')
  db1.close()
  // replace cat001 with a supercat:
  const db2 = await openDB('db2', 1)
  db2.put('store3', { id: 'cat001', strength: 99, speed: 99 })
  db2.close()
}

// demo7: move supercat: 2 operations in 1 transaction:
export async function demo7 () {
  const db2 = await openDB('db2', 1)
  // open a new transaction, declare which stores are involved:
  const transaction = db2.transaction(['store3', 'store4'], 'readwrite')
  // do multiple things inside the transaction, if one fails all fail:
  const superCat = await transaction.objectStore('store3').get('cat001')
  transaction.objectStore('store3').delete('cat001')
  transaction.objectStore('store4').add(superCat)
  db2.close()
}

// demo8: transaction on a single store, and error handling:
export async function demo8 () {
  // we'll only operate on one store this time:
  const db1 = await openDB('db1', 1)
  // ↓ this is equal to db1.transaction(['store2'], 'readwrite'):
  const transaction = db1.transaction('store2', 'readwrite')
  // ↓ this is equal to transaction.objectStore('store2').add(..)
  transaction.store.add('foo', 'foo')
  transaction.store.add('bar', 'bar')
  // monitor if the transaction was successful:
  transaction.done
    .then(() => {
      console.log('All steps succeeded, changes committed!')
    })
    .catch(() => {
      console.error('Something went wrong, transaction aborted')
    })
  db1.close()
}

// demo9: very explicitly create a new db and new store
export async function demo9 () {
  const db3 = await openDB('db3', 1, {
    upgrade: (db, oldVersion, newVersion, transaction) => {
      if (oldVersion === 0) upgradeDB3fromV0toV1()

      function upgradeDB3fromV0toV1 () {
        db.createObjectStore('moreCats', { keyPath: 'id' })
        generate100cats().forEach(cat => {
          transaction.objectStore('moreCats').add(cat)
        })
      }
    }
  })
  db3.close()
}

function generate100cats () {
  return new Array(100).fill().map((item, index) => {
    const id = 'cat' + index.toString().padStart(3, '0')
    const strength = Math.round(Math.random() * 100)
    const speed = Math.round(Math.random() * 100)
    return { id, strength, speed }
  })
}

// demo10: handle both upgrade: 0->2 and 1->2
export async function demo10 () {
  const db3 = await openDB('db3', 2, {
    upgrade: (db, oldVersion, newVersion, transaction) => {
      switch (oldVersion) {
        case 0:
          upgradeDB3fromV0toV1()
        // falls through
        case 1:
          upgradeDB3fromV1toV2()
          break
        default:
          console.error('unknown db version')
      }

      function upgradeDB3fromV0toV1 () {
        db.createObjectStore('moreCats', { keyPath: 'id' })
        generate100cats().forEach(cat => {
          transaction.objectStore('moreCats').add(cat)
        })
      }

      function upgradeDB3fromV1toV2 () {
        db.createObjectStore('userPreference')
        transaction.objectStore('userPreference').add(false, 'useDarkMode')
        transaction.objectStore('userPreference').add(25, 'resultsPerPage')
      }
    }
  })
  db3.close()
}

// demo11: upgrade db version even when no schema change is needed:
export async function demo11 () {
  const db3 = await openDB('db3', 3, {
    upgrade: async (db, oldVersion, newVersion, transaction) => {
      switch (oldVersion) {
        case 0:
          upgradeDB3fromV0toV1()
        // falls through
        case 1:
          upgradeDB3fromV1toV2()
        // falls through
        case 2:
          await upgradeDB3fromV2toV3()
          break
        default:
          console.error('unknown db version')
      }

      function upgradeDB3fromV0toV1 () {
        db.createObjectStore('moreCats', { keyPath: 'id' })
        generate100cats().forEach(cat => {
          transaction.objectStore('moreCats').add(cat)
        })
      }
      function upgradeDB3fromV1toV2 () {
        db.createObjectStore('userPreference')
        transaction.objectStore('userPreference').add(false, 'useDarkMode')
        transaction.objectStore('userPreference').add(25, 'resultsPerPage')
      }
      async function upgradeDB3fromV2toV3 () {
        const store = transaction.objectStore('userPreference')
        store.put('English', 'language')
        store.delete('resultsPerPage')
        let colorTheme = 'automatic'
        const useDarkMode = await store.get('useDarkMode')
        if (oldVersion === 2 && useDarkMode === false) colorTheme = 'light'
        if (oldVersion === 2 && useDarkMode === true) colorTheme = 'dark'
        store.put(colorTheme, 'colorTheme')
        store.delete('useDarkMode')
      }
    }
  })
  db3.close()
}

// const db = await openDB(dbName, version, {
//   blocked: () => {
//     // seems an older version of this app is running in another tab
//     console.log(`Please close this app opened in other browser tabs.`);
//   },
//   upgrade: (db, oldVersion, newVersion, transaction) => {
//     // …
//   },
//   blocking: () => {
//     // seems the user just opened this app again in a new tab
//     // which happens to have gotten a version change
//     console.log(`App is outdated, please close this tab`);
//   }
// });

// demo12: create an index on the 100 cats' strength:
export async function demo12 () {
  const db3 = await openDB('db3', 4, {
    upgrade: (db, oldVersion, newVersion, transaction) => {
      // upgrade to v4 in a less careful manner:
      const store = transaction.objectStore('moreCats')
      store.createIndex('strengthIndex', 'strength')
    }
  })
  db3.close()
}

// demo13: get values from index by key
export async function demo13 () {
  const db3 = await openDB('db3', 4)
  const transaction = db3.transaction('moreCats')
  const strengthIndex = transaction.store.index('strengthIndex')
  // get all entries where the key is 9:
  const strongestCats = await strengthIndex.getAll(9)
  console.log('strongest cats: ', strongestCats)
  // get the first entry where the key is 9:
  const oneStrongCat = await strengthIndex.get(9)
  console.log('a strong cat: ', oneStrongCat)
  db3.close()
}

// demo14: get values from index by key using shortcuts:
export async function demo14 () {
  const db3 = await openDB('db3', 4)
  // do similar things as demo13, but use single-action transaction shortcuts:
  const weakestCats = await db3.getAllFromIndex('moreCats', 'strengthIndex', 0)
  console.log('weakest cats: ', weakestCats)
  const oneWeakCat = await db3.getFromIndex('moreCats', 'strengthIndex', 0)
  console.log('a weak cat: ', oneWeakCat)
  db3.close()
}

// demo15: find items matching a condition by using range
export async function demo15 () {
  const db3 = await openDB('db3', 4)
  // create some ranges. note that IDBKeyRange is a native browser API,
  // it's not imported from idb, just use it:
  const strongRange = IDBKeyRange.lowerBound(8)
  const midRange = IDBKeyRange.bound(3, 7)
  const weakRange = IDBKeyRange.upperBound(2)
  const [strongCats, ordinaryCats, weakCats] = [
    await db3.getAllFromIndex('moreCats', 'strengthIndex', strongRange),
    await db3.getAllFromIndex('moreCats', 'strengthIndex', midRange),
    await db3.getAllFromIndex('moreCats', 'strengthIndex', weakRange)
  ]
  console.log('strong cats (strength >= 8): ', strongCats)
  console.log('ordinary cats (strength from 3 to 7): ', ordinaryCats)
  console.log('weak cats (strength <=2): ', weakCats)
  db3.close()
}

// demo16: loop over the store with a cursor
export async function demo16 () {
  const db3 = await openDB('db3', 4)
  // open a 'readonly' transaction:
  const store = db3.transaction('moreCats').store
  // create a cursor, inspect where it's pointing at:
  let cursor = await store.openCursor()
  console.log('cursor.key: ', cursor.key)
  console.log('cursor.value: ', cursor.value)
  // move to next position:
  cursor = await cursor.continue()
  // inspect the new position:
  console.log('cursor.key: ', cursor.key)
  console.log('cursor.value: ', cursor.value)

  // keep moving until the end of the store
  // look for cats with strength and speed both greater than 8
  while (true) {
    const { strength, speed } = cursor.value
    if (strength >= 8 && speed >= 8) {
      console.log('found a good cat! ', cursor.value)
    }
    cursor = await cursor.continue()
    if (!cursor) break
  }
  db3.close()
}

// demo17: use cursor on a range and/or on an index
export async function demo17 () {
  const db3 = await openDB('db3', 4)
  const store = db3.transaction('moreCats').store
  // create a cursor on a very small range:
  const range = IDBKeyRange.bound('cat042', 'cat045')
  let cursor1 = await store.openCursor(range)
  // loop over the range:
  while (true) {
    console.log('cursor1.key: ', cursor1.key)
    cursor1 = await cursor1.continue()
    if (!cursor1) break
  }
  console.log('------------')
  // create a cursor on an index:
  const index = db3.transaction('moreCats').store.index('strengthIndex')
  const cursor2 = await index.openCursor()
  // cursor.key will be the key of the index:
  console.log('cursor2.key:', cursor2.key)
  // the primary key will be located in cursor.primaryKey:
  console.log('cursor2.primaryKey:', cursor2.primaryKey)
  // it's the first item in the index, so it's a cat with strength 0
  console.log('cursor2.value:', cursor2.value)
  db3.close()
}
