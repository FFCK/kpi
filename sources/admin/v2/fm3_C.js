/* 
 * Feuille de marque en ligne
 * Javascript partie C : Match non verrouillé
 */

/* WebSocket */
const checkWebSocket = () => {
    if (idEvent <= 0) {
        return;
    }
    fetch(`../live/cache/event${idEvent}_network.json`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Resource not found (404)')
            } else {
                throw new Error('Network response was not ok')
            }
        }
        return response.json();
    })        
    .then(data => {
        webSocketConnect(data.network.global)
    })
    .catch(error => {
        console.error('Error:', error)
        shotclockReset()
        broadcastPost('teams')
        broadcastPost('scores')
        broadcastPost('period')
        broadcastPost('timer')
        broadcastPost('shotclock')
        broadcastPens()

    });
}

const webSocketConnect = (params) => {
    // Créer une nouvelle connexion WebSocket
    socket = new WebSocket(params.url, params.topic);

    // Gérer l'ouverture de la connexion
    socket.onopen = function(event) {
        // Envoyer un message au serveur
        socket.send('Hello server!')
        socket.isopen = true
        avertissement(lang.Connexion_ouverte)
        console.log('Connexion WebSocket ouverte.')
        broadcastPost('teams')
        broadcastPost('scores')
        broadcastPost('timer')
        broadcastPost('shotclock')
        broadcastPost('period')
        broadcastPens()
    };

    // Gérer la réception de messages
    socket.onmessage = (event) => {
        // console.log('Message reçu sur mon_topic:', event.data)
    };

    // Gérer les erreurs
    socket.onerror = function(error) {
        console.error('Erreur WebSocket:', error)
    };

    // Gérer la fermeture de la connexion
    socket.onclose = function(event) {
        socket.isopen = false
        console.log('Connexion WebSocket fermée. Tentative de reconnexion...')
        setTimeout(webSocketConnect, RECONNECT_INTERVAL, params)
    };
}

/* Penalites */
const addPenalite = (type, equipe, startTime = penDefault * 10, serverUpdating = true) => {
    pen[equipe]++
    const newKey = penId
    penalites.detail[newKey] = {}
    penalites.detail[newKey].params = {type: type, equipe: equipe}
    const divElement = document.createElement('div')
    divElement.id = 'pen-' + newKey
    divElement.classList.add('pen')
    divElement.classList.add('pen-' + type)
    divElement.setAttribute('data-type', type)
    divElement.setAttribute('data-id', newKey)
    divElement.classList.add('pen-' + equipe)
    const spanElement = document.createElement('span')
    spanElement.classList.add('pen-timer')
    const buttonRemove = document.createElement('button')
    buttonRemove.classList.add('pen-delete')
    buttonRemove.textContent = 'X'
    buttonRemove.addEventListener('click', (e) => {
        e.preventDefault()
        divElement.remove()
        mainTimer.removeEventListener('started', penalites.detail[newKey].start)
        mainTimer.removeEventListener('paused', penalites.detail[newKey].pause)
        if (penalites.detail[newKey].timer.getTotalTimeValues().secondTenths > 0) {
            delete penalites.detail[newKey]
            pen[equipe]--
            broadcastPost('pen' + equipe, {'nb': pen[equipe], 'id': newKey, 'type': type, 'time': null})
        }
        if (Object.keys(penalites.detail).length == 0) {
            // console.log('No penalites')
            // $('#zonePenalites').hide()
        }
        serverUpdate('updateChrono', {idMatch: idMatch})
    })
    const buttonPlus = document.createElement('button')
    buttonPlus.classList.add('pen-plus')
    buttonPlus.textContent = '+'
    buttonPlus.addEventListener('click', (e) => {
        e.preventDefault()
        penalites.detail[newKey].timer.setParams({countdown: true, precision: 'secondTenths', startValues: {
            seconds: penalites.detail[newKey].timer.getTotalTimeValues().seconds + 1
        }})
        penalites.detail[newKey].display()
        serverUpdate('updateChrono', {idMatch: idMatch})
    })
    const buttonMinus = document.createElement('button')
    buttonMinus.classList.add('pen-minus')
    buttonMinus.textContent = '-'
    buttonMinus.addEventListener('click', (e) => {
        e.preventDefault()
        penalites.detail[newKey].timer.setParams({countdown: true, precision: 'secondTenths', startValues: {
            seconds: penalites.detail[newKey].timer.getTotalTimeValues().seconds - 1
        }})
        penalites.detail[newKey].display()
        serverUpdate('updateChrono', {idMatch: idMatch})
    })
    penalites.detail[newKey].timer = new easytimer.Timer({
        countdown: true,
        precision: 'secondTenths',
        startValues: {
            secondTenths: startTime
        }
    })
    penalites.detail[newKey].start = () => {
        buttonPlus.style.display = 'none'
        buttonMinus.style.display = 'none'
        penalites.detail[newKey].timer.start()
        penalites.detail[newKey].display()
    }
    penalites.detail[newKey].pause = () => {
        buttonPlus.style.display = 'inline'
        buttonMinus.style.display = 'inline'
        penalites.detail[newKey].timer.pause()
        penalites.detail[newKey].display()
    }
    penalites.detail[newKey].display = () => {
        spanElement.textContent = penalites.detail[newKey].timer.getTimeValues().minutes + ':' + formatPartTime(penalites.detail[newKey].timer.getTimeValues().seconds)
        const broadcastValue = penalites.detail[newKey].timer.getTotalTimeValues().secondTenths > 0 ? spanElement.textContent : null
        broadcastPost('pen' + equipe, {'nb': pen[equipe], 'id': newKey, 'type': type, 'time': broadcastValue})
    }
    mainTimer.isRunning() ? penalites.detail[newKey].start() : penalites.detail[newKey].pause()
    penalites.detail[newKey].timer.addEventListener('secondsUpdated', penalites.detail[newKey].display)
    
    penalites.detail[newKey].timer.addEventListener('targetAchieved', () => {
        spanElement.classList.add('pen-achieved')
        pen[equipe]--
        broadcastPost('pen' + equipe, {'nb': pen[equipe], 'id': newKey, 'type': type, 'time': null})
    })
    mainTimer.addEventListener('started', penalites.detail[newKey].start)
    mainTimer.addEventListener('paused', penalites.detail[newKey].pause)

    if (equipe === 'A') {
        divElement.appendChild(buttonRemove)
        divElement.appendChild(spanElement)
        divElement.appendChild(buttonMinus)
        divElement.appendChild(buttonPlus)
    } else {
        divElement.appendChild(buttonMinus)
        divElement.appendChild(buttonPlus)
        divElement.appendChild(spanElement)
        divElement.appendChild(buttonRemove)
    }  
    document.querySelector('#zonePenalites').appendChild(divElement)
    // $('#zonePenalites').show()
    penId++
    if (serverUpdating) {
        serverUpdate('updateChrono', {idMatch: idMatch})
    }
}
document.querySelector('#newPenaliteA').addEventListener('click', () => {
    addPenalite('Custom', 'A')
})
document.querySelector('#newPenaliteB').addEventListener('click', () => {
    addPenalite('Custom', 'B')
})


$(function () {
    $('.fm_bouton').click(function (e) {
        e.preventDefault()
    })

    /* VALIDATION SCORE */
    $('#validScore').buttonset()
    /****************************************************/
    $('#validScore').click(function (event) {
        event.preventDefault()
        if (confirm(lang.Valider_score + ' ' + $('#scoreA').text() + '-' + $('#scoreB').text() + ' ?')) {

            $('#scoreA4').text($('#scoreA').text())
            $('#scoreB4').text($('#scoreB').text())

            serverUpdate('StatutPeriode', {idMatch: idMatch, type: 'Score'})
        }
    })
    /*****************************************************/

    /* OFFICIELS */
    $('.editOfficiel').editable('v2/saveOfficiel.php', {
        style: 'display: inline',
        submit: 'OK',
        cssclass: 'autocompleteOfficiel',
        indicator: '<img src="images/indicator.gif" height="23">',
        submitdata: { idMatch: idMatch },
        type: 'autocomplete',
        placeholder: '<i class="placehold">' + lang.Cliquez_pour_modifier + '</i>',
        //tooltip   : "Clic pour modifier",
        //onblur    : "submit",
        autocomplete: { //parametres transmis au plugin autocomplete
            minLength: 2,
            delay: 200,
            source: 'Autocompl_joueur2.php'
        }
    })
    $('.editArbitres').editable('v2/saveArbitres.php', {
        style: 'display: inline',
        submit: 'OK',
        type: 'catcomplete',
        placeholder: '<i class="placehold">' + lang.Cliquez_pour_modifier + '</i>',
        indicator: '<img src="images/indicator.gif" height="23">',
        submitdata: { idMatch: idMatch },
        //tooltip   : "Clic pour modifier",
        //onblur    : "submit",
        autocomplete: { //parametres transmis au plugin autocomplete
            minLength: 2,
            delay: 200,
            source: 'Autocompl_arb3.php?idMatch=' + idMatch,
            //select: function( event, ui ) {
            //	$( "#MatricTransmit" ).val( ui.item.matric );
            //	return false;
            //}
        },
    })
    // COMPO EQUIPES
    $('.editStatut').editable('v2/saveStatut.php', {
        data: " {'-':'" + lang.Joueur + "','C':'" + lang.Capitaine + "','E':'" + lang.Entraineur + "'}",
        placeholder: '-',
        indicator: '<img src="images/indicator.gif" height="23">',
        submitdata: { idMatch: idMatch },
        type: 'select',
        submit: 'OK',
        callback: function (value, settings) {
            idjoueur = $(this).attr('id').split('-')
            if (value == 'C')
                attrSatut = ' (Cap.)'
            else if (value == 'E')
                attrSatut = ' (Coach)'
            else
                attrSatut = ''
            $('.joueurs[data-id=' + idjoueur[1] + '] .StatutJoueur').text(attrSatut)
        }
    })
    $('.editNo').editable('v2/saveNo.php', {
        style: 'display: inline',
        placeholder: '-',
        submit: 'OK',
        indicator: '<img src="images/indicator.gif" height="23">',
        submitdata: { idMatch: idMatch },
        type: 'spinner',
        callback: function (value, settings) {
            idjoueur = $(this).attr('id').split('-')
            $('.joueurs[data-id=' + idjoueur[1] + ']').attr('data-nb', value).find('.NumJoueur').text(value)
        }
    })
    // SUPPRESSION JOUEUR
    $('.suppression').click(function () {
        matricSupp = $(this).attr('id').split('-')
        $('div.simple_alert').remove()
        $("<div></div>").html(lang.Confirm_suppression_joueur + ' ' + matricSupp[2] + ' ' + lang.Equipe + ' ' + matricSupp[1] + ' ?').dialog({
            dialogClass: 'simple_alert',
            title: lang.Suppression_joueur,
            resizable: false,
            modal: true,
            buttons: {
                "Ok": function () {
                    $(this).dialog("close")
                    $.post(
                        'v2/delJoueur.php', // Le fichier cible côté serveur.
                        {
                            Id_Match: idMatch,
                            Matric: matricSupp[2],
                            Equipe: matricSupp[1]
                        },
                        function (data) { // callback
                            if (data == 'OK') {
                                $('#No-' + matricSupp[2]).parent().remove()
                                $('a.joueurs[data-id=' + matricSupp[2] + ']').remove()
                                custom_alert(lang.Joueur_supprime + ' ' + matricSupp[2], lang.Attention)
                            }
                            else {
                                custom_alert(lang.Action_impossible, lang.Attention)
                            }
                        },
                        'text' // Format des données reçues.
                    )
                },
                "Annuler/Dismiss": function () {
                    $(this).dialog("close")
                    custom_alert(lang.Joueur_non_supprime, lang.Attention)
                }
            }
        })
    })
    // Réinitialisation des présents
    $('#initA').click(function () {
        queueAlert()
        $.post(
            'v2/initPresents.php', // Le fichier cible côté serveur.
            {
                idMatch: idMatch,
                codeEquipe: 'A',
                idEquipe: idEquipeA
            },
            function (data) { // callback
                if (data == 'OK') {
                    window.location = '?idMatch=' + idMatch
                } else {
                    custom_alert(lang.Action_impossible, lang.Attention)
                }
            },
            'text' // Format des données reçues.
        )
    })
    $('#initB').click(function () {
        queueAlert()
        $.post(
            'v2/initPresents.php', // Le fichier cible côté serveur.
            {
                idMatch: idMatch,
                codeEquipe: 'B',
                idEquipe: idEquipeB
            },
            function (data) { // callback
                if (data == 'OK') {
                    window.location = '?idMatch=' + idMatch
                }
                else {
                    custom_alert(lang.Action_impossible, lang.Attention)
                }
            },
            'text' // Format des données reçues.
        )
    })
    // COMMENTAIRES
    $('#comments').editable('v2/saveComments.php', {
        style: 'display: inline',
        placeholder: lang.Cliquez_pour_modifier + '...',
        type: 'textarea',
        indicator: '<img src="images/indicator.gif" height="23">',
        tooltip: lang.Cliquez_pour_modifier,
        submitdata: { idMatch: idMatch },
        submit: 'OK',
    })

    /* TYPE MATCH */
    $('#typeMatchElimination').click(function () {
        if (confirm(lang.Vainqueur_obligatoire_confirmez)) {
            typeMatch = 'E'
            $('#P1, #P2, #TB').show()
            $('#typeMatchImg').attr('src', '../img/typeE.png')

            serverUpdate('StatutPeriode', {idMatch: idMatch, valeur: 'E', type: 'Type'})

        }
    })
    $('#typeMatchClassement').click(function () {
        if (confirm(lang.Egalite_possible)) {
            typeMatch = 'C'
            $('#P1, #P2, #TB').hide()
            $('#typeMatchImg').attr('src', '../img/typeC.png')
            
            serverUpdate('StatutPeriode', {idMatch: idMatch, valeur: 'C', type: 'Type'})
        }
    })
    /* STATUT */
    $('.statut').click(function (event) {
        event.preventDefault()
        if (!$(this).hasClass('ouvert')) {
            return
        }
        valeur = $(this).attr('id')

        if (valeur === 'END') {
            queueAlert()
        }

        $('.statut').removeClass('actif')
        $('#' + valeur).addClass('actif')
        statutActive(valeur, 'O')
        $('#reset_evt').click()

        serverUpdate('StatutPeriode', {idMatch: idMatch, valeur: valeur, type: 'Statut'})

        if (valeur == 'ON') {// && valeur2 == ''
            $('#M1').click()
        }
    })


    $("#dialog_end_opener").click(function () {
        $('#periode_end').text(minut_max + ':' + second_max)
        $("#dialog_end").dialog("open")
    })

    /* PERIODE */
    $('.periode.ouvert').click(function (event) {
        event.preventDefault()
        if (!$(this).hasClass('ouvert')) {
            return
        }
        valeur = $(this).attr('id')
        if ($('#update_evt').attr('data-id') == '') {
            $('.statut').removeClass('actif')
            $('#ON').addClass('actif')
            $('#end_match_time').removeClass('actif').addClass('inactif')
            $('.joueurs, .equipes, .evtButton, .chronoButton, .evtButton2').removeClass('inactif')
            switch (valeur) {
                case 'P1':
                    texte = lang.period_P1 + ' : 5 minutes'
                    minut_max = duree_prolongations
                    second_max = '00'
                    break
                case 'P2':
                    texte = lang.period_P2 + ' : 5 minutes'
                    minut_max = duree_prolongations
                    second_max = '00'
                    break
                case 'TB':
                    texte = lang.period_TB
                    minut_max = '01'
                    second_max = '00'
                    break
                case 'M2':
                    texte = lang.period_M2 + ' : 10 minutes'
                    minut_max = '10'
                    second_max = '00'
                    break
                default:
                    texte = lang.period_M1 + ' : 10 minutes'
                    minut_max = '10'
                    second_max = '00'
                    break
            }
            $('#periode_ajust, #chrono_ajust').val(minut_max + ':' + second_max)
            $('#dialog_ajust_periode').html($(this).html())
            $('#dialog_ajust_selected_period').text($(this).attr('id'))
            $("#dialog_ajust").dialog("open")
            $("#dialog_ajust").parent().find(".ui-dialog-buttonset").first().find("button").first().focus()

            
        } else {
            $('.periode').removeClass('actif')
            $('#' + valeur).addClass('actif')
            $('#time_evt').focus()
        }
    })

    $("#dialog_ajust_opener").click(function () {
        $('#chrono_ajust').val(formatTime($('#heure').val()))
        $('#periode_ajust').val(minut_max + ':' + second_max)
        $('#dialog_ajust_periode').text($('.periode[class="actif"]').attr('id'))
        $("#dialog_ajust").dialog("open")
        $("#dialog_ajust").parent().find(".ui-dialog-buttonset").first().find("button").first().focus()
    })
    $("#heure").click(function () {
        if (timerStatus == 'stop') {
            $('#chrono_ajust').val(formatTime($('#heure').val()))
            $('#periode_ajust').val(minut_max + ':' + second_max)
            $('#dialog_ajust_periode').text($('.periode[class="actif"]').attr('id'))
            $("#dialog_ajust").dialog("open")
            $("#dialog_ajust").parent().find(".ui-dialog-buttonset").first().find("button").first().focus()
        }
    })

    /* BOUTONS MATCH */
    $('.motifCarton').click(function (event) {
        event.preventDefault()
        $('#motif').val($(this).data('motif'))
        $('#motif_texte').val($(this).data('texte'))
        $("#dialog_motif").dialog("close")
        $('#time_evt').focus()
    })
    $('.joueurs, .equipes').click(function (event) {
        event.preventDefault()
        $('.joueurs, .equipes').removeClass('actif')
        $(this).addClass('actif')
        if ($('.evtButton[class*="actif"]').attr('data-evt') !== undefined) {
            if ($('.evtButton[class*="actif"]').data('code') == 'V'
                || $('.evtButton[class*="actif"]').data('code') == 'J'
                || $('.evtButton[class*="actif"]').data('code') == 'R'
                || $('.evtButton[class*="actif"]').data('code') == 'D'
            ) {
                $("#dialog_motif").dialog("open")
            } else {
                $('#motif').val('')
                $('#motif_texte').val('')
                $('#time_evt').focus()
            }
        }
    })
    $('.evtButton').click(function (event) {
        event.preventDefault()
        if (!$(this).hasClass('ouvert')) {
            return
        }
        $('.evtButton').removeClass('actif')
        $(this).addClass('actif')
        if ($('#update_evt').attr('data-id') == '') {
            if ($('#heure').val() != '10:00' || $('#time_evt').val() == '') {
                $('#time_evt').val(formatTime($('#heure').val()))
            }
        }
        $('#valid_evt').removeClass('inactif')
        if ($('.joueurs[class*="actif"]').attr('data-player') !== undefined || $('.equipes[class*="actif"]').attr('data-player') !== undefined) {
            if ($(this).data('code') == 'V'
                || $(this).data('code') == 'J'
                || $(this).data('code') == 'R'
                || $(this).data('code') == 'D'
            ) {
                $("#dialog_motif").dialog("open")
            } else {
                $('#motif').val('')
                $('#motif_texte').val('')
                $('#time_evt').focus()
            }
        }
    })


    /* BUT = TEMPS MORT SYSTEMATIQUE */
    $('#evt_but').click(function (event) {
        if (arret_chrono_sur_but) {
            $('#stop_button').click()
        }
    })

    // INSERT EVENT
    $('#valid_evt').click(function (event) {
        event.preventDefault()
        if (theInEvent)
            return

        theInEvent = true
        var texte
        var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player')
        var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb')
        var ligne_num = ligne_nb + ' - '
        var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id')
        var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe')
        var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt')
        var ligne_motif = $('#motif').val()
        var ligne_motif_texte = $('#motif_texte').val()
        if (ligne_motif_texte != '') {
            ligne_motif_texte = ' (' + ligne_motif_texte + ')'
        }
        if (ligne_evt === undefined) {
            theInEvent = false
            return
        }
        var carton_equipe = 0
        if (ligne_nom === undefined) {
            carton_equipe = 1
            ligne_nom = $('.equipes[class*="actif"]').attr('data-player')
            ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe')
            ligne_num = ''
            ligne_id_joueur = undefined
            if (ligne_equipe === undefined) {
                custom_alert(lang.Selectionnez_equipe_joueur)
                theInEvent = false
                return
            }
        }
        const code_ligne = {}
        code_ligne.period = $('.periode[class*="actif"]').attr('id')
        code_ligne.time = $('#time_evt').val()
        code_ligne.evt = $('.evtButton[class*="actif"]').attr('data-code')
        code_ligne.team = ligne_equipe
        code_ligne.player = ligne_id_joueur
        code_ligne.number = ligne_nb
        code_ligne.cause = ligne_motif
        const id_ligne = 'ligne_' + crypto.randomUUID().replaceAll('-', '')

        texte = $('#time_evt').val() + ' ' + ligne_evt
        texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom

        if (ligne_evt == 'Tir') {
            $('#nb_tirs_' + ligne_equipe).text(parseInt($('#nb_tirs_' + ligne_equipe).text()) + 1)
            $('.evtButton, .joueurs, .equipes').removeClass('actif')
            $('#valid_evt').addClass('inactif')
        } else if (ligne_evt == 'Arret') {
            $('#nb_arrets_' + ligne_equipe).text(parseInt($('#nb_arrets_' + ligne_equipe).text()) + 1)
            $('.evtButton, .joueurs, .equipes').removeClass('actif')
            $('#valid_evt').addClass('inactif')
        } else {
            // texteNom += ' (' + lang.Tir + ')';
            texteTR = '<tr id="' + id_ligne + '">'
            texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>'
            texteBut = '<td class="list_evt">'
            if (ligne_evt == 'But') {
                texteBut += '<img src="v2/but1.png" />'
                $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="v2/but1.png" />')
            }
            texteBut += '</td>'
            if (ligne_nom) {
                texteNom = '<td class="list_nom">' + ligne_num + ligne_nom + ligne_motif_texte + '</td>'
            } else {
                texteNom = '<td class="list_nom">' + lang.Equipe + ' ' + ligne_equipe + ligne_motif_texte + '</td>'
            }
            texteVert = '<td class="list_evt">'
            if (ligne_evt == 'Carton vert') {
                texteVert += '<img src="v2/carton_vert.png" />'
                $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />')
                // si 2 verts...
                var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length
                if (nb_cartons >= 2) {
                    custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
                }
                // si 3 verts dans l'équipe
                var nb_cartons = $('.joueurs[data-equipe="' + ligne_equipe + '"] img[src="v2/carton_vert.png"]').length
                if (nb_cartons > 3) {
                    custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_cette_equipe + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
                }
                // Carton d'équipe
                /*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
                        $('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                        code_ligne.cause += '-teamCard';
                    }
                    if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
                        $('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                        code_ligne.cause += '-teamCard';
                    }
                */
                addPenalite('G', ligne_equipe)
            }
            texteVert += '</td>'
            texteJaune = '<td class="list_evt">'
            if (ligne_evt == 'Carton jaune') {
                texteJaune += '<img src="v2/carton_jaune.png" />'
                $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune.png" />')
                //si 2 jaunes...
                var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune.png"]').length
                if (nb_cartons >= 2) {
                    custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_jaune.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre2 + '.', lang.Attention)
                }
                addPenalite('Y', ligne_equipe)
            }
            texteJaune += '</td>'
            texteRouge = '<td class="list_evt">'
            if (ligne_evt == 'Carton rouge') {
                texteRouge += '<img src="v2/carton_jaune_rouge.png" />'
                $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune_rouge.png" />')
                var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune_rouge.png"]').length
                if (nb_cartons >= 2) {
                    custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_jaune_rouge.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre2 + '.', lang.Attention)
                }
                addPenalite('R', ligne_equipe)
            }
            if (ligne_evt == 'Carton rouge D') {
                texteRouge += '<img src="v2/carton_rouge_' + lang.D + '.png" />'
                $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_rouge_' + lang.D + '.png" />')
                var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_rouge_' + lang.D + '.png"]').length
                if (nb_cartons >= 2) {
                    custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_rouge_' + lang.D + '.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre2 + '.', lang.Attention)
                }
                addPenalite('E', ligne_equipe)
            }
            texteRouge += '</td>'
            texteVide = '<td colspan="5" class="list_evt_vide"></td>'
            texteTR2 = '</tr>'
            $('.evtButton, .joueurs, .equipes').removeClass('actif')
            $('#valid_evt').addClass('inactif')
            if (ligne_equipe == 'A') {
                if (ligne_evt == 'But')
                    $('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1)
                    broadcastPost('scores')
                if (ordre_actuel == 'up') {
                    $('#list').prepend(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2)
                } else {
                    $('#list').append(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2)
                }
            } else {
                if (ligne_evt == 'But')
                    $('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1)
                    broadcastPost('scores')
                if (ordre_actuel == 'up') {
                    $('#list').prepend(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2)
                } else {
                    $('#list').append(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2)
                }
            }
            $('tr[id="' + id_ligne + '"]').attr('data-code', JSON.stringify(code_ligne))

            serverUpdate('evt_match', {idMatch: idMatch, ligne: JSON.stringify(code_ligne), idLigne: id_ligne, action: 'insert'})

            // scroll en haut
            $('html, body').animate({
                scrollTop: $("#description-match").offset().top
            }, 200)
        }
        theInEvent = false


    })

    // RESET
    $('#reset_evt').click(function (event) {
        event.preventDefault()
        $('.evtButton, .joueurs, .equipes').removeClass('actif')
        $('#valid_evt').addClass('inactif').show()
        $('#list tr').removeClass('actif')
        $('#time_evt').val('')
        $('#update_evt').attr('data-id', '').attr('data-code', '')
        $('#zoneTemps a').removeClass('actif2')
        $('#update_evt, #delete_evt').hide()
        //$('#reset_evt').removeClass('evtButton3');
        if (periode_en_cours != '') {
            $('.periode').removeClass('actif')
            $('#' + periode_en_cours).addClass('actif')
        }
        // scroll en haut
        $('html, body').animate({
            scrollTop: $("#description-match").offset().top
        }, 200)
    })

    // EDIT
    $('#list').on("click", 'tr', function () {
        $('#reset_evt').click()
        //periode_en_cours = $('.periode[class*="actif"]').attr('id');
        $('.periode').removeClass('actif')
        $(this).addClass('actif') //Efface la ligne !
        code_ligne = JSON.parse($(this).attr('data-code'))
        id_ligne = $(this).attr('id')
        ancienne_ligne = code_ligne
        $('#zoneTemps a').addClass('actif2')
        $('#update_evt').show().attr('data-code', code_ligne).attr('data-id', id_ligne)
        $('#delete_evt').show()
        //$('#reset_evt').addClass('evtButton3');
        $('#valid_evt').hide()
        $('a[id="' + code_ligne.period + '"]').addClass('actif')
        $('#time_evt').val(code_ligne.time)
        $('a[data-code="' + code_ligne.evt + '"]').addClass('actif')
        if (code_ligne.player != null) {
            $('a[data-id="' + code_ligne.player + '"]').addClass('actif')
        } else {
            $('a.equipes[data-equipe="' + code_ligne.team + '"]').addClass('actif')
        }
        $('#zoneChrono').focus()
    })
    // UPDATE
    $('#update_evt').click(function (event) {
        event.preventDefault()
        var texte
        var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player')
        var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb')
        var ligne_num = ligne_nb + ' - '
        var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id')
        var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe')
        var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt')
        if (ligne_evt === undefined) { return }
        var ligne_motif = $('#motif').val()
        var ligne_motif_texte = $('#motif_texte').val()
        if (ligne_motif_texte != '') {
            ligne_motif_texte = ' (' + ligne_motif_texte + ')'
        }
        var carton_equipe = 0
        if (ligne_nom === undefined) {
            carton_equipe = 1
            ligne_nom = $('.equipes[class*="actif"]').attr('data-player')
            ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe')
            ligne_num = ''
            ligne_id_joueur = undefined
            if (ligne_equipe === undefined) {
                custom_alert(lang.Selectionnez_equipe_joueur)
                return
            }
        }

        ancienne_ligne = JSON.parse($('#list tr.actif').attr('data-code'))
        const code_ligne = {}
        code_ligne.period = $('.periode[class*="actif"]').attr('id')
        code_ligne.time = $('#time_evt').val()
        code_ligne.evt = $('.evtButton[class*="actif"]').attr('data-code')
        code_ligne.team = ligne_equipe
        code_ligne.player = ligne_id_joueur
        code_ligne.number = ligne_nb
        code_ligne.cause = ligne_motif

        texte = $('#time_evt').val() + ' ' + ligne_evt
        texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom

        // suppression anciens éléments
        if (ancienne_ligne.evt == 'B') {
            $('#score' + ancienne_ligne.team + ', #score' + ancienne_ligne.team + '2, #score' + ancienne_ligne.team + '3').text(parseInt($('#score' + ancienne_ligne.team).text()) - 1)
            $('a[data-id="' + ancienne_ligne.player + '"] img[class="c_but"]').first().remove()
        }
        if (ancienne_ligne.evt == 'V') {
            $('a[data-id="' + ancienne_ligne.player + '"] img[src="v2/carton_vert.png"]').first().remove()
            if (ancienne_ligne.number == 'teamCard') {
                // PREMIER CARTON VERT DE CHAQUE JOUEUR !
                $('.joueurs[data-equipe="' + ancienne_ligne.team + '"]').each(function () {
                    $(this).find('img[src="v2/carton_vert.png"]').first().remove()
                })
            }
        }
        if (ancienne_ligne.evt == 'J') {
            $('a[data-id="' + ancienne_ligne.player + '"] img[src="v2/carton_jaune.png"]').first().remove()
        }
        if (ancienne_ligne.evt == 'R') {
            $('a[data-id="' + ancienne_ligne.player + '"] img[src="v2/carton_jaune_rouge.png"]').first().remove()
        }
        if (ancienne_ligne.evt == 'D') {
            $('a[data-id="' + ancienne_ligne.player + '"] img[src="v2/carton_rouge_' + lang.D + '.png"]').first().remove()
        }
        $('tr[id="' + id_ligne + '"] td').remove()

        // insertion nouveaux éléments
        texteTR = '<tr id="' + id_ligne + '">'
        texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>'
        texteBut = '<td class="list_evt">'
        if (ligne_evt == 'But') {
            texteBut += '<img src="v2/but1.png" />'
            $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="v2/but1.png" />')
        }
        texteBut += '</td>'
        if (ligne_nom) {
            texteNom = '<td class="list_nom">' + ligne_num + ligne_nom + ligne_motif_texte + '</td>'
        } else {
            texteNom = '<td class="list_nom">' + lang.Equipe + ' ' + ligne_equipe + ligne_motif_texte + '</td>'
        }
        // if(ligne_evt == 'Arret')
        //         texteNom += ' (' + lang.Tir_contre + ')';
        //     if(ligne_evt == 'Tir')
        //         texteNom += ' (' + lang.Tir + ')';
        // texteNom += '</td>';
        texteVert = '<td class="list_evt">'
        if (ligne_evt == 'Carton vert') {
            texteVert += '<img src="v2/carton_vert.png" />'
            $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />')
            // si 2 verts...
            var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length
            if (nb_cartons >= 2) {
                custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
            }
            // si 3 verts dans l'équipe
            var nb_cartons = $('.joueurs[data-equipe="' + ligne_equipe + '"] img[src="v2/carton_vert.png"]').length
            if (nb_cartons > 3) {
                custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_cette_equipe + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
            }
            /*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
                    $('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                    code_ligne += '-teamCard';
                }
                if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
                    $('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                    code_ligne += '-teamCard';
                }
            */
        }
        texteVert += '</td>'
        texteJaune = '<td class="list_evt">'
        if (ligne_evt == 'Carton jaune') {
            texteJaune += '<img src="v2/carton_jaune.png" />'
            $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune.png" />')
            //si 2 jaunes...
            var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune.png"]').length
            if (nb_cartons >= 2) {
                custom_alert(nb_cartons + 'e <img class="c_carton" src="v2/carton_jaune.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre2 + '.', lang.Attention)
            }
        }
        texteJaune += '</td>'
        texteRouge = '<td class="list_evt">'
        if (ligne_evt == 'Carton rouge') {
            texteRouge += '<img src="v2/carton_jaune_rouge.png" />'
            $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune_rouge.png" />')
            var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune_rouge.png"]').length
            if (nb_cartons >= 2) {
                custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_jaune_rouge.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
            }
        }
        if (ligne_evt == 'Carton rouge D') {
            texteRouge += '<img src="v2/carton_rouge_' + lang.D + '.png" />'
            $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_rouge_' + lang.D + '.png" />')
            var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_rouge_' + lang.D + '.png"]').length
            if (nb_cartons >= 2) {
                custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_rouge_' + lang.D + '.png" /> ' + lang.pour_ce_joueur + ' !<br>' + lang.Avertir_arbitre + '.', lang.Attention)
            }
        }
        texteRouge += '</td>'
        texteVide = '<td colspan="5" class="list_evt_vide"></td>'
        texteTR2 = '</tr>'
        $('.evtButton, .joueurs, .equipes').removeClass('actif')
        $('#valid_evt').addClass('inactif')
        if (ligne_equipe == 'A') {
            if (ligne_evt == 'But') {
                $('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1)
                broadcastPost('scores')
            }
            texte2 = texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide
        } else {
            if (ligne_evt == 'But') {
                $('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1)
                broadcastPost('scores')
            }
            texte2 = texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge
        }

        $('tr[id="' + id_ligne + '"]').attr('data-code', JSON.stringify(code_ligne)).append(texte2)
        $('#reset_evt').click()

        serverUpdate('evt_match', {idMatch: idMatch, ligne: JSON.stringify(code_ligne), idLigne: id_ligne, action: 'update'})

    })

    // DELETE
    $('#delete_evt').click(function (event) {
        event.preventDefault()
        code_ligne = JSON.parse($('#list tr.actif').attr('data-code'))

        // suppression éléments
        if (code_ligne.evt == 'B') {
            $('#score' + code_ligne.team + ', #score' + code_ligne.team + '2, #score' + code_ligne.team + '3').text(parseInt($('#score' + code_ligne.team).text()) - 1)
            $('a[data-id="' + code_ligne.player + '"] img[class="c_but"]').first().remove()
            broadcastPost('scores')
        }
        if (code_ligne.evt == 'V') {
            $('a[data-id="' + code_ligne.player + '"] img[src="v2/carton_vert.png"]').first().remove()
            if (code_ligne.number == 'teamCard') {
                // PREMIER CARTON VERT DE CHAQUE JOUEUR !
                $('.joueurs[data-equipe="' + code_ligne.team + '"]').each(function () {
                    $(this).find('img[src="v2/carton_vert.png"]').first().remove()
                })
            }
        }
        if (code_ligne.evt == 'J') {
            $('a[data-id="' + code_ligne.player + '"] img[src="v2/carton_jaune.png"]').first().remove()
        }
        if (code_ligne.evt == 'R') {
            $('a[data-id="' + code_ligne.player + '"] img[src="v2/carton_jaune_rouge.png"]').first().remove()
        }
        if (code_ligne.evt == 'D') {
            $('a[data-id="' + code_ligne.player + '"] img[src="v2/carton_rouge_' + lang.D + '.png"]').first().remove()
        }
        $('#reset_evt').click()

        serverUpdate('evt_match', {idMatch: idMatch, ligne: JSON.stringify(code_ligne), idLigne: id_ligne, action: 'delete'})

    })

    /**************** CHRONO *******************/

    Raz()
    $('#stop_button').hide()
    $('#run_button').hide()
    // broadcastPost('teams')
    // broadcastPost('period')
    // broadcastPost('scores')
    $.get(
        '../live/cache/' + idMatch + '_match_chrono.json?_=' + Date.now(),
        {},
        function (data) {
            if (data.action == 'start' || data.action == 'run') {
                $('#start_button').hide()
                $('#run_button').hide()
                $('#stop_button').show()
                mainTimerDefault = parseInt(data.max_time.split(':')[0])
                // const runTimeObject = millisecondsToMinutesAndSeconds(parseInt(data.start_time) + mainTimerDefault * 60000 - Date.now())
                const runTime = (parseInt(data.start_time) + mainTimerDefault * 60000 - Date.now()) / 100
                mainTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {
                    secondTenths: runTime
                }})
                if (mainTimer.getTotalTimeValues().seconds < mainTimerStep) {
                    mainTimerEventListenerSecondTenths()
                } else {
                    mainTimerEventListenerSeconds()
                }
                if (data.shotclock) {
                    let shotTime = runTime - (data.run_time - data.shotclock) / 100
                    shotTime = shotTime < 0 ? 0 : shotTime
                    shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {secondTenths: shotTime}})
                }
                if (data.penalties) {
                    JSON.parse(data.penalties).forEach((item) => {
                        let penTime = runTime - (data.run_time / 100 - item.timer)
                        penTime = penTime < 0 ? 0 : penTime
                        addPenalite (item.type, item.equipe, penTime, false)
                    })
                }
                mainTimerStart()

                timerStatus = 'start'
                // broadcastPost('timer_status', timerStatus)
                // broadcastPens()

                avertissement(lang.Chrono + ' ' + lang.en_cours)
                $('#tabs-2_link').click()

            } else if (data.action == 'stop') {
                $('#start_button').hide()
                $('#run_button').show()
                $('#stop_button').hide()
                $('#chrono_moins').show()
                $('#chrono_plus').show()
                mainTimerDefault = parseInt(data.max_time.split(':')[0])
                // const runTimeObject = millisecondsToMinutesAndSeconds(data.run_time)
                mainTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {
                    secondTenths: data.run_time / 100
                }})
                if (mainTimer.getTotalTimeValues().seconds < mainTimerStep) {
                    mainTimerEventListenerSecondTenths()
                } else {
                    mainTimerEventListenerSeconds()
                }
                if (data.shotclock) {
                    shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {secondTenths: data.shotclock / 100}})
                }
                if (data.penalties) {
                    const runTime = (parseInt(data.start_time) + mainTimerDefault * 60000 - Date.now()) / 100
                    JSON.parse(data.penalties).forEach((item) => {
                        addPenalite (item.type, item.equipe, item.timer, false)
                    })
                }

                mainTimerPause()

                timerStatus = 'stop'
                // broadcastPost('timer_status', timerStatus)
                // broadcastPens()

                avertissement(lang.Chrono + ' ' + lang.arrete)
                $('#tabs-2_link').click()
            }
            shotclockDisplay()
            // setTimeout(checkWebSocket, 100)
            checkWebSocket()
        },
        'json'
    ).fail(function(xhr, textStatus, errorThrown) {
        if (xhr.status === 404) {
          console.log("Erreur 404: Page non trouvée");
          checkWebSocket()
        } else {
          console.log("Une erreur s'est produite: " + textStatus + ", " + errorThrown);
        }
    })

    $('#chrono_moins').click(function () {
        if (allowMainTimerUpdateWhileRunning || timerStatus == 'stop') {
            adjustTimerAdjust(-1)
        }
    })
    $('#chrono_plus').click(function () {
        if (allowMainTimerUpdateWhileRunning || timerStatus == 'stop') {
            adjustTimerAdjust(1)
        }
    })
    $('#chrono_moins10').click(function () {
        if (allowMainTimerUpdateWhileRunning || timerStatus == 'stop') {
            adjustTimerAdjust(-10)
        }
    })
    $('#chrono_plus10').click(function () {
        if (allowMainTimerUpdateWhileRunning || timerStatus == 'stop') {
            adjustTimerAdjust(10)
        }
    })
    $('#updateChrono img').click(function () {
        serverUpdate('updateChrono', {idMatch: idMatch})
        adjustTimerConfirm()
        broadcastPost('timer')
    })
    $('#time_plus60').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0]) + 1
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2)
        if (second_2 > 60) { second_2 = 60 }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    $('#time_moins60').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0]) - 1
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2)
        if (second_2 > 60) { second_2 = 60 }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    $('#time_plus10').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0])
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2) + 10
        if (second_2 > 59) { second_2 = second_2 - 60; $('#time_plus60').click() }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    $('#time_moins10').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0])
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2) - 10
        if (second_2 < 0) { second_2 = second_2 + 60; $('#time_moins60').click() }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    $('#time_plus1').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0])
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2) + 1
        if (second_2 > 59) { second_2 = 0; $('#time_plus60').click() }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    $('#time_moins1').click(function () {
        var temp_time2 = $('#time_evt').val()
        temp_time2 = temp_time2.split(':')
        minut_2 = Number(temp_time2[0])
        if (minut_2 > 99) { minut_2 = 99 }
        if (minut_2 < 0) { minut_2 = 0 }
        if (minut_2 < 10) { minut_2 = '0' + minut_2 }
        var second_2 = temp_time2[1]
        if (isNaN(second_2)) { second_2 = 0 }
        second_2 = Number(second_2) - 1
        if (second_2 < 0) { second_2 = 59; $('#time_moins60').click() }
        if (second_2 < 10) { second_2 = '0' + second_2 }
        $('#time_evt').val(minut_2 + ':' + second_2)
    })
    

    $('#start_button').click(function () {
        $('#start_button').hide()
        $('#run_button').hide()
        $('#stop_button').show()
        if (!allowMainTimerUpdateWhileRunning) {
            $('#chrono_moins').hide()
            $('#chrono_plus').hide()
            $('#chrono_moins10').hide()
            $('#chrono_plus10').hide()
        }
        if (!allowShotclockUpdateWhileRunning) {
            $('#shotclock_moins').hide()
            $('#shotclock_plus').hide()
            $('#shotclock_moins10').hide()
            $('#shotclock_plus10').hide()
        }
        
        serverUpdate('setChrono', {idMatch: idMatch, action: 'start'})
        timerStatus = 'start'
        broadcastPost('timer_status', timerStatus)
        mainTimerStart()
    })
    $('#stop_button').click(function () {
        $('#run_button').show()
        $('#start_button').hide()
        $('#stop_button').hide()
        $('#chrono_moins').show()
        $('#chrono_plus').show()
        $('#shotclock_moins').show()
        $('#shotclock_plus').show()
        $('#shotclock_moins10').show()
        $('#shotclock_plus10').show()

        serverUpdate('setChrono', {idMatch: idMatch, action: 'stop'})
        timerStatus = 'stop'
        broadcastPost('timer_status', timerStatus)
        mainTimerPause()
    })
    $('#run_button').click(function () {
        if (mainTimer.getTotalTimeValues().secondTenths === 0) {
            return
        }
        $('#run_button').hide()
        $('#stop_button').show()
        if (!allowMainTimerUpdateWhileRunning) {
            $('#chrono_moins').hide()
            $('#chrono_plus').hide()
            $('#chrono_moins10').hide()
            $('#chrono_plus10').hide()
        }
        if (!allowShotclockUpdateWhileRunning) {
            $('#shotclock_moins').hide()
            $('#shotclock_plus').hide()
            $('#shotclock_moins10').hide()
            $('#shotclock_plus10').hide()
        }
        
        serverUpdate('setChrono', {idMatch: idMatch, action: 'run'})
        
        timerStatus = 'start'
        broadcastPost('timer_status', timerStatus)
        mainTimerStart()
    })
    $('#raz_button').click(function () {
        $('#start_button').show()
        $('#run_button').hide()
        $('#stop_button').hide()
        $('#chrono_moins').show()
        $('#chrono_plus').show()
        $('#shotclock_moins').show()
        $('#shotclock_plus').show()
        $('#shotclock_moins10').show()
        $('#shotclock_plus10').show()

        serverUpdate('setChrono', {idMatch: idMatch, action: 'RAZ'})
        timerStatus = 'stop'
        broadcastPost('timer_status', timerStatus)
        mainTimerReset()
    })
    $('#reset_shotclock').click(function () {
        shotclockReset()
    })
    $('#shotclock_moins').click(function () {
        if (allowShotclockUpdateWhileRunning || timerStatus == 'stop') {
            const seconds = shotclockTimer.getTotalTimeValues().seconds
            if (seconds > 0) {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: seconds - 1}})
            }
            shotclockDisplay()
            serverUpdate('updateChrono', {idMatch: idMatch})
        }
    })
    $('#shotclock_plus').click(function () {
        if (allowShotclockUpdateWhileRunning || timerStatus == 'stop') {
            const seconds = shotclockTimer.getTotalTimeValues().seconds
            if (seconds < shotclockDefault) {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: shotclockTimer.getTotalTimeValues().seconds + 1}})
            }
            shotclockDisplay()
            serverUpdate('updateChrono', {idMatch: idMatch})
        }
    })
    $('#shotclock_moins10').click(function () {
        if (allowShotclockUpdateWhileRunning || timerStatus == 'stop') {
            const seconds = shotclockTimer.getTotalTimeValues().seconds
            if (seconds > 10) {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: seconds - 10}})
            } else {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: 0}})
            }
            shotclockDisplay()
            serverUpdate('updateChrono', {idMatch: idMatch})
        }
    })
    $('#shotclock_plus10').click(function () {
        if (allowShotclockUpdateWhileRunning || timerStatus == 'stop') {
            const seconds = shotclockTimer.getTotalTimeValues().seconds
            if (seconds < shotclockDefault - 10) {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: shotclockTimer.getTotalTimeValues().seconds + 10}})
            } else {
                shotclockTimer.setParams({countdown: true, precision: 'secondTenths', startValues: {seconds: shotclockDefault}})
            }
            shotclockDisplay()
            serverUpdate('updateChrono', {idMatch: idMatch})
        }
    })

    $('#open_scoreboard_button').click(function (event) {
		event.preventDefault()
		window.open('scoreboard.php?v=' + version, 'scoreboard')
        setTimeout(() => {$('#update_scoreboard_button').click()}, 1000)
	})
    $('#open_shotclock_button').click(function (event) {
		event.preventDefault()
		window.open('shotclock.php?v=' + version, 'shotclock')
	})
    $('#test_sound_button').click(function (event) {
		event.preventDefault()
		buzzer()
	})

    $('#update_scoreboard_button').click(function () {
        broadcastPost('teams')
        broadcastPost('period')
        broadcastPost('scores')
        broadcastPost('timer_status', timerStatus)
        broadcastPost('timer')
        broadcastPost('shotclock')
        broadcastPens()
    })

    $('.chronoButton').click(function () {
        // scroll en haut
        if ($(window).scrollTop() !== 0) {
            $('html, body').scrollTop(0);
        }
    })

    /* Clavier */
    addEventListener("keydown", (event) => {
        if (
            document.getElementById('tabs-1_link').style.display == 'none' ||
            document.querySelector('#chrono_ajust:focus') != null ||
            document.querySelector('#periode_ajust:focus') != null ||
            document.querySelector('#time_evt:focus') != null ||
            document.querySelector('#time_end_match:focus') != null ||
            document.querySelector('#commentaires:focus') != null
        ) {
            return
        }
        switch (event.key) {
            case '0':
                event.preventDefault()
                $('#reset_shotclock').click()
                break
            case '+':
                event.preventDefault()
                $('#shotclock_plus').click()
                break
            case '-':
                event.preventDefault()
                $('#shotclock_moins').click()
                break
            case ' ':
                event.preventDefault()
                if (timerStatus == 'stop' && document.getElementById('start_button').style.display == 'none') {
                    $('#run_button').click()
                } else if (timerStatus == 'stop') {
                    $('#start_button').click()
                } else {
                    $('#stop_button').click()
                }
                break
        }
    })

    $('#time_evt').keypress(function (e) {
        if (e.which == 13) {
            $(this).focus().blur()
            if ($('#update_evt').attr('data-id') == '') {
                $('#valid_evt').click()
            } else {
                $('#update_evt').click()
            }
        }
    })

})