/* eslint-disable */
const wsPORT_OFFSET_COMMAND = 5
const wsPORT_OFFSET_BROADCAST = 6
const wsPORT_OFFSET_FLOW = 7
const wsPORT_OFFSET_FLOWBINARY = 8
const wsPORT_OFFSET_CONTROL = 9

const wsContext = {
  url: 'ws://localhost',
  port: 20000,

  mapCommand: new Map(),
  mapControl: new Map(),
  mapBroadcast: new Map(),
  mapFlow: new Map()
}

wsContext.OpenWebSocketCommand = function (fnOpen = undefined, fnClose = undefined) {
  wsContext.wsCommand = new WebSocket(wsContext.url + ':' + (wsContext.port + wsPORT_OFFSET_COMMAND).toString())

  if (fnOpen != undefined) { wsContext.wsCommand.onopen = fnOpen }

  wsContext.wsCommand.onmessage = function (evt) {
    var objJSON = null

    try {
      objJSON = JSON.parse(evt.data)
    } catch (e) {
      alert('Parsing error data :' + evt.data)
      alert('Parsing error:' + e)
      return
    }

    if (objJSON && typeof objJSON === 'object' && typeof objJSON.key === 'string') {
      const fn = wsContext.mapCommand.get(objJSON.key)
      if (fn && typeof fn === 'function') { fn(objJSON) }
    }
  }

  if (fnClose != undefined) { wsContext.wsCommand.onclose = fnClose } else { wsContext.wsCommand.onclose = function () { wsContext.wsCommand = null } }
}

wsContext.OpenWebSocketControl = function (fnOpen = undefined, fnClose = undefined) {
  wsContext.wsControl = new WebSocket(wsContext.url + ':' + (wsContext.port + wsPORT_OFFSET_CONTROL).toString())

  if (fnOpen != undefined) { wsContext.wsControl.onopen = fnOpen }

  wsContext.wsControl.onmessage = function (evt) {
    var objJSON = null

    try {
      objJSON = JSON.parse(evt.data)
    } catch (e) {
      alert('Parsing error data :' + evt.data)
      alert('Parsing error:' + e)
      return
    }

    if (objJSON && typeof objJSON === 'object' && typeof objJSON.key === 'string') {
      const fn = wsContext.mapControl.get(objJSON.key)
      if (fn && typeof fn === 'function') { fn(objJSON) }
    }
  }

  if (fnClose != undefined) { wsContext.wsControl.onclose = fnClose } else { wsContext.wsControl.onclose = function () { wsContext.wsControl = null } }
}

wsContext.OpenWebSocketBroadcast = function (fnOpen = undefined, fnClose = undefined) {
  wsContext.wsBroadcast = new WebSocket(wsContext.url + ':' + (wsContext.port + wsPORT_OFFSET_BROADCAST).toString())

  if (fnOpen != undefined) { wsContext.wsBroadcast.onopen = fnOpen }

  wsContext.wsBroadcast.onmessage = function (evt) {
    var objJSON = null

    try {
      objJSON = JSON.parse(evt.data)
    } catch (e) {
      alert('Parsing error:' + e)
      return
    }

    if (objJSON && typeof objJSON === 'object' && typeof objJSON.key === 'string') {
      const fn = wsContext.mapBroadcast.get(objJSON.key)
      if (fn && typeof fn === 'function') { fn(objJSON) }
    }
  }

  if (fnClose != undefined) { wsContext.wsBroadcast.onclose = fnClose } else { wsContext.wsBroadcast.onclose = function () { wsContext.wsBroadcast = null } }
}

wsContext.OpenWebSocketFlow = function (fnOpen = undefined, fnClose = undefined) {
  wsContext.wsFlow = new WebSocket(wsContext.url + ':' + (wsContext.port + wsPORT_OFFSET_FLOW).toString())

  if (fnOpen != undefined) { wsContext.wsFlow.onopen = fnOpen }

  wsContext.wsFlow.onmessage = function (evt) {
    var objJSON = null

    try {
      objJSON = JSON.parse(evt.data)
    } catch (e) {
      alert('Parsing error:' + e)
      return
    }

    if (objJSON && typeof objJSON === 'object' && typeof objJSON.key === 'string') {
      const fn = wsContext.mapFlow.get(objJSON.key)
      if (fn && typeof fn === 'function') { fn(objJSON) }
    }
  }

  if (fnClose != undefined) {
    wsContext.wsFlow.onclose = fnClose
  } else {
    wsContext.wsFlow.onclose = function () {
      wsContext.wsFlow = null
    }
  }
}

export default wsContext
