
const mychannel = new BroadcastChannel('kpi_channel')

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
                // shotclockSpan.textContent = parseInt(message.value, 10)
                shotclockSpan.textContent = message.value
            }
            break;
        case 'timer_status':
            if (message.value === 'stop') {
                shotclockSpan.classList.add("text-danger")
            } else {
                shotclockSpan.classList.remove("text-danger")
            }
            break;
        default:
            console.log('Unknown message type:', message.type);
            break;
    }
}

mychannel.postMessage({ type: 'shotclock', value: 'ready' })
