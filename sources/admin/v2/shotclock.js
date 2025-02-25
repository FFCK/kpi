
const mychannel = new BroadcastChannel('kpi_channel')

const shotclockSpan = document.getElementById('shotclock')
const timerSpan = document.getElementById('timer')
const container2 = document.getElementById('container2')

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
                container2.classList.add("stop")
            } else {
                container2.classList.remove("stop")
            }
            break;
        case 'timer':
            timerSpan.textContent = message.value
            break;
        default:
            console.log('Unknown message type:', message.type);
            break;
    }
}

mychannel.postMessage({ type: 'shotclock', value: 'ready' })
