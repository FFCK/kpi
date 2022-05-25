
var ably = new Ably.Realtime(ABLY_API_KEY)
ably.connection.on('connected', () => {
  console.log('Connected to Ably!')
})

var channel = ably.channels.get('test')

// Publish a message to the test channel
channel.publish('greeting', 'hello')

// Subscribe to messages on channel
channel.subscribe(function (message) {
  console.log(message.name, message.data)
})