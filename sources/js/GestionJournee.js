var jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Annuler'] = 'Cancel'
	langue['Arbitre_non_identifie'] = 'Unidentified referee'
	langue['Cliquez_pour_modifier'] = 'Click to edit'
	langue['Compet'] = 'Competition'
	langue['Confirm_affect'] = 'You should have recalculate group ranking first, confirm auto assignment ?'
	langue['Confirm_delete'] = 'Delete teams & referees from selected games ?'
	langue['Confirm_update'] = 'Confirm update ?'
	langue['Confirmer_MAJ'] = 'Confirm composition update ?'
	langue['Date_vide'] = 'Date is empty, unable to create !'
	langue['Equipe'] = 'Team'
	langue['Equipe_non_definie'] = 'Unidentified team'
	langue['Heure_invalide'] = 'Time is invalid (format hh:mm), continue anymore ?'
	langue['InitTitu'] = 'Delete all players and re-assign team rosters\n(excluding X-Unavailables and et A-Referees)\nfor unlocked games of :\n'
	langue['Journee'] = 'matchday / phase / group'
	langue['MAJ_impossible'] = 'Unable to update'
	langue['Match_de_classement'] = 'Classifying game'
	langue['Match_eliminatoire'] = 'Playoffs'
	langue['Non_valide'] = 'Unvalidated (private score)'
	langue['Select_journee'] = 'Select a matchday / phase / group.'
	langue['Selection_journee'] = 'Select a matchday / phase / group first, unable to create !'
	langue['Selection_competition'] = 'Select a competition !'
	langue['Selection_equipe'] = 'Select a team !'
	langue['Valider'] = 'Valid'
	langue['Valide'] = 'Validated, locked (public score)'
	langue['Vider'] = 'Empty'
} else {
	langue['Annuler'] = 'Annuler'
	langue['Arbitre_non_identifie'] = 'Arbitre non identifié'
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
	langue['Compet'] = 'Compétition'
	langue['Confirm_affect'] = 'Vous devez avoir recalculé le classement, Confirmer l\affectation automatique ?'
	langue['Confirm_delete'] = 'Supprimer les équipes et arbitres des matchs sélectionnés ?'
	langue['Confirm_update'] = 'Confirmer le changement ?'
	langue['Confirmer_MAJ'] = 'Confirmez-vous la mise à jour des feuilles de matchs ?'
	langue['Date_vide'] = 'Date vide, ajout impossible !'
	langue['Equipe'] = 'Equipe'
	langue['Equipe_non_definie'] = 'Equipe non définie'
	langue['Heure_invalide'] = 'Heure invalide (format hh:mm), continuer ?'
	langue['InitTitu'] = 'Supprimer tous les joueurs et ré-affecter\nles joueurs présents (sauf X-Inactifs et A-Arbitres)\npour les matchs non verrouillés de :\n'
	langue['Journee'] = 'journée / phase / poule'
	langue['MAJ_impossible'] = 'Mise à jour impossible'
	langue['Match_de_classement'] = 'Match de classement'
	langue['Match_eliminatoire'] = 'Match éliminatoire'
	langue['Non_valide'] = 'Non validé (score non public)'
	langue['Select_journee'] = 'Sélectionner une journée / phase / poule.'
	langue['Selection_journee'] = 'Sélectionner une journée / phase / poule, ajout impossible !'
	langue['Selection_competition'] = 'Sélectionner une compétition !'
	langue['Selection_equipe'] = 'Sélectionner une équipe !'
	langue['Valider'] = 'Valider'
	langue['Valide'] = 'Validé / verrouillé (score public)'
	langue['Vider'] = 'Vider'
}


function changeEquipeA () {
}

function changeEquipeB () {
}

function validMatch () {
	var dateMatch = document.forms['formJournee'].elements['Date_match'].value
	if (dateMatch.length == 0) {
		alert(langue['Date_vide'])
		return false
	}

	var heureMatch = document.forms['formJournee'].elements['Heure_match'].value
	if ((heureMatch.length != 5) || (heureMatch.charAt(2) != ':')) {
		if (!confirm(langue['Heure_invalide']))
			return false
	}

	var journMatch = document.forms['formJournee'].elements['comboJournee'].value
	if (journMatch == '*') {
		alert(langue['Selection_journee'])
		return false
	}

	return true
}

function Add () {
	if (!validMatch())
		return

	changeCombo('formJournee', 'equipeA', 'idEquipeA', false)
	changeCombo('formJournee', 'equipeB', 'idEquipeB', false)

	document.forms['formJournee'].elements['Cmd'].value = 'Add'
	document.forms['formJournee'].elements['ParamCmd'].value = ''

	document.forms['formJournee'].submit()
}

function Update () {
	if (!validMatch())
		return

	changeCombo('formJournee', 'equipeA', 'idEquipeA', false)
	changeCombo('formJournee', 'equipeB', 'idEquipeB', false)

	document.forms['formJournee'].elements['Cmd'].value = 'Update'
	document.forms['formJournee'].elements['ParamCmd'].value = ''

	document.forms['formJournee'].submit()
}

function Raz () {
	document.forms['formJournee'].elements['Cmd'].value = 'Raz'
	document.forms['formJournee'].elements['ParamCmd'].value = ''
	document.forms['formJournee'].submit()
}


function ParamMatch (idMatch) {
	document.forms['formJournee'].elements['Cmd'].value = 'ParamMatch'
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch
	document.forms['formJournee'].submit()
}


function ChangeOrderMatchs (Journee) {
	document.forms['formJournee'].action = 'GestionJournee.php?idJournee=' + Journee
	document.forms['formJournee'].submit()
}

function changeCompet () {
	document.forms['formJournee'].elements['Cmd'].value = ''
	document.forms['formJournee'].elements['ParamCmd'].value = 'changeCompet'
	document.forms['formJournee'].submit()
}

function publiMatch (idMatch, pub) {
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'PubliMatch'
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch
	document.forms['formJournee'].elements['Pub'].value = pub
	document.forms['formJournee'].submit()
}

function verrouMatch (idMatch, verrou) {
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouMatch'
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch
	document.forms['formJournee'].elements['Verrou'].value = verrou
	document.forms['formJournee'].submit()
}

function publiMultiMatchs () {
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'PubliMultiMatchs'
	document.forms['formJournee'].submit()
}

function verrouMultiMatchs () {
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouMultiMatchs'
	document.forms['formJournee'].submit()
}

function verrouPubliMultiMatchs () {
	var matchs = document.forms['formJournee'].elements['ParamCmd'].value
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouPubliMultiMatchs'
	document.forms['formJournee'].submit()
}

function affectMultiMatchs () {
	if (!confirm(langue['Confirm_affect'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'AffectMultiMatchs'
	document.forms['formJournee'].submit()
}

function annulMultiMatchs () {
	if (!confirm(langue['Confirm_delete'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'AnnulMultiMatchs'
	document.forms['formJournee'].submit()
}

function changeMultiMatchs () {
	var journ = jq('#comboJournee').val()
	if (journ == '*') {
		alert(langue['Select_journee'])
		jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus()
		return false
	}
	if (!confirm(langue['Confirm_update'])) {
		return false
	}
	document.forms['formJournee'].elements['Cmd'].value = 'ChangeMultiMatchs'
	document.forms['formJournee'].submit()
}


function numMultiMatchs () {
	jq('#numMultiMatchsBtn')
		.hide()
		.after('<span id="numMultiMatchsStart" class="right">\n\
                        <br><label>Renuméroter à partir de :</label><input type="tel" size=2 value="1">\n\
                        <input type="button" value="Confirmer">\n\
                        <input type="button" value="Annuler">\n\
                    </span>')
}

function imprimeMultiMatchs () {
	if (jq('#tableMatchs tbody input:checkbox:checked').length == 0) {
		alert('Aucun match sélectionné')
		return
	}
	jq('#tableMatchs tbody input:checkbox:checked').each(function () {
		jq(this).parent().parent().find('.imprimMatch').click()
	})
}
	


// Annuler
jq("body").delegate('#numMultiMatchsStart input:button:last', 'click', function (e) {
	e.preventDefault()
	jq('#numMultiMatchsStart').remove()
	jq('#numMultiMatchsBtn').show()
})
// Confirmer
jq("body").delegate('#numMultiMatchsStart input:button:first', 'click', function (e) {
	//        e.preventDefault();
	if (jq('#tableMatchs tbody input:checkbox:checked').length == 0) {
		alert('Aucun match sélectionné')
		return
	}
	var numMultiMatchs = parseInt(jq('#numMultiMatchsStart input[type="tel"]').val())
	var nbMatchsModifies = 0
	//    console.log('longueur : ' + jq('#tableMatchs tbody input:checkbox:checked').length);
	jq('#tableMatchs tbody input:checkbox:checked').each(function () {
		var checkboxIdMatch = jq(this).val()
		var numMatch = jq(this).parent().parent().find('.numMatch')
		if (numMatch.text() != numMultiMatchs) {
			var nouveauNumMatch = numMultiMatchs
			console.log(checkboxIdMatch + ' : ' + numMatch.text() + ' -> ' + nouveauNumMatch)
			//                alert(valeurPrecedente + ' => ' + numMultiMatchs);
			var AjaxWhere = jq('#AjaxWhere').val()
			var AjaxTableName = jq('#AjaxTableName').val()
			var AjaxAnd = ''
			var AjaxUser = jq('#AjaxUser').val()
			jq.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: 'Numero_ordre',
					AjValeur: nouveauNumMatch,
					AjAnd: AjaxAnd,
					AjId: checkboxIdMatch,
					AjId2: '',
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function (data) {
					if (data != 'OK!') {
						alert(langue['MAJ_impossible'] + ' : ' + data)
					} else {
						numMatch.text(nouveauNumMatch)
					}
				}
			)
		}
		numMultiMatchs = numMultiMatchs + 1
		nbMatchsModifies = nbMatchsModifies + 1
	})
	alert(nbMatchsModifies + ' matchs modifiés.')
	jq('#numMultiMatchsStart').remove()
	jq('#numMultiMatchsBtn').show()
})


// ****************************************************************************************************

jq(document).ready(function () { //Jquery + NoConflict='J'

	if (filtreMatchsNonVerrouilles == 'on') {
		jq(".verrouMatch[data-valeur!='N']").each(function() {
			jq(this).parent().parent().hide()
		})
		jq('#filterAttspan').addClass('highlight3')
	}

	jq("*").tooltip({
		showURL: false
	})

	//sessionJournee
	//ajax
	var journ = jq('#comboJournee').val()
	jq.get("Autocompl_session_journee.php", {
		j: journ
		//},  function(data) {
		//	alert(data);
	})
	jq('#comboJournee').change(function () {
		var journ = jq('#comboJournee').val()
		jq.get("Autocompl_session_journee.php", {
			j: journ
			//},  function(data) {
			//	alert(data);
		})
		//		alert(journ+' !');
	})


	// AUTOCOMPLETE ARBITRES
	jq("#arbitre1").focus(function () {
		var journ = jq('#comboJournee').val()
		if (journ == '*') {
			//alert('Selectionnez une journee / une phase !');
			jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus()
		}
	})
	jq("#arbitre1").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 0,
		scrollHeight: 320,
	})
	jq("#arbitre1").result(function (event, data, formatted) {
		if (data) {
			if (data[1] == 'XXX') {
				jq("#arbitre1_matric").val('')
				jq("#arbitre1").val('')
			}
			else {
				if (data[4] != '')
					var nomArb = data[2] + ' ' + data[3] + ' (' + data[4] + ') ' + data[5]
				else
					var nomArb = data[2] + ' ' + data[3] + ' ' + data[5]
				jq("#arbitre1_matric").val(data[1])
				jq("#arbitre1").val(nomArb)
			}
		}
	})

	jq("#arbitre2").focus(function () {
		var journ = jq('#comboJournee').val()
		if (journ == '*') {
			//alert('Selectionnez une journee / une phase !');
			jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus()
		}
	})
	jq("#arbitre2").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 0,
		scrollHeight: 320,
	})
	jq("#arbitre2").result(function (event, data, formatted) {
		if (data) {
			if (data[1] == 'XXX') {
				jq("#arbitre2_matric").val('')
				jq("#arbitre2").val('')
			}
			else {
				if (data[4] != '')
					var nomArb = data[2] + ' ' + data[3] + ' (' + data[4] + ') ' + data[5]
				else
					var nomArb = data[2] + ' ' + data[3] + ' ' + data[5]
				jq("#arbitre2_matric").val(data[1])
				jq("#arbitre2").val(nomArb)
			}
		}
	})

	// onChange="arbitre1_matric.value=this.options[this.selectedIndex].value; arbitre1.value=this.options[this.selectedIndex].text;"

	jq("#comboarbitre1b").change(function () {
		if (jq('#arbitre1_matric').val() != '' && jq('#arbitre1_matric').val() != '-1') {
			var arbitre = jq('#arbitre1').val()
			var regExp = /\(([^)]+)\)/ // valeur entre parenthèses
			var matches = regExp.exec(arbitre)
			var remplace = arbitre.replace(matches[1], jq("#comboarbitre1b option:selected").text())
			jq('#arbitre1').val(remplace)
		} else {
			jq('#arbitre1').val(jq("#comboarbitre1b option:selected").text())
		}
	})

	jq("#comboarbitre2b").change(function () {
		if (jq('#arbitre2_matric').val() != '' && jq('#arbitre2_matric').val() != '-1') {
			var arbitre = jq('#arbitre2').val()
			var regExp = /\(([^)]+)\)/ // valeur entre parenthèses
			var matches = regExp.exec(arbitre)
			var remplace = arbitre.replace(matches[1], jq("#comboarbitre2b option:selected").text())
			jq('#arbitre2').val(remplace)
		} else {
			jq('#arbitre2').val(jq("#comboarbitre2b option:selected").text())
		}
	})

	// Maskedinput
	jq(".champsHeure").mask("99:99")
	if (lang == 'en') {
		jq('.date').mask("9999-99-99")
	} else {
		jq('.date').mask("99/99/9999")
	}

	//Recherches arbitres
	jq('#iframeRechercheLicenceIndi2').hide()
	jq('#rechercheArbitre1').click(function (e) {
		//jq('#numeroChamps').val('1');
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre1_matric&zoneIdentite=arbitre1')
		jq('#iframeRechercheLicenceIndi2').toggle()
	})
	jq('#rechercheArbitre2').click(function (e) {
		//jq('#numeroChamps').val('2');
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre2_matric&zoneIdentite=arbitre2')
		jq('#iframeRechercheLicenceIndi2').toggle()
	})

	//Init Titulaires
	jq('#InitTitulaireCompet').click(function (e) {
		e.preventDefault()
		var champs = 'Compet'
		var valeur = jq('#comboCompet').val()
		var valeur2 = jq('#comboCompet option:selected').text()
		if (valeur == '*') {
			alert(langue['Selection_competition'])
		} else {
			initTitu(champs, valeur, valeur2)
		}
	})
	jq('#InitTitulaireEquipeA').click(function (e) {
		e.preventDefault()
		var champs = 'Equipe'
		var valeur = jq('#equipeA').val()
		var valeur2 = jq('#equipeA option:selected').text()
		if (valeur == '-1') {
			alert(langue['Selection_equipe'])
		} else {
			initTitu(champs, valeur, valeur2)
		}
	})
	jq('#InitTitulaireJournee').click(function (e) {
		e.preventDefault()
		var champs = 'Journee'
		var valeur = jq('#comboJournee').val()
		var valeur2 = jq('#comboJournee option:selected').text()
		if (valeur == '*') {
			alert(langue['Select_journee'])
		} else {
			initTitu(champs, valeur, valeur2)
		}
	})
	jq('#InitTitulaireEquipeB').click(function (e) {
		e.preventDefault()
		var champs = 'Equipe'
		var valeur = jq('#equipeB').val()
		var valeur2 = jq('#equipeB option:selected').text()
		if (valeur == '-1') {
			alert(langue['Selection_equipe'])
		} else {
			initTitu(champs, valeur, valeur2)
		}
	})
	function initTitu (champs, valeur, valeur2) {
		var langChamps = langue[champs]
		if (confirm(langue['InitTitu'] + '\n' + langChamps + ' : ' + valeur2)) {
			//ajax
			jq.post("InitTitulaireJQ.php", {
				champs: champs,
				valeur: valeur,
				valeur3: -1
			}, function (data) {
				alert(data)
			})
		}
	}

	// Direct Input
	//Ajout title
	jq('.directInput').attr('title', langue['Cliquez_pour_modifier'])
	jq('.pbArb').attr('title', langue['Arbitre_non_identifie'])
	jq('.undefTeam').attr('title', langue['Equipe_non_definie'])
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableMatchs').bind('keydown', function (e) {
		if (e.which == 13) {
			validationDonnee()
			return false
		}
	})

	// focus sur un span du tableau => remplace le span par un input
	jq("body").delegate("#tableMatchs td span.directInput", "focus", function (event) {
		//jq("body").on("focus", "#tableMatchs td > span.directInput", function(event){
		event.preventDefault()
		jq('#inputZone2annul').click()
		var valeur = jq(this).text().trim()
		console.log('valeur: ', valeur)
		var tabindexVal = jq(this).attr('tabindex')
		jq(this).attr('tabindex', tabindexVal + 1000)
		if (jq(this).hasClass('text')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="12" value="' + valeur + '">')
		} else if (jq(this).hasClass('numMatch')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="1" value="' + valeur + '">')
		} else if (jq(this).hasClass('date')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="8" value="' + valeur + '">')
			jq('#inputZone').mask("99/99/9999")
		} else if (jq(this).hasClass('dateEN')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="8" value="' + valeur + '">')
			jq('#inputZone').mask("9999-99-99")
		} else if (jq(this).hasClass('heure')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="4" value="' + valeur + '">')
			jq('#inputZone').mask("99:99")
		} else if (jq(this).hasClass('terrain')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="2" value="' + valeur + '">')
		} else if (jq(this).hasClass('score')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="2" value="' + valeur + '">')
		} else if (jq(this).hasClass('equipe')) {
			jq('#selectZoneAnnul').click()
			jq(this).before('<select id="selectZone" class="directInputSpan" tabindex="' + tabindexVal + '"></select>')
			jq(this).before('<br /><input type="button" id="selectZoneAnnul" value="' + langue['Annuler'] + '">')
			datamatch = jq(this).attr('data-match')
			dataidEquipe = jq(this).attr('data-idequipe')
			dataequipe = jq(this).attr('data-equipe')
			datajournee = jq(this).attr('data-journee')
			jq.post(
				'v2/getEquipesMatch.php', // Le fichier cible côté serveur.
				{
					idMatch: datamatch,	// variables transmises
					idJournee: datajournee,
				},
				function (data) { // callback
					if (data) {
						for (var key in data) {
							if (data[key].Id == dataidEquipe) {
								jq('#selectZone').append('<option value="' + data[key].Id + '" selected="selected">' + data[key].Libelle + '</option>')
							} else {
								jq('#selectZone').append('<option value="' + data[key].Id + '">' + data[key].Libelle + '</option>')
							}
						}
					}
				},
				'json' // Format des données reçues.
			)
			jq('#selectZone').change(function () {
				jq('#selectZoneAnnul').remove()
			})
			jq('#selectZoneAnnul').click(function () {
				jq('#selectZone ~ span').show()
				jq('#selectZone + br').remove()
				jq('#selectZoneAnnul').remove()
				jq('#selectZone').remove()
			})
			jq('#selectZone').blur(function () {
				newIdEquipe = jq(this).val()
				newEquipe = jq('#selectZone option:selected').text()
				if (newIdEquipe != dataidEquipe) {
					jq.post(
						'v2/setEquipesMatch.php', // Le fichier cible côté serveur.
						{
							idMatch: datamatch,	// variables transmises
							idEquipe: newIdEquipe,
							equipe: dataequipe
						},
						function (data) { // callback
							if (data) {
								jq('#selectZone ~ span').attr('data-idequipe', newIdEquipe).text(newEquipe).show()
								if (newIdEquipe == '0') {
									jq('#selectZone ~ span').addClass('undefTeam').attr('title', langue['Equipe_non_definie'])
								} else {
									jq('#selectZone ~ span').removeClass('undefTeam').attr('title', langue['Cliquez_pour_modifier'])
								}
								jq('#selectZone + br').remove()
								jq('#selectZoneAnnul').remove()
								jq('#selectZone').remove()
							}
						},
						'text' // Format des données reçues.
					)
				} else {
					jq('#selectZone ~ span').show()
					jq('#selectZone + br').remove()
					jq('#selectZoneAnnul').remove()
					jq('#selectZone').remove()
				}
			})
		} else if (jq(this).hasClass('phase')) {
			jq('#selectZoneAnnul').click()
			jq(this).before('<select id="selectZone" class="directInputSpan" tabindex="' + tabindexVal + '"></select>')
			jq(this).before('<br /><input type="button" id="selectZoneAnnul" value="' + langue['Annuler'] + '">')
			datamatch = jq(this).attr('data-match')
			dataphase = jq(this).attr('data-phase')
			dataidPhase = jq(this).attr('data-idphase')
			jq('#comboJournee option:not(:first)').each(function () {
				jq('#selectZone').append('<option value="' + jq(this).val() + '" selected="selected">' + jq(this).attr('data-phase') + '</option>')
			})
			jq('#selectZone').val(jq(this).attr('data-idphase'))
			jq('#selectZone').change(function () {
				jq('#selectZoneAnnul').remove()
			})
			jq('#selectZoneAnnul').click(function () {
				jq('#selectZone ~ span').show()
				jq('#selectZone + br').remove()
				jq('#selectZoneAnnul').remove()
				jq('#selectZone').remove()
			})
			jq('#selectZone').blur(function () {
				newIdPhase = jq(this).val()
				newPhase = jq('#selectZone option:selected').text()
				if (newPhase != dataidPhase) {
					jq.post(
						'v2/setPhaseMatch.php',
						{
							idMatch: datamatch,
							idPhase: newIdPhase,
						},
						function (data) {
							if (data) {
								jq('#selectZone ~ span').attr('data-idphase', newIdPhase).text(newPhase).show()
								jq('#selectZone ~ span').attr('title', langue['Cliquez_pour_modifier'])
								jq('#selectZone + br').remove()
								jq('#selectZoneAnnul').remove()
								jq('#selectZone').remove()
							}
						},
						'text'
					)
				} else {
					jq('#selectZone ~ span').show()
					jq('#selectZone + br').remove()
					jq('#selectZoneAnnul').remove()
					jq('#selectZone').remove()
				}
			})
		}
		else if (jq(this).hasClass('arbitre')) {
			jq(this).before('<input type="text" id="inputZone2" class="directInputSpan" tabindex="' + tabindexVal + '" size="22" value="' + valeur + '">')
			jq(this).before('<br>\n\
                            <input type="button" id="inputZone2valid" data-value2="0" value="' + langue['Valider'] + '">\n\
                            <input type="button" id="inputZone2annul" value="' + langue['Annuler'] + '">\n\
                            <input type="button" id="inputZone2vid" data-value2="0" value="' + langue['Vider'] + '">')
			datamatch = jq(this).attr('data-match')
			datajournee = jq(this).attr('data-journee')
			dataid = jq(this).attr('data-id')
			jq("#inputZone2valid").attr('data-match', datamatch)
			jq("#inputZone2valid").attr('data-id', dataid)
			jq("#inputZone2valid").attr('data-value', '')
			jq("#inputZone2valid").attr('data-value2', 0)
			// AUTOCOMPLETE ARBITRES
			jq("#inputZone2").autocomplete('Autocompl_arb.php', {
				width: 320,
				max: 80,
				mustMatch: false,
				minChars: 2,
				cacheLength: 0,
				scrollHeight: 320,
				extraParams: {
					journee: datajournee,
					sessionMatch: datamatch
				}
			})
			jq("#inputZone2").result(function (event, data, formatted) {
				if (data) {
					if (typeof (data[1]) == 'undefined' || data[1] == 'XXX') {
						//						jq("#inputZone2").val('');
						jq("#inputZone2valid").attr('data-match', datamatch)
						jq("#inputZone2valid").attr('data-id', dataid)
						jq("#inputZone2valid").attr('data-value', '')
						jq("#inputZone2valid").attr('data-value2', 0)
					} else {
						if (data[4] != '') {
							var nomArb = data[2] + ' ' + data[3] + ' (' + data[4] + ') ' + data[5]
						} else {
							var nomArb = data[2] + ' ' + data[3] + ' ' + data[5]
						}
						jq("#inputZone2valid").attr('data-match', datamatch)
						jq("#inputZone2valid").attr('data-id', dataid)
						jq("#inputZone2valid").attr('data-value', nomArb)
						jq("#inputZone2valid").attr('data-value2', data[1])
						jq("#inputZone2").val(nomArb)
					}
				}
			})

		}
		jq(this).hide()
		setTimeout(function () {
			jq('#selectZone').select()
			jq('#inputZone').select()
			jq('#inputZone2').select()
		}, 0)
	})

	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function () {
		var Classe = jq(this).attr('class')
		validationDonnee(Classe)
	})
	jq('#inputZone2annul').live('click', function (event) {
		event.preventDefault
		jq('#inputZone2vid ~ span').show()
		jq('#inputZone2 + br').remove()
		jq('#inputZone2').remove()
		jq('#inputZone2valid').remove()
		jq('#inputZone2annul').remove()
		jq('#inputZone2vid').remove()
	})
	jq('#inputZone2valid').live('click', function (event) {
		event.preventDefault
		if (jq(this).attr('data-value') != '') {
			lavaleur = jq(this).attr('data-value')
		} else {
			lavaleur = jq('#inputZone2').val()
		}
		lavaleur2 = jq(this).attr('data-value2')
		lavaleur3 = lavaleur + '|' + lavaleur2
		lidMatch = jq(this).attr('data-match')
		lid = jq(this).attr('data-id')
		jq.post(
			'v2/saveArbitres.php', // Le fichier cible côté serveur.
			{
				idMatch: lidMatch,
				id: lid,
				value: lavaleur3
			},
			function (data) { // callback
				if (data) {
					lavaleur = lavaleur.replace(' (', ' <br />(')
					lavaleur = lavaleur.replace(') ', ')<br /> ')
					jq('#inputZone2vid ~ span').html(lavaleur)
					if (lavaleur2 == 0) {
						jq('#inputZone2vid ~ span').addClass('pbArb').attr('title', langue['Arbitre_non_identifie'])
					} else {
						jq('#inputZone2vid ~ span').removeClass('pbArb').attr('title', langue['Cliquez_pour_modifier'])
					}
					//compléter format(retour ligne, contrôle valeur n°arbitre)
					jq('#inputZone2vid ~ span').show()
					jq('#inputZone2 + br').remove()
					jq('#inputZone2').remove()
					jq('#inputZone2valid').remove()
					jq('#inputZone2annul').remove()
					jq('#inputZone2vid').remove()
				}
			},
			'text' // Format des données reçues.
		)
	})
	jq('#inputZone2vid').live('click', function (event) {
		event.preventDefault
		lavaleur = ''
		lavaleur2 = 0
		lavaleur3 = lavaleur + '|' + lavaleur2
		lidMatch = datamatch
		lid = dataid
		jq.post(
			'v2/saveArbitres.php', // Le fichier cible côté serveur.
			{
				idMatch: lidMatch,
				id: lid,
				value: lavaleur3
			},
			function (data) { // callback
				if (data) {
					lavaleur = lavaleur.replace(' (', ' <br />(')
					lavaleur = lavaleur.replace(') ', ')<br /> ')
					jq('#inputZone2vid ~ span').html(lavaleur)
					if (lavaleur2 == 0) {
						jq('#inputZone2vid ~ span').addClass('pbArb').attr('title', langue['Arbitre_non_identifie'])
					} else {
						jq('#inputZone2vid ~ span').removeClass('pbArb').attr('title', langue['Cliquez_pour_modifier'])
					}
					//compléter format(retour ligne, contrôle valeur n°arbitre)
					jq('#inputZone2vid ~ span').show()
					jq('#inputZone2 + br').remove()
					jq('#inputZone2').remove()
					jq('#inputZone2valid').remove()
					jq('#inputZone2annul').remove()
					jq('#inputZone2vid').remove()
				}
			},
			'text' // Format des données reçues.
		)
	})
	function validationDonnee (Classe) {
		var nouvelleValeur = jq('#inputZone').val()
		var tabindexVal = jq('#inputZone').attr('tabindex')
		if (Classe == 'directInputSpan') {
			jq('#inputZone + span').attr('tabindex', tabindexVal)
		} else if (Classe == 'directInputTd') {
			jq('#inputZone').parent('td').attr('tabindex', tabindexVal)
		}
		jq('#inputZone + span').show()
		var valeur = jq('#inputZone + span').text().trim()
		var identifiant = jq('#inputZone + span').attr('id')
		var identifiant2 = identifiant.split('-')
		var typeValeur = identifiant2[0]
		var numMatch = identifiant2[1]
		var formatValeur = identifiant2[2]
		if (valeur != nouvelleValeur) {
			valeurTransmise = nouvelleValeur
			var AjaxWhere = jq('#AjaxWhere').val()
			var AjaxTableName = jq('#AjaxTableName').val()
			var AjaxAnd = ''
			var AjaxUser = jq('#AjaxUser').val()
			jq.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numMatch,
					AjId2: '',
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function (data) {
					if (data != 'OK!') {
						alert(langue['MAJ_impossible'] + ' : ' + data)
					} else {
						jq('#' + identifiant).text(nouvelleValeur)
					}
				}
			)
		};
		jq('#inputZone').remove()
	}
	function validationDonnee2 () {
		var nouvelleValeur = jq('#inputZone2').val()
		var tabindexVal = jq('#inputZone2').attr('tabindex')
		jq('#inputZone2 + span').attr('tabindex', tabindexVal)
		jq('#inputZone2 + span').show()
		var valeur = jq('#inputZone2 + span').text()
		var identifiant = jq('#inputZone2 + span').attr('id')
		var identifiant2 = identifiant.split('-')
		var typeValeur = identifiant2[0]
		var numMatch = identifiant2[1]
		var formatValeur = identifiant2[2]
		if (valeur != nouvelleValeur && confirm(langue['Confirm_update'] + ' : ' + nouvelleValeur + ' ?')) {
			valeurTransmise = nouvelleValeur
			if (formatValeur == 'date') {
				valeurTransmise2 = valeurTransmise.split('/')
				valeurTransmise = valeurTransmise2[2] + '-' + valeurTransmise2[1] + '-' + valeurTransmise2[0]
			}
			var AjaxWhere = jq('#AjaxWhere').val()
			var AjaxTableName = jq('#AjaxTableName').val()
			var AjaxAnd = ''
			var AjaxUser = jq('#AjaxUser').val()

			/*			jq.get("UpdateCellJQ.php",
							{
								AjTableName: AjaxTableName,
								AjWhere: AjaxWhere,
								AjTypeValeur: typeValeur,
								AjValeur: valeurTransmise,
								AjAnd: AjaxAnd,
								AjId: numMatch,
								AjId2: '',
								AjUser: AjaxUser,
								AjOk: 'OK'
							},
							function(data){
								if(data != 'OK!'){
									alert('mise à jour impossible : '+data);
								}else{
									jq('#'+identifiant).text(nouvelleValeur);
								}
							}
						);
			*/
			jq('#' + identifiant).text(nouvelleValeur)
			jq('#' + identifiant).attr('data-idArb', jq('#inputZone2').attr('data-idArb'))

		};
		jq('#inputZone2').remove()
	}



	//Affiche, masque formulaire
	jq('#clickdown').toggle()
	jq('#clickup').click(function () {
		jq('.hideTr').toggle()
		jq('#clickdown').toggle()
	})
	jq('#clickdown').click(function () {
		jq('.hideTr').toggle()
		jq('#clickdown').toggle()
	})
	if (jq('#Num_match').val() == '') {
		jq('#clickup').click()
	}

	//Surligne l'événement filtré
	if (jq('#evenement').val() != '-1') {
		jq('#evenement').addClass('highlight4')
	}
	//Surligne la competition filtrée
	if (jq('#comboCompet').val() != '*') {
		jq('td>span.compet').addClass('highlight3')
		jq('#comboCompet').addClass('highlight3')
	}
	//Surligne le tour filtré
	if (jq('#filtreTour').val() != '') {
		jq('td>span.phase').addClass('highlight3')
		jq('td>span.lieu').addClass('highlight3')
		jq('#filtreTour').addClass('highlight3')
	}
	//Surligne la phase, le lieu filtrés
	if (jq('#comboJournee2').val() != '*') {
		jq('td>span.phase').addClass('highlight3')
		jq('td>span.lieu').addClass('highlight3')
		jq('#comboJournee2').addClass('highlight3')
	}
	//Surligne la date filtrée
	if (jq('#filtreJour').val() != '') {
		jq('td>span.date').addClass('highlight3')
		jq('#filtreJour').addClass('highlight3')
	}
	//Surligne le terrain filtré
	if (jq('#filtreTerrain').val() != '') {
		jq('td>span.terrain').addClass('highlight3')
		jq('#filtreTerrain').addClass('highlight3')
	}

	// Highlight
	jq('#reach').bind('keyup change', function (ev) {
		// pull in the new value
		var searchTerm = jq(this).val()
		// remove any old highlighted terms
		jq('.tableau').removeHighlight()
		// disable highlighting if empty
		if (searchTerm) {
			// highlight the new term
			jq('.tableau').highlight(searchTerm)
		}
	})

	// Highlight2
	jq('#reach2').bind('keyup change', function (ev) {
		// pull in the new value
		var searchTerm = jq(this).val()
		// remove any old highlighted terms
		jq('.tableau').removeHighlight2()
		// disable highlighting if empty
		if (searchTerm) {
			// highlight the new term
			jq('.tableau').highlight2(searchTerm)
		}
	})

	// 
	jq('#filterAtt').bind('change', function (ev) {
		jq.post(
			'v2/StatutSession.php', // Le fichier cible côté serveur.
			{ // variables
				Valeur: document.getElementById("filterAtt").checked ? 'on' : '',
				TypeUpdate: 'MatchsNonVerrouilles'
			},
			function (data) { // callback
				if (data == 'OK') {
					if (document.getElementById("filterAtt").checked) {
						jq(".verrouMatch[data-valeur!='N']").each(function() {
							jq(this).parent().parent().hide()
						})
						jq('#filterAttspan').addClass('highlight3')
					} else {
						jq(".verrouMatch[data-valeur!='N']").each(function() {
							jq(this).parent().parent().show()
						})
						jq('#filterAttspan').removeClass('highlight3')	
					}
				}
			},
			'text' // Format des données reçues.
		)
	})

	jq("body").delegate(".typeMatch", "click", function () {
		//jq("body").on("click", ".typeMatch", function(){
		//if(confirm('Confirmez-vous le changement de statut ?')){
		leMatch = jq(this)
		leMatch.attr('src', 'v2/images/indicator.gif')
		if (leMatch.attr('data-valeur') == 'C') {
			changeType = 'E'
			textType = 'Elimination'
		} else {
			changeType = 'C'
			textType = 'Classement'
		}
		jq.post(
			'v2/StatutPeriode.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Match: leMatch.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Type'
			},
			function (data) { // callback
				if (data == 'OK') {
					leMatch.attr('src', '../img/type' + changeType + '.png')
					leMatch.attr('data-valeur', changeType)
					leMatch.attr('title', textType)
				}
				else {
					alert(langue['MAJ_impossible'])
					leMatch.attr('src', '../img/type' + leMatch.attr('data-valeur') + '.png')
					leMatch.attr('data-valeur', leMatch.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
		//}
	})
	jq("#typeMatch1").click(function () {
		if (jq("#Type").val() == 'C') {
			jq("#Type").val("E")
			jq("#typeMatch1").attr("src", "../img/typeE.png").attr("title", langue['Match_eliminatoire'])
		} else {
			jq("#Type").val("C")
			jq("#typeMatch1").attr("src", "../img/typeC.png").attr("title", langue['Match_de_classement'])
		}
	})
	jq("#comboJournee").change(function () {
		loption = jq(this).val()
		leType = jq("#comboJournee option[value=" + loption + "]").attr('data-type')
		if (leType == 'E') {
			jq("#Type").val("E")
			jq("#typeMatch1").attr("src", "../img/typeE.png").attr("title", langue['Match_eliminatoire'])
		} else {
			jq("#Type").val("C")
			jq("#typeMatch1").attr("src", "../img/typeC.png").attr("title", langue['Match_de_classement'])
		}
	})
	jq(".publiMatch").click(function () {
		//if(confirm('Confirmez-vous le changement de publication ?')){
		leMatch = jq(this)
		leMatch.attr('src', 'v2/images/indicator.gif')
		if (leMatch.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = 'Private'
		} else {
			changeType = 'O'
			textType = 'Public'
		}
		jq.post(
			'v2/StatutPeriode.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Match: leMatch.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Publication'
			},
			function (data) { // callback
				if (data == 'OK') {
					leMatch.attr('src', '../img/oeil2' + changeType + '.gif')
					leMatch.attr('data-valeur', changeType)
					leMatch.attr('title', textType)
				} else {
					alert(langue['MAJ_impossible'])
					leMatch.attr('src', '../img/oeil2' + leMatch.attr('data-valeur') + '.gif')
					leMatch.attr('data-valeur', leMatch.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
		//}
	})
	jq(".verrouMatch").click(function () {
		//if(confirm('Confirmez-vous le changement de publication ?')){
		leMatch = jq(this)
		leMatch.attr('src', 'v2/images/indicator.gif')
		if (leMatch.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = langue['Non_valide']
		} else {
			changeType = 'O'
			textType = langue['Valide']
		}
		jq.post(
			'v2/StatutPeriode.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Match: leMatch.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Validation'
			},
			function (data) { // callback
				if (data == 'OK') {
					leMatch.attr('src', '../img/verrou2' + changeType + '.gif')
					leMatch.attr('data-valeur', changeType)
					leMatch.attr('title', textType)
					if (changeType == 'O') {
						leMatch.parent().parent().find('.directInput').addClass('directInputOff').removeClass('directInput')
						leMatch.parent().parent().parent().find('.directStatutMatch').addClass('directStatutMatchOff').removeClass('directStatutMatch')
						leMatch.parent().parent().find('.showOn').addClass('showOff').removeClass('showOn')
						leMatch.parent().parent().find('.typeMatch').addClass('typeMatchOff').removeClass('typeMatch')
						leMatch.parent().parent().find('.imprimMatch').addClass('imprimMatchOff').removeClass('imprimMatch')
					} else {
						leMatch.parent().parent().find('.directInputOff').addClass('directInput').removeClass('directInputOff')
						leMatch.parent().parent().parent().find('.directStatutMatchOff').addClass('directStatutMatch').removeClass('directStatutMatchOff')
						leMatch.parent().parent().find('.showOff').addClass('showOn').removeClass('showOff')
						leMatch.parent().parent().find('.typeMatchOff').addClass('typeMatch').removeClass('typeMatchOff')
						leMatch.parent().parent().find('.imprimMatchOff').addClass('imprimMatch').removeClass('imprimMatchOff')
					}
				}
				else {
					alert(langue['MAJ_impossible'])
					leMatch.attr('src', '../img/verrou2' + leMatch.attr('data-valeur') + '.gif')
					leMatch.attr('data-valeur', leMatch.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
		//}
	})

	jq("body").delegate(".imprimMatch", "click", function () {
		// This handler will work for dynamically added elements and multiple simultaneous clicks
		var theMatch = jq(this)
		// theMatch.attr('src', 'v2/images/indicator.gif')
		var changeType, textType
		if (theMatch.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = ''
		} else {
			changeType = 'O'
			textType = 'Printed'
		}
		jq.post(
			'v2/StatutPeriode.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Match: theMatch.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Imprime'
			},
			function (data) { // callback
				if (data == 'OK') {
					theMatch.attr('src', '../img/imprime' + changeType + '.png')
					theMatch.attr('data-valeur', changeType)
					theMatch.attr('title', textType)
				} else {
					alert(langue['MAJ_impossible'])
				}
			},
			'text' // Format des données reçues.
		)
	})


	jq("body").delegate(".directStatutMatch", "click", function () {
		if (confirm('Confirmez-vous le changement de statut ?')) {
			leMatch = jq(this)
			statut = leMatch.attr('data-statut')
			periode = leMatch.attr('data-periode')
			leMatch.html('<img src="v2/images/indicator.gif" height="23">')
			if (statut == '0' || statut == 'ATT') {
				changeType = 'ON'
			} else if (statut == 'ON') {
				changeType = 'END'
			} else {
				changeType = 'ATT'
			}
			jq.post(
				'v2/StatutPeriode.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Match: leMatch.attr('data-id'),
					Valeur: changeType,
					TypeUpdate: 'Statut'
				},
				function (data) { // callback
					if (data == 'OK') {
						leMatch.attr('data-statut', changeType)
						leMatch.attr('title', '')
						if (changeType == 'ON') {
							leMatch.html(changeType)
								.addClass('statutMatchOn')
								.removeClass('scoreProvisoire')
							leMatch.next().removeClass('hidden')
						} else if (changeType == 'END') {
							leMatch.html(changeType)
						} else {
							leMatch.html(changeType)
								.addClass('scoreProvisoire')
								.removeClass('statutMatchOn')
							leMatch.next().addClass('hidden')
						}
					} else {
						alert(langue['MAJ_impossible'])
						leMatch.html(changeType)
					}
				},
				'text' // Format des données reçues.
			)
		}
	})

	if (arrayCheck != '') {
		arrayCheck = arrayCheck.split(',').forEach(function (item) {
			console.log(item)
			jq('input[type="checkbox"][value="' + item + '"]').click()
		})
	}


});


