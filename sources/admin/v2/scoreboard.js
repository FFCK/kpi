
const mychannel = new BroadcastChannel('my_channel')

const timerSpan = document.getElementById('timer')
const timerStatusSpan = document.getElementById('timerStatus')
const shotclockSpan = document.getElementById('shotclock')
timerStatusSpan.style.display = 'none'

mychannel.onmessage = (event) => {
    console.log(event.data)
    const message = event.data
    switch (message.type) {
        case 'timer':
            timerSpan.textContent = message.value
            break;
        case 'timer_status':
            if (message.value === 'stop') {
                timerSpan.classList.add("text-danger")
                shotclockSpan.classList.add("text-danger")
            } else {
                timerSpan.classList.remove("text-danger")
                shotclockSpan.classList.remove("text-danger")
            }
            break;
        case 'shotclock':
            if (message.value === '') {
                shotclockSpan.style.display = 'none'
            } else {
                shotclockSpan.style.display = 'inline'
                shotclockSpan.textContent = parseInt(message.value, 10)
            }
            break;
        case 'period':
            const periodSpan = document.getElementById('period')
            switch (message.value) {
                case 'M1':
                    periodSpan.textContent = 'Period 1'
                    break;
                case 'M2':
                    periodSpan.textContent = 'Period 2'
                    break;
                case 'P1':
                case 'P2':
                    periodSpan.textContent = 'Overtime'
                    break;
                case 'TB':
                    periodSpan.textContent = 'TB'
                    break;
            }
            break;
        case 'teams':
            const teamASpan = document.getElementById('teamA')
            const teamBSpan = document.getElementById('teamB')
            const nationAimg = document.getElementById('nationA')
            const nationBimg = document.getElementById('nationB')
            teamASpan.textContent = message.value.teamA
            teamBSpan.textContent = message.value.teamB
            nationAimg.src = '../img/Nations/' + message.value.nationA + '.png'
            nationBimg.src = '../img/Nations/' + message.value.nationB + '.png'
            break;
        case 'scores':
            const scoreASpan = document.getElementById('scoreA')
            const scoreBSpan = document.getElementById('scoreB')
            scoreASpan.textContent = message.value.scoreA
            scoreBSpan.textContent = message.value.scoreB
            break;
        default:
            console.log('Unknown message type:', message.type);
            break;
    }
}

