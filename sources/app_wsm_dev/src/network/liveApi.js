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
  getLogo (numero) {
    return api.get('/live/cache/logos/logo_' + numero + '.json')
  },
  getScore (gameId) {
    return api.get('/live/cache/' + gameId + '_match_score.json')
  },
  getTimer (gameId) {
    return api.get('/live/cache/' + gameId + '_match_chrono.json')
  },
  setEventNetwork (event, network) {
    return api.put('/api/wsm/eventNetwork/' + event, {
      network: network
    })
  },
  setGameParams (gameId, param, value) {
    return api.put('/api/wsm/gameParam/' + gameId, {
      param: param,
      value: value
    })
  },
  setGameEvent (gameId, params) {
    return api.put('/api/wsm/gameEvent/' + gameId, {
      params: params
    })
  },
  setPlayerStatus (gameId, params) {
    return api.put('/api/wsm/playerStatus/' + gameId, {
      params: params
    })
  },
  setGameTimer (gameId, params) {
    return api.put('/api/wsm/gameTimer/' + gameId, {
      params: params
    })
  }
}
