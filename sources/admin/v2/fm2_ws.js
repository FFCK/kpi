let socket = new WebSocket("ws://localhost:7681", "lws-minimal")

socket.onopen = function (e) {
  console.log("[open] Connection established")
  console.log("Sending to server")
  socket.send("Connection established - Client 1")
}

socket.onmessage = function (event) {
  console.log(`${event.data}`)
}

socket.onclose = function (event) {
  if (event.wasClean) {
    console.log(`[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`)
  } else {
    // e.g. server process killed or network down
    // event.code is usually 1006 in this case
    console.log('[close] Connection died')
  }
}

socket.onerror = function (error) {
  console.log(`[error] ${error.message}`)
}
