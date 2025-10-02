import Dexie from 'dexie';

const db = new Dexie('app2');
db.version(1).stores({
  preferences: '&id',
  games: '&eventId, timestamp',
  charts: '++id, eventId, timestamp',
});

export default db;
