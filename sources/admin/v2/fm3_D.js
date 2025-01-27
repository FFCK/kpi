/* 
 * Feuille de marque en ligne
 * Javascript partie D
 */


$(function () {
	/* DIALOG END MATCH */
	$("#dialog_end_match").dialog({
		autoOpen: false,
		modal: true,
		width: 500,
		buttons: {
			Ok: function () {
				$(this).dialog("close")
				$.post(
					'v2/saveComments.php', // Le fichier cible côté serveur.
					{ // variables
						idMatch: idMatch,
						value: $('#commentaires').val(),
						heure_fin_match: $('#time_end_match').val()
					},
					function (data) { // callback
						if (data == $('#commentaires').val()) {
							$('#end_match_time').removeClass('inactif').addClass('actif')
							$('#end_match_time').val($('#time_end_match').val())
							$('#raz_button').click()
							$('#run_button').hide()
							$('.statut').removeClass('actif')
							$('#END').addClass('actif')
							$('#zoneTemps, .periode, #zoneChrono, #zoneScoreboard').hide()
							$('.endmatch').show()
							$('#comments').text($('#commentaires').val())
							$('#tabs-1_link').click()
							$('#tabs2-A_link').click()
							$('#validScore').click()
						} else {
							custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention)
						}
					},
					'text' // Format des données reçues.
				)
					.fail(function (xhr, status, error) {
						custom_alert(lang.Action_impossible + '<br>' + error, lang.Attention)
					})
			},
			'Annuler/Dismiss': function () {
				$(this).dialog("close")
			}
		}
	})
	/* DIALOG END */
	$("#dialog_end").dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function () {
				$(this).dialog("close")
			}
		}
	})

	/* DIALOG AJUST */
	$("#dialog_ajust").dialog({
		autoOpen: false,
		modal: true,
		buttons: {
			Ok: function () {
				$(this).dialog("close")
				const split_period = $('#periode_ajust').val().split(':')
				const split_chrono = $('#chrono_ajust').val().split(':')
				mainTimerDefault = parseInt(split_period[0])
				mainTimer.setParams({
					countdown: true,
					precision: 'seconds',
					startValues: {
						minutes: parseInt(split_chrono[0]),
						seconds: parseInt(split_chrono[1])
					}
				})

				$('#stop_button').click()
				broadcastPost('timer')
				broadcastPost('period')
				$('#time_evt').val('')
				if ($('#chrono_ajust').val() == '00:00') {
					$('#run_button').hide()
				}
			},
			"Annuler/Dismiss": function () {
				$(this).dialog("close")
			}
		}
	})

	/* DIALOG MOTIF */
	$("#dialog_motif").dialog({
		autoOpen: false,
		modal: true,
		width: 800,
		buttons: {
			"Annuler/Dismiss": function () {
				$(this).dialog("close")
				$('#motif').val('')
				$('#motif_texte').val('')
				$('#time_evt').focus()
			}
		}
	})

	/* TABS */
	$('#tabs-1_link').hide()
	$('#tabs-2').hide()
	$('.fm_tabs').click(function () {
		$('.fm_tabs').toggle()
		$('.tabs_content').toggle()
	})
	/* TABS SETTINGS */
	$('#tabs2-A_link').addClass('actif')
	$('#tabs2-B').hide()
	$('#tabs2-C').hide()
	$('#tabs2-D').hide()
	$('.fm_tabs2').click(function () {
		$('.fm_tabs2').removeClass('actif')
		$(this).addClass('actif')
		var target = $(this).data('target')
		$('.tabs2_content').hide()
		$('#' + target).show()
	})
	/* END MATCH */
	/* Charge nouvelle feuille */
	$('#chargeFeuille').click(function (event) {
		event.preventDefault()
		queueAlert()

		/* Numéro court */
		const numTarget = $('#idFeuille').val()
		console.log(numTarget.length)
		if (numTarget.length <= 5) {
			$.post(
				'v2/getShortGame.php',
				{
					idMatch: idMatch,
					numTarget: numTarget
				},
				function (data) {
					if (Number.isInteger(data?.Id)) {
						$('#idFeuille').val(data.Id)
						window.location = '?idMatch=' + data.Id
					} else {
						custom_alert(lang.Action_impossible, lang.Attention)
					}
				},
				'json'
			)
			.fail(function (xhr, status, error) {
				custom_alert(lang.Action_impossible + '<br>' + error, lang.Attention)
			})

		} else {
			window.location = '?idMatch=' + $('#idFeuille').val()
		}
		
	})
	$('#idFeuille').keypress(function (e) {
		if (e.which == 13 && $(this).val() != '') {
			$('#chargeFeuille').click()
		}
	})
	/* ORDRE EVTS */
	$('#change_ordre').click(function () {
		if (ordre_actuel == 'up') {
			ordre_actuel = 'down'
			$('#change_ordre img').attr('src', '../img/down.png')
			$('#list tr').each(function () {
				$(this).prependTo('#list')
			})
		} else {
			ordre_actuel = 'up'
			$('#change_ordre img').attr('src', '../img/up.png')
			$('#list tr').each(function () {
				$(this).prependTo('#list')
			})
		}
	})

	// VERSION PDF
	$('#pdfFeuille').buttonset()
	$('#pdfFeuille').click(function (event) {
		event.preventDefault()
		window.open('FeuilleMatchMulti.php?listMatch=' + idMatch, '_blank')
	})

	// Stats
	$('#btn_stats').click(function (event) {
		event.preventDefault()
		window.open('FeuilleMarque2stats.php?idMatch=' + idMatch)
	})

	// Match suivant
	$('#nextGame').click(function () {
		$.post(
			'v2/getNextGame.php',
			{
				idMatch: idMatch
			},
			function (data) {
				if (Number.isInteger(data?.idMatch) && data.equipeA != null && data.equipeB != null) {
					$('#nextGameDetail').html(
						'Next: Game #' + data.Numero_ordre + ' - Pitch ' + data.Terrain
						+ '<br>' + data.Date_match + ' ' + data.Heure_match
						+ '<br>' + data.equipeA + ' | ' + data.equipeB
					)
					$('#idFeuille').val(data.idMatch)
					$('#nextGame').hide()
				} else {
					custom_alert(lang.Action_impossible, lang.Attention)
				}
			},
			'json'
		)
			.fail(function (xhr, status, error) {
				custom_alert(lang.Action_impossible + '<br>' + error, lang.Attention)
			})
	})
})

