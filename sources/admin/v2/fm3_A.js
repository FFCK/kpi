/* 
* Feuille de marque en ligne
* Javascript partie A
*/
const formatTime = (time) => {
    const timeArray = time.split(':')
    if (timeArray[0] < 10) {
        timeArray[0] = '0' + timeArray[0]
    }
    return timeArray.join(':')
}

const formatPartTime = (time) => {
    return time < 10 ? '0' + time : time.toString()
}


function millisecondsToMinutesAndSeconds(milliseconds) {
    const minutes = Math.floor(milliseconds / 60000)
    const seconds = Math.floor((milliseconds % 60000) / 1000)
    const secondTenths = Math.floor((milliseconds % 1000) / 100)
    return {'minutes': minutes, 'seconds': seconds, 'secondTenths': secondTenths}
}

const cleanStr = (str) => {
    return str.replace(/\s+/g, '')
}

const channel = new BroadcastChannel('kpi_channel')
channel.onmessage = (event) => {
    // console.log(event.data)
    const message = event.data
    switch (message.type) {
        case 'scoreboard':
            if (message.value === 'ready') {
                $('#update_scoreboard_button').click()
            }
            break;
        case 'shotclock':
            if (message.value === 'ready') {
                broadcastPost('timer_status', timerStatus)
                broadcastPost('shotclock')
                broadcastPost('timer')
            }

    }
}

const broadcastPost = (type, value = null) => {
    switch (type) {
        case 'timer':
            const broadcastTimer = $('#heure').val()
            channel.postMessage({'type': 'timer', 'value': broadcastTimer})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'chrono', v: {time: broadcastTimer, run: timerStatus !== 'stop'}}
                ))
            }
            break;
        case 'shotclock':
            channel.postMessage({'type': 'shotclock', 'value': cleanStr($('#shotclock').val())})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'posses', v: cleanStr($('#shotclock').val())}
                ))
            }
            break;
        case 'timer_status':
            channel.postMessage({'type': 'timer_status', 'value': value})
            break;
        case 'period':
            channel.postMessage({'type': 'period', 'value': periode_en_cours})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'period', v: periode_en_cours}
                ))
            }
            break;
        case 'teams':
            channel.postMessage({'type': 'teams', 'value': {'teamA': labelEquipeA, 'teamB': labelEquipeB, 'nationA': nationA, 'nationB': nationB}})
            break;
        case 'scores':
            const broadcastScoreA = cleanStr($('#scoreA').text())
            const broadcastScoreB = cleanStr($('#scoreB').text())
            channel.postMessage({'type': 'scores', 'value': {'scoreA': broadcastScoreA, 'scoreB': broadcastScoreB}})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'scoreA', v: broadcastScoreA}
                ))
                setTimeout(() => {
                    socket.send(JSON.stringify(
                        {p: socketTarget, t: 'scoreB', v: broadcastScoreB}
                    ))
                }, 50)
            }
            break;
        case 'penA':
            channel.postMessage({'type': 'penA', 'value': value})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'penA', v: value.nb}
                ))
            }                
            break;
        case 'penB':
            channel.postMessage({'type': 'penB', 'value': value})
            if (socket && socket.isopen) {
                socket.send(JSON.stringify(
                    {p: socketTarget, t: 'penB', v: value.nb}
                ))
            }
            break;
        default:
            console.log('Unknown message type:', type);
            break;
    }
}

function broadcastPens () {
    const divPen = document.querySelectorAll('#zonePenalites div.pen')
    divPen.forEach((iteration) => {
        const equipe = iteration.classList.contains('pen-A') ? 'A' : 'B'
        const id = iteration.getAttribute('data-id')
        const type = iteration.getAttribute('data-type')
        const time = iteration.querySelector('.pen-timer').textContent
        broadcastPost('pen' + equipe, {'nb': pen[equipe], 'id': id, 'type': type, 'time': time})
    })
}

function Raz () {
    $('#heure').val(minut_max + ':' + second_max)
    broadcastPost('timer')
    broadcastPost('timer_status', 'stop')
}


/* MainTimer EasyTimer */
const mainTimerStart = () => {
    mainTimer.start()
    mainTimerDisplay()
    $('#heure').css('background-color', '#009900')
    adjustTimerStart()
    shotclockStart()
}

const mainTimerPause = () => {
    mainTimer.pause()
    mainTimerDisplay()
    $('#heure').css('background-color', '#990000')
    adjustTimerPause()
    shotclockPause()
}

const mainTimerDisplay = () => {
    if (mainTimer.getTotalTimeValues().seconds < mainTimerStep) {
        $('#heure').val(mainTimer.getTimeValues().minutes + ':' + formatPartTime(mainTimer.getTimeValues().seconds) + '.' + mainTimer.getTimeValues().secondTenths)
    } else {
        $('#heure').val(mainTimer.getTimeValues().minutes + ':' + formatPartTime(mainTimer.getTimeValues().seconds))
    }
    broadcastPost('timer')
}
const mainTimerUpdate = () => {
    mainTimerDisplay()
}

const mainTimerReset = () => {
    mainTimerPause()
    mainTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {minutes: mainTimerDefault}})
    mainTimerEventListenerSeconds()
    mainTimerDisplay()
    adjustTimerReset()
    shotclockReset()
}

/* Adjust Timer */
const adjustTimerCheck = () => {
    if (adjustTimer === null) {
        adjustTimer = new easytimer.Timer(
            {
                countdown: true,
                precision: 'seconds',
                startValues: {
                    minutes: mainTimer.getTimeValues().minutes,
                    seconds: mainTimer.getTimeValues().seconds
                }
            }
        )
        timerStatus === 'stop' ? adjustTimer.pause() : adjustTimer.start()
        adjustTimer.addEventListener('secondsUpdated', adjustTimerDisplay)
    }
}

const adjustTimerAdjust = (value) => {
    adjustTimerCheck()
    const minutes = adjustTimer.getTimeValues().minutes
    const seconds = adjustTimer.getTimeValues().seconds
    adjustTimer.setParams({countdown: true, precision: 'seconds', startValues: {
        minutes: minutes,
        seconds: seconds + value
    }})
    if (adjustTimer.getTotalTimeValues().seconds < 0) {
        adjustTimer.setParams({countdown: true, precision: 'seconds', startValues: {
            minutes: 0,
            seconds: 0
        }})
    } else if (adjustTimer.getTotalTimeValues().seconds > mainTimerDefault * 60) {
        adjustTimer.setParams({countdown: true, precision: 'seconds', startValues: {
            minutes: mainTimerDefault,
            seconds: 0
        }})
    }
    adjustTimerDisplay()
    $('#updateChrono').show()
}
const adjustTimerConfirm = () => {
    mainTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {
        minutes: adjustTimer.getTimeValues().minutes,
        seconds: adjustTimer.getTimeValues().seconds
    }})
    mainTimerDisplay()
    adjustTimerReset()
}

const adjustTimerReset = () => {
    if (adjustTimer !== null) {
        adjustTimer.removeEventListener('secondsUpdated', adjustTimerDisplay)
    }
    adjustTimer = null
    $('#updateChrono').hide()
}

const adjustTimerStart = () => {
    if (adjustTimer !== null) {
        adjustTimer.start()
    }
}

const adjustTimerPause = () => {
    if (adjustTimer !== null) {
        adjustTimer.pause()
    }
}

const adjustTimerDisplay = () => {
    const minutes = formatPartTime(adjustTimer.getTimeValues().minutes)
    const secondes = formatPartTime(adjustTimer.getTimeValues().seconds)
    $('#chronoText').text(minutes + ':' + secondes)
}

/* ShotclockTimer EasyTimer */
const shotclockStart = () => {
    shotclockTimer.start()
    shotClockShow = (mainTimer.getTotalTimeValues().seconds >= shotclockDefault)
    shotclockDisplay()
}

const shotclockPause = () => {
    shotclockTimer.pause()
}

const shotclockDisplay = () => {
    if (!shotClockShow) {
        $('#shotclock').val('--')
    } else if (shotclockTimer.getTotalTimeValues().seconds < shotclockStep) {
        $('#shotclock').val(shotclockTimer.getTotalTimeValues().seconds + '.' + shotclockTimer.getTimeValues().secondTenths)
    } else {
        $('#shotclock').val(shotclockTimer.getTotalTimeValues().seconds)
    }
    broadcastPost('shotclock')
}
const shotclockUpdate = () => {
    shotclockDisplay()
}

const shotclockReset = () => {
    shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: shotclockDefault}})
    if (timerStatus !== 'stop' && timerStatus !== undefined) {
        shotclockStart()
    }
    shotClockShow = (mainTimer.getTotalTimeValues().seconds >= shotclockDefault)
    shotclockDisplay()

    // TODO: vérifier si mainTimer < 1 minute => ne plus afficher shotclock
}

shotclockTimer.addEventListener('secondTenthsUpdated', shotclockUpdate)

shotclockTimer.addEventListener('targetAchieved', () => {
    buzzer2()
})


// Messages 
function avertissement (texte, level = 'info') {
    if (level === 'danger') {
        $('#avert').append('<div class="avertText danger">' + texte + '</div>')
        $('.avertText:last').show('blind', {}, 500).text(texte).delay(5000).fadeOut(800)
    } else if (level === 'success') {
        $('#avert').append('<div class="avertText success">' + texte + '</div>')
        $('.avertText:last').show('blind', {}, 500).text(texte).delay(5000).fadeOut(800)
    } else {
        $('#avert').append('<div class="avertText">' + texte + '</div>')
        $('.avertText:last').show('blind', {}, 500).text(texte).delay(2000).fadeOut(800)
    }
}
// Alert
function custom_alert (output_msg, title_msg) {
    if (output_msg == '')
        output_msg = lang.Aucun_message
    if (title_msg == '')
        title_msg = lang.Attention
    $('div.simple_alert').remove()
    $("<div></div>").html(output_msg).dialog({
        dialogClass: 'simple_alert',
        title: title_msg,
        resizable: false,
        modal: true,
        buttons: {
            "Ok": function () {
                $(this).dialog("close")
            }
        }
    })
}

// Queue Alert
function queueAlert() {
    let queue = $('table#list tbody tr.danger')
    if (queue.length > 0) {
        custom_alert('Multiple events could not be transmitted to the server (See red lines below)', 'Offline !!!')
        console.log('queue.length', queue.length)
        return true
    }
    return false
}

let timeoutSetChrono
let timeoutUpdateChrono

// Retry
function serverUpdate(target, object, iteration = 0) {
    // console.log(target, object, iteration)

    const delay = [0, 5, 10, 15, 30, 60, 60, 60, 60, 60, 60, 60, 60, 60]
    // const delay = [0, 5, 6, 7, 8, 9]
    let level = 'info'

    if (iteration > 0) {
        avertissement(target + ' ' + object.action + ' : ' + lang.Nouvelle_tentative + delay[iteration] + lang.secondes)
        level = 'success'
    }
    if (target === 'evt_match') {
        if (iteration === 0) {
            avertissement('Processing')
        }
        setTimeout(() => {
            $.post(
                'v2/evt_match.php',
                {
                    idMatch: object.idMatch,
                    ligne: object.ligne,
                    idLigne: object.idLigne,
                    action: object.action
                },
                function (data) {
                    if (data.id === object.idLigne.replace('ligne_', '')) {
                        if (iteration > 0) {
                            avertissement(lang.Transmission_reussie, level)
                        }
                        $('tr#' + object.idLigne).removeClass('danger')
                        if (object.action === 'delete') {
                            $('tr#' + object.idLigne).hide()
                        }
                        // Update score
                        serverUpdate('StatutPeriode', {idMatch: idMatch, type: 'ProvisionnalScore'})
                    }
                },
                'json'
            ).fail(function(){
                $('tr#' + object.idLigne).addClass('danger')
                iteration++
                if (iteration < delay.length) {
                    serverUpdate(target, object, iteration)
                } else {
                    avertissement(lang.Transmission_echouee, 'danger')
                }
            })
        }, delay[iteration] * 1000)

    } else if (target === 'updateChrono') {
        if (typeof(timeoutUpdateChrono) !== 'undefined') {
            clearTimeout(timeoutUpdateChrono)
        }
        timeoutUpdateChrono = setTimeout(() => {
            const penalties = []
            Object.keys(penalites.detail).forEach((key) => {
                penalties[key] = penalites.detail[key].params
                penalties[key].timer = penalites.detail[key].timer.getTotalTimeValues().secondTenths
            })
            $.post(
                'v2/ajax_updateChrono.php',
                {
                    idMatch: object.idMatch,
                    start_time: Date.now() + mainTimer.getTotalTimeValues().secondTenths * 100 - mainTimerDefault * 60000,
                    run_time: mainTimer.getTotalTimeValues().secondTenths * 100,
                    shotclock: shotclockTimer.getTotalTimeValues().secondTenths * 100,
                    penalties: penalties.length > 0 ? JSON.stringify(penalties.filter(element => element !== null)) : null
                },
                function (data) {
                    if (data == 'OK') {
                        // avertissement('Update chrono', level)
                    }
                },
                'text'
            ).fail(function(){
                iteration++
                if (iteration < delay.length) {
                    serverUpdate(target, object, iteration)
                } else {
                    avertissement(lang.Transmission_echouee, 'danger')
                }
            })
        }, delay[iteration] * 1000)

    } else if (target === 'setChrono') {
        if (iteration === 0) {
            avertissement('Processing')
        }
        if (typeof(timeoutSetChrono) !== 'undefined') {
            clearTimeout(timeoutSetChrono)
        }
        timeoutSetChrono = setTimeout(() => {
            const penalties = []
            Object.keys(penalites.detail).forEach((key) => {
                penalties[key] = penalites.detail[key].params
                penalties[key].timer = penalites.detail[key].timer.getTotalTimeValues().secondTenths
            })
            $.post(
                'v2/setChrono.php',
                {
                    idMatch: object.idMatch,
                    action: object.action,
                    start_time: Date.now() + mainTimer.getTotalTimeValues().secondTenths * 100 - mainTimerDefault * 60000,
                    run_time: mainTimer.getTotalTimeValues().secondTenths * 100,
                    max_time: formatPartTime(mainTimerDefault) + ':00',
                    shotclock: shotclockTimer.getTotalTimeValues().secondTenths * 100,
                    penalties: penalties.length > 0 ? JSON.stringify(penalties.filter(element => element !== null && element.timer > 0)) : null
                },
                function (data) {
                    if (data == 'OK') {
                        if (iteration > 0) {
                            avertissement(object.action + ' chrono', level)
                        }
                    }
                },
                'text'
            ).fail(function(){
                iteration++
                if (iteration < delay.length) {
                    serverUpdate(target, object, iteration)
                } else {
                    avertissement(lang.Transmission_echouee, 'danger')
                }
            })
        }, delay[iteration] * 1000)

    } else if (target === 'StatutPeriode') {
        // if (iteration === 0) {
        //     avertissement('Processing')
        // }
        $.post(
            'v2/StatutPeriode.php',
            {
                Id_Match: object.idMatch,
                Valeur: object.valeur || $('#scoreA').text() + '-' + $('#scoreB').text(),
                TypeUpdate: object.type
            },
            function (data) {
                if (data == 'OK') {
                    if (iteration > 0) {
                        avertissement('Update ' +  object.type, level)
                    }
                }
            },
            'text'
        ).fail(function(){
            iteration++
            if (iteration < delay.length) {
                serverUpdate(target, object, iteration)
            } else {
                avertissement(lang.Transmission_echouee, 'danger')
            }
        })

    }
}


function statutActive (leStatut, leClick) {
    if (leStatut == 'ATT') {
        $('#zoneTemps, .periode, #zoneChrono, #zoneScoreboard').hide()
        $('.endmatch').hide()
    } else if (leStatut == 'ON') {
        $('.joueurs, #zoneTemps, #M1, #M2, #zoneChrono, #zoneScoreboard').show()
        $('.endmatch').hide()
        if (typeMatch == 'E') {
            $('#P1, #P2, #TB').show()
        }
    } else if (leStatut == 'END') {
        if (leClick == 'O') {
            avertissement(lang.Fin_match)
            avertissement(lang.Saisissez_heure_fin)
            var timestamp = Date.now()
            var end_time = new Date(timestamp)
            var end_hours = end_time.getHours()
            if (end_hours < 10) {
                end_hours = '0' + end_hours
            }
            var end_minuts = end_time.getMinutes()
            if (end_minuts < 10) {
                end_minuts = '0' + end_minuts
            }
            if ($('#end_match_time').val() == '00:00' || $('#end_match_time').val() == '00h00') {
                $('#time_end_match').val(end_hours + 'h' + end_minuts)
            } else {
                $('#time_end_match').val($('#end_match_time').val())
            }
            $('#commentaires').val($('#comments').text().replace(lang.Cliquez_pour_modifier + '...', ''))
            $('#dialog_end_match').dialog('open')
            $('#reset_evt').click()
        } else {
            $('#zoneTemps, .periode, #zoneChrono, #zoneScoreboard').hide()
            $('#end_match_time').removeClass('inactif').addClass('actif')
        }
    }
}

$(function () {


    $('#updateChrono').hide()
    $.editable.addInputType('autocomplete', { //Plugin Autocomplete pour jEditable
        element: $.editable.types.text.element,
        plugin: function (settings, original) {
            $('input', this).autocomplete(settings.autocomplete)
        }
    })
    $.editable.addInputType('catcomplete', { //Plugin Autocomplete avec categories pour jEditable
        element: $.editable.types.text.element,
        plugin: function (settings, original) {
            $('input', this).catcomplete(settings.autocomplete)
        }
    })
    $.editable.addInputType('spinner', { //Plugin spinner pour jEditable
        element: $.editable.types.text.element,
        plugin: function (settings, original) {
            $('input', this).spinner(settings.spinner)
        }
    })
    $.widget("custom.catcomplete", $.ui.autocomplete, { // Widget autocomplete avec gestion des categories
        _create: function () {
            this._super()
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)")
        },
        _renderMenu: function (ul, items) {
            var that = this,
                currentCategory = ""
            $.each(items, function (index, item) {
                var li
                if (item.category != currentCategory) {
                    ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>")
                    currentCategory = item.category
                }
                li = that._renderItemData(ul, item)
                if (item.category) {
                    li.attr("aria-label", item.category + " : " + item.label)
                }
            })
        }
    })


    $(document).tooltip()
    $("#chrono_ajust").mask("99:99")
    $("#periode_ajust").mask("99:99")
    $("#time_evt").mask("99:99")
    $("#end_match_time, #time_end_match").mask("99h99")
    /* COMPO EQUIPE */
    $('#equipeA, #equipeB').dataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "searching": false,
        bJQueryUI: true,
    })

    //    $('#accordion').accordion({
    //        header: "h3",
    //        heightStyle: "content"
    //    });
    $('#typeMatch').buttonset()
    $('#controleMatch').buttonset()
    $('#publiMatch').buttonset()
    $('#list_up').hide()
    $('#liste_evt').click(function (e) {
        e.preventDefault()
        $('#list, #list_header, #list_up, #list_down').toggle()
    })

    $("#idFeuille").focus()

})
