import Dexie from 'dexie';

const db = new Dexie('app2');
db.version(1).stores({
  preferences: '&id',
});

export default db;
