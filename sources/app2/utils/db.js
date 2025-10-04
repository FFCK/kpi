import Dexie from 'dexie';

const db = new Dexie('app2');
db.version(1).stores({
  preferences: '&id',
  games: '&eventId, timestamp',
  charts: '++id, eventId, timestamp',
});

db.version(2).stores({
  preferences: '&id',
  games: '&eventId, timestamp',
  charts: '++id, eventId, timestamp',
  user: '&id',
});

db.version(3).stores({
  preferences: '&id',
  games: '&eventId, timestamp',
  charts: '++id, eventId, timestamp',
  user: null, // Remove user table
});

db.version(4).stores({
  preferences: '&id',
  games: '&eventId, timestamp',
  charts: '++id, eventId, timestamp',
  stars: '&id, timestamp',
});

export default db;
