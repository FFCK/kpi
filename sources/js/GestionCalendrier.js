jq = jQuery.noConflict()

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

jq(document).ready(function () {
	jq("#evenement").change(function () {
		if (jq("#modeEvenement").val() != "2" && jq("#evenement").val() != "-1") {
			jq("#competition").val('*')
		}
		jq("#formCalendrier").submit()
	})

	jq(".typeJournee").click(function () {
		laJournee = jq(this)
		laJournee.attr('src', 'v2/images/indicator.gif')
		if (laJournee.attr('data-valeur') == 'C') {
			changeType = 'E'
			textType = 'Elimination'
		} else {
			changeType = 'C'
			textType = 'Classement'
		}
		jq.post(
			'v2/StatutJournee.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Journee: laJournee.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Type'
			},
			function (data) { // callback
				if (data == 'OK') {
					laJournee.attr('src', '../img/type' + changeType + '.png')
					laJournee.attr('data-valeur', changeType)
					laJournee.attr('title', textType)
				} else {
					alert(langue['MAJ_impossible'])
					laJournee.attr('src', '../img/type' + laJournee.attr('data-valeur') + '.png')
					laJournee.attr('data-valeur', laJournee.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
	})

	jq(".publiJournee").click(function () {
		laJournee = jq(this)
		laJournee.attr('src', 'v2/images/indicator.gif')
		if (laJournee.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = 'Non public'
		} else {
			changeType = 'O'
			textType = 'Public'
		}
		jq.post(
			'v2/StatutJournee.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Journee: laJournee.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Publication'
			},
			function (data) { // callback
				if (data == 'OK') {
					laJournee.attr('src', '../img/oeil2' + changeType + '.gif')
					laJournee.attr('data-valeur', changeType)
					laJournee.attr('title', textType)
				}
				else {
					custom_alert(langue['MAJ_impossible'], 'Attention')
					laJournee.attr('src', '../img/oeil2' + laJournee.attr('data-valeur') + '.gif')
					laJournee.attr('data-valeur', laJournee.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
	})

	jq(".checkassoc2").click(function (event) {
		event.preventDefault()
		var laJournee = jq(this)
		var idJournee = laJournee.attr('data-id')
		var idEvenement = jq('#evenement').val()
		var statut = laJournee.attr('checked')
		laJournee.after('<img src="v2/images/indicator.gif"  height="23">').hide()

		jq.post(
			'v2/setEvenementJournee.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Evenement: idEvenement,
				Id_Journee: idJournee,
				Valeur: statut,
			},
			function (data) { // callback
				if (data == 'OK') {
					laJournee.show().attr('checked', statut).next().remove()
				}
				else {
					laJournee.show().next().remove()
					alert(langue['MAJ_impossible'] + ' <br />' + data)
				}
			},
			'text' // Format des données reçues.
		)
	})

	document.querySelector('table.tableau').addEventListener('input', function(event) {
		if (event.target.matches('input[type="tel"]')) {
			// Supprime tous les caractères non numériques
			event.target.value = event.target.value.replace(/\D/g, '');
		}
		if (event.target.matches('input[type="text"].dpt')) {
			// Supprime tous les caractères non numériques et limite à 3 caractères
			event.target.value = event.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
		}
	});


	jq('.directInput').attr('title', langue['Cliquez_pour_modifier'])

	jq("body").delegate("span.directInput", "click", function (event) {
		event.preventDefault()
		var valeur = jq(this).text()
		var typeChamps = jq(this).attr('data-type')
		var spanRef = jq(this) // Stocker la référence au span AVANT de le cacher
		switch (typeChamps) {
			case 'text':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="7" data-anciennevaleur="' + valeur + '" value="' + valeur + '">')
				break
			case 'longtext':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="20" data-anciennevaleur="' + valeur + '" value="' + valeur + '">')
				break
			case 'smalltext':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="3" data-anciennevaleur="' + valeur + '" value="' + valeur + '">')
				break
			case 'dpt':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan dpt" size="3" maxlength="3" data-anciennevaleur="' + valeur + '" value="' + valeur + '">')
				break
			case 'tel':
				jq(this).before('<input type="tel" id="inputZone" class="directInputSpan" size="1" maxlength="2" data-anciennevaleur="' + valeur + '" value="' + valeur + '">')
				break
			case 'date':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan flatpickr-input" size="8" value="' + valeur + '" data-anciennevaleur="' + valeur + '">')
				// Stocker la référence au span directement sur l'élément DOM (pas via jQuery .data())
				document.getElementById('inputZone')._spanRef = spanRef[0]
				// Initialiser Flatpickr avec format français
				flatpickr('#inputZone', {
					dateFormat: 'd/m/Y',
					locale: 'fr',
					allowInput: true,
					clickOpens: true,
					defaultDate: valeur || null,
					onClose: function(selectedDates, dateStr, instance) {
						// Déclencher le blur après fermeture du calendrier
						setTimeout(function() {
							jq('#inputZone').blur()
						}, 100)
					}
				})
				break
			case 'dateEN':
				jq(this).before('<input type="text" id="inputZone" class="directInputSpan flatpickr-input" size="8" value="' + valeur + '" data-anciennevaleur="' + valeur + '">')
				// Stocker la référence au span directement sur l'élément DOM (pas via jQuery .data())
				document.getElementById('inputZone')._spanRef = spanRef[0]
				// Initialiser Flatpickr avec format ISO
				flatpickr('#inputZone', {
					dateFormat: 'Y-m-d',
					locale: 'fr',
					allowInput: true,
					clickOpens: true,
					defaultDate: valeur || null,
					onClose: function() {
						// Déclencher le blur après fermeture du calendrier
						setTimeout(function() {
							jq('#inputZone').blur()
						}, 100)
					}
				})
				break
		}
		jq(this).hide()
		setTimeout(function () {
			jq('#inputZone').select()
		}, 0)
	})

	jq('#inputZone').live('keydown', function (e) {
		if (e.which == 13) {
			jq(this).blur()
			return false
		}
	})

	jq('#inputZone').live('blur', function (e) {
		// Si c'est un input avec Flatpickr, ne rien faire ici
		// Le blur sera déclenché par onClose de Flatpickr
		if (jq(this).hasClass('flatpickr-input')) {
			// Vérifier si le calendrier est ouvert
			if (jq('.flatpickr-calendar.open').length > 0) {
				// Le calendrier est ouvert, ne rien faire
				return
			}
		}
		// Pour tous les autres cas (ou Flatpickr fermé), traiter normalement
		processBlur(this)
	})

	function processBlur(element) {
		// Stratégie prioritaire: utiliser la référence stockée sur l'élément DOM (pour les champs date avec Flatpickr)
		var thisSpan = null
		if (element._spanRef) {
			thisSpan = jq(element._spanRef)
		}

		// Fallback: chercher le span dans le DOM si pas de référence stockée
		if (!thisSpan || !thisSpan.length) {
			// Stratégie 1: chercher parmi les siblings suivants
			thisSpan = jq(element).nextAll('span.directInput').first()
		}

		if (!thisSpan || !thisSpan.length) {
			// Stratégie 2: Fallback sur .next('span') simple
			thisSpan = jq(element).next('span')
		}

		var nouvelleValeur = jq(element).val()
		var typeChamps = jq(element).attr('type')
		var valeurEntier = nouvelleValeur | 0

		// Vérifier que thisSpan existe
		if (!thisSpan || !thisSpan.length) {
			console.error('processBlur: thisSpan not found', element)
			return
		}
		if (typeChamps == 'tel' && valeurEntier < 1) {
			jq(element).focus().css('border', '1px solid red')
		} else {
			if (nouvelleValeur != jq(element).attr('data-anciennevaleur')) {
				var AjaxWhere = jq('#AjaxWhere').val()
				var AjaxTableName = jq('#AjaxTableName').val()
				var AjaxAnd = ''
				var AjaxUser = jq('#AjaxUser').val()
				var numJournee = thisSpan.attr('data-id')
				var typeValeur = thisSpan.attr('data-target')
				jq.get("UpdateCellJQ.php",
					{
						AjTableName: AjaxTableName,
						AjWhere: AjaxWhere,
						AjTypeValeur: typeValeur,
						AjValeur: nouvelleValeur,
						AjAnd: AjaxAnd,
						AjId: numJournee,
						AjId2: '',
						AjUser: AjaxUser,
						AjOk: 'OK'
					},
					function (data) {
						if (data != 'OK!') {
							alert(langue['MAJ_impossible'] + ' : ' + data)
						} else {
							thisSpan.text(nouvelleValeur)
						}
					}
				)
			}
			thisSpan.show()
			jq(element).remove()
		}
	}
})

function changeCompetition () {
	document.forms['formCalendrier'].submit()
}

function changeCompetitionOrder () {
	document.forms['formCalendrier'].submit()
}

function changeModeEvenement () {
	document.forms['formCalendrier'].submit()
}

function ParamJournee (idJournee) {
	if (idJournee == 0) {
		var compet = document.forms['formCalendrier'].elements['competition'].value
		if (compet == '*') {
			alert(langue['Selection_competition'])
			return
		}
	}

	document.forms['formCalendrier'].elements['Cmd'].value = 'ParamJournee'
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee
	document.forms['formCalendrier'].submit()
}

function ClickEvenementJournee (idJournee) {
	var obj = document.getElementById('checkEvenementJournee' + idJournee)
	if (obj == null)
		return

	if (obj.checked)
		document.forms['formCalendrier'].elements['Cmd'].value = 'AddEvenementJournee'
	else
		document.forms['formCalendrier'].elements['Cmd'].value = 'RemoveEvenementJournee'

	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee
	document.forms['formCalendrier'].submit()
}

function publiMultiJournees () {
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formCalendrier'].elements['Cmd'].value = 'PubliMultiJournees'
	document.forms['formCalendrier'].submit()
}

function duplicate (idJournee) {
	if (!confirm(langue['Confirm_dupplicate']))
		return false

	document.forms['formCalendrier'].elements['Cmd'].value = 'Duplicate'
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee
	document.forms['formCalendrier'].submit()
}



