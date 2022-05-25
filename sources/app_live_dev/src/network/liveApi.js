import api from '@/network/api'

// const force = process.env.NODE_ENV === 'development' ? '/force' : ''

export default {
  getEvents () {
    return api.get('/api/events/all')
  },
  getGameId (event, pitch) {
    return api.get('/live/cache/event' + event + '_pitch' + pitch + '.json')
  },
  getGame (gameId) {
    return api.get('/live/cache/' + gameId + '_match_global.json')
  },
  getScore (gameId) {
    return api.get('/live/cache/' + gameId + '_match_score.json')
  },
  getTimer (gameId) {
    return api.get('/live/cache/' + gameId + '_match_chrono.json')
  }
}
