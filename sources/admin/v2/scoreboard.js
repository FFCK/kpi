
const mychannel = new BroadcastChannel('kpi_channel')

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
                // shotclockSpan.textContent = parseInt(message.value, 10)
                shotclockSpan.textContent = message.value
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
            /* RAZ des pénalités */
            const parentElementA = document.querySelector('#penaltyA')
            while (parentElementA.firstChild) {
                parentElementA.removeChild(parentElementA.firstChild)
            }
            const parentElementB = document.querySelector('#penaltyB')
            while (parentElementB.firstChild) {
                parentElementB.removeChild(parentElementB.firstChild)
            }
            break;
        case 'scores':
            const scoreASpan = document.getElementById('scoreA')
            const scoreBSpan = document.getElementById('scoreB')
            scoreASpan.textContent = message.value.scoreA
            scoreBSpan.textContent = message.value.scoreB
            break;
        case 'penA':
            if (message.value.time !== null) {
                let penASpan = document.querySelector('#penA-' + message.value.id + ' span.pen-timer')
                if (!penASpan) {
                    const penADiv = document.createElement('div')
                    penADiv.id = 'penA-' + message.value.id
                    penADiv.classList.add('pen-' + message.value.type)
                    const penASpan = document.createElement('span')
                    penASpan.classList.add('pen-timer')
                    penASpan.textContent = message.value.time
                    penADiv.appendChild(penASpan)
                    const parentElement = document.querySelector('#penaltyA')
                    parentElement.appendChild(penADiv)
                } else {
                    penASpan.textContent = message.value.time
                }
            } else {
                let penADiv = document.querySelector('#penA-' + message.value.id)
                if (penADiv) { penADiv.remove() }
            }
            
            break;
        case 'penB':
            if (message.value.time !== null) {
                let penBSpan = document.querySelector('#penB-' + message.value.id + ' span.pen-timer')
                if (!penBSpan) {
                    const penBDiv = document.createElement('div')
                    penBDiv.id = 'penB-' + message.value.id
                    penBDiv.classList.add('pen-' + message.value.type)
                    const penBSpan = document.createElement('span')
                    penBSpan.classList.add('pen-timer')
                    penBSpan.textContent = message.value.time
                    penBDiv.appendChild(penBSpan)
                    const parentElement = document.querySelector('#penaltyB')
                    parentElement.appendChild(penBDiv)
                } else {
                    penBSpan.textContent = message.value.time
                }
            } else {
                let penBDiv = document.querySelector('#penB-' + message.value.id)
                if (penBDiv) { penBDiv.remove() }
            }
            break;
        default:
            console.log('Unknown message type:', message.type);
            break;
    }
}

mychannel.postMessage({ type: 'scoreboard', value: 'ready' })
