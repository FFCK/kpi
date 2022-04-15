/* 
 * Feuille de marque en ligne
 * Javascript partie B : Profil suffisant
 */

$(function () {
    /* CONTROLE */
    $('#controleVerrou').click(function (event) {
        event.preventDefault()
        if ($('#scoreA3').text() != $('#scoreA4').text() || $('#scoreB3').text() != $('#scoreB4').text()) {
            $('div.simple_alert').remove()
            $("<div></div>").html(lang.score_non_valide).dialog({
                dialogClass: 'simple_alert',
                title: lang.Attention,
                resizable: false,
                modal: true,
                buttons: {
                    "Ok": function () {
                        $(this).dialog("close")
                        $('#controleOuvert').click()
                    }
                },
                close: function (event, ui) {
                    $('#controleOuvert').click()
                }
            })
        } else {
            $('div.simple_alert').remove()
            $("<div></div>").html(lang.controle_feuille).dialog({
                dialogClass: 'simple_alert',
                title: lang.Confirmation + ' ?',
                resizable: false,
                modal: true,
                buttons: {
                    "Oui/Yes": function () {
                        $(this).dialog("close")
                        $.post(
                            'v2/StatutPeriode.php', // Le fichier cible côté serveur.
                            { // variables
                                Id_Match: idMatch,
                                Valeur: 'O',
                                TypeUpdate: 'Validation'
                            },
                            function (data) { // callback
                                if (data == 'OK') {
                                    $('.statut, .periode, #zoneTemps, #zoneChrono, .match').hide()
                                    $('#reset_evt').click()
                                    window.location = 'FeuilleMarque2.php?idMatch=' + idMatch
                                } else {
                                    custom_alert(lang.Action_impossible, lang.Attention)
                                }
                            },
                            'text' // Format des données reçues.
                        )
                    },
                    "Non/No": function () {
                        $('#controleOuvert').click()
                        $(this).dialog("close")
                    }
                }
            })
        }
    })
    $('#controleOuvert').click(function (event) {
        event.preventDefault()
        //if(confirm('Déverrouiller la feuille de match ?')){
        $.post(
            'v2/StatutPeriode.php', // Le fichier cible côté serveur.
            { // variables
                Id_Match: idMatch,
                Valeur: '',
                TypeUpdate: 'Validation'
            },
            function (data) { // callback
                if (data == 'OK') {
                    $('.statut, .periode, #zoneTemps, #zoneChrono, .match').show()
                    //$('.statut[class*="actif"]').click();
                    $('#reset_evt').click()
                    window.location = 'FeuilleMarque2.php?idMatch=' + idMatch
                }
                else {
                    custom_alert(lang.Action_impossible, lang.Attention)
                }
            },
            'text' // Format des données reçues.
        )
        //}
    })

    /* PUBLICATION */
    $('#prive').click(function (event) {
        event.preventDefault()
        if (confirm(lang['Depublier_le_match'])) {
            $.post(
                'v2/StatutPeriode.php', // Le fichier cible côté serveur.
                { // variables
                    Id_Match: idMatch,
                    Valeur: '',
                    TypeUpdate: 'Publication'
                },
                function (data) { // callback
                    if (data == 'OK') {
                        window.location = 'FeuilleMarque2.php?idMatch=' + idMatch
                    }
                    else {
                        custom_alert(lang.Action_impossible, lang.Attention)
                    }
                },
                'text' // Format des données reçues.
            )
        } else {
            $('#controleOuvert').click()
        }
    })
    $('#public').click(function (event) {
        event.preventDefault()
        if (confirm(lang.Depublier_le_match)) {
            $.post(
                'v2/StatutPeriode.php', // Le fichier cible côté serveur.
                { // variables
                    Id_Match: idMatch,
                    Valeur: 'O',
                    TypeUpdate: 'Publication'
                },
                function (data) { // callback
                    if (data == 'OK') {
                        window.location = 'FeuilleMarque2.php?idMatch=' + idMatch
                    }
                    else {
                        custom_alert(lang.Action_impossible, lang.Attention)
                    }
                },
                'text' // Format des données reçues.
            )
        }
    })
})