import wsContext from '@/network/ws'
import wsParams from '@/ws_params'

const OnOpenWebSocketBroadcast = () => {
  document.getElementById('message').innerHTML = 'Connexion Broadcast Ok ...'
}

const OnCloseWebSocketBroadcast = () => {
  document.getElementById('message').innerHTML = 'Connexion Broadcast Ko !!!'
}

const OnFlowTiming = (objJSON) => {
  const elClock = document.getElementById('match_horloge')
  if (elClock != null) {
    elClock.innerHTML = objJSON.counter
  }
}

const OnNotifyKP = (objJSON) => {
  if (objJSON.action === 'goal_left') {
    ++wsContext.goal_left
  }

  if (objJSON.action === 'goal_right') {
    ++wsContext.goal_right
  }

  UpdateScore()
}

const UpdateScore = () => {
  const elScore = document.getElementById('score')
  if (elScore !== null) {
    document.getElementById('goal_left').innerHTML = wsContext.goal_left
    document.getElementById('goal_right').innerHTML = wsContext.goal_right
  }
}

const WsInit = async () => {
  await WsCloseAll()
  wsContext.lang = 'en'
  wsContext.url = wsParams.url
  wsContext.port = wsParams.port

  wsContext.goal_left = 0
  wsContext.goal_right = 0

  // Flow Notification
  wsContext.mapFlow.set('<timing>', OnFlowTiming)

  // Broadcast Notification
  wsContext.mapBroadcast.set('<kp>', OnNotifyKP)

  // Ouverture ws
  wsContext.OpenWebSocketBroadcast(OnOpenWebSocketBroadcast, OnCloseWebSocketBroadcast)
  wsContext.OpenWebSocketFlow()
}

const WsBroadcastClose = () => {
  if (wsContext.wsBroadcast) {
    wsContext.wsBroadcast.close()
  }
}

const WsFlowClose = () => {
  if (wsContext.wsFlow) {
    wsContext.wsFlow.close()
  }
}

const WsCloseAll = () => {
  WsBroadcastClose()
  WsFlowClose()
}

export {
  OnOpenWebSocketBroadcast,
  OnCloseWebSocketBroadcast,
  OnFlowTiming,
  OnNotifyKP,
  WsInit,
  WsBroadcastClose,
  WsFlowClose,
  WsCloseAll
}
