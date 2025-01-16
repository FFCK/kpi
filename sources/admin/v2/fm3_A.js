/* 
 * Feuille de marque en ligne
 * Javascript partie A
 */

const channel = new BroadcastChannel('my_channel')

const broadcastPost = (type, value = null) => {
    switch (type) {
        case 'timer':
            const broadcastTimer = $('#heure').val()
            channel.postMessage({'type': 'timer', 'value': broadcastTimer})
            break;
        case 'shotclock':
            channel.postMessage({'type': 'shotclock', 'value': $('#shotclock').val()})
            break;
        case 'timer_status':
            channel.postMessage({'type': 'timer_status', 'value': value})
            break;
        case 'period':
            channel.postMessage({'type': 'period', 'value': periode_en_cours})
            break;
        case 'teams':
            channel.postMessage({'type': 'teams', 'value': {'teamA': labelEquipeA, 'teamB': labelEquipeB, 'nationA': nationA, 'nationB': nationB}})
            break;
        case 'scores':
            const broadcastScoreA = $('#scoreA').text()
            const broadcastScoreB = $('#scoreB').text()
            channel.postMessage({'type': 'scores', 'value': {'scoreA': broadcastScoreA, 'scoreB': broadcastScoreB}})
            break;
        default:
            console.log('Unknown message type:', type);
            break;
    }
}

function Raz () {
    //$('#heure').val('00:00');
    $('#heure').val(minut_max + ':' + second_max)

    broadcastPost('timer')
    broadcastPost('timer_status', 'stop')
}

function Horloge () {
    var temp_time = new Date()
    // chrono
    // run_time.setTime(temp_time.getTime() - start_time.getTime());
    // compte à rebours
    var max_time1 = (minut_max * 60000) + (second_max * 1000)
    run_time.setTime(start_time.getTime() + max_time1 - temp_time.getTime())
    $('#run_time_display').text(run_time.toLocaleString()) //debug
    var minut_ = run_time.getMinutes()
    if (minut_ < 10) { minut_ = '0' + minut_ }
    var second_ = run_time.getSeconds()
    if (second_ < 10) { second_ = '0' + second_ }
    $('#heure').val(minut_ + ':' + second_)
    /* Contrôle maxi */
    //if(minut_ >= minut_max && second_ >= second_max)
    if (minut_ <= 0 && second_ <= 0) {
        // Temps écoulé
        buzzer();
        clearInterval(timer)
        //$('#periode_end').text(minut_max + ':' + second_max);
        $('#periode_end').text('00:00')
        $('#stop_button').click()
        $("#dialog_end").dialog("open")
    }

    broadcastPost('timer')
}

const shotclockStart = () => {
    shotclockTimer.start()
    shotclockDisplay()
    broadcastPost('shotclock')
}

const shotclockDisplay = () => {
    if (shotclockTimer.getTotalTimeValues().seconds < shotclockStep) {
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
    shotclockDisplay()
    if (timerStatus !== 'stop' && timerStatus !== undefined) {
        shotclockStart()
    }
}

shotclockTimer.addEventListener('secondTenthsUpdated', shotclockUpdate)

shotclockTimer.addEventListener('targetAchieved', () => {
    buzzer()
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
            $.post(
                'v2/ajax_updateChrono.php',
                {
                    idMatch: object.idMatch,
                    start_time: start_time.getTime(),
                    run_time: run_time.getTime(),
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
            $.post(
                'v2/setChrono.php',
                {
                    idMatch: object.idMatch,
                    action: object.action,
                    start_time: start_time.getTime(),
                    run_time: run_time.getTime(),
                    max_time: minut_max + ':' + second_max
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
            var end_time = new Date()
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


    $('#updateChrono img').hide()
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
