
const mychannel = new BroadcastChannel('my_channel')

const shotclockSpan = document.getElementById('shotclock')

mychannel.onmessage = (event) => {
    console.log(event.data)
    const message = event.data
    switch (message.type) {
        case 'shotclock':
            if (message.value === '') {
                shotclockSpan.style.display = 'none'
            } else {
                shotclockSpan.style.display = 'block'
                shotclockSpan.textContent = parseInt(message.value, 10)
            }
            break;
        default:
            console.log('Unknown message type:', message.type);
            break;
    }
}

