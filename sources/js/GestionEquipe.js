jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Cliquez_pour_modifier'] = 'Click to edit'
	langue['Confirmer_MAJ'] = 'Confirm composition update ?'
	langue['Confirmer_verrouillage'] = 'Confirm presence sheets lock / unlock ?'
	langue['MAJ_impossible'] = 'Unable to update'
	langue['Nom_equipe_vide'] = 'Team name is empty, unable to create'
	langue['Rechercher_equipe'] = 'Search team'
	langue['Selectionner_competition'] = 'Select a competition'
	langue['Selectionner_club'] = 'Select a club'
	langue['Verrouiller_avant'] = 'Lock presence sheets before !'
} else {
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
	langue['Confirmer_MAJ'] = 'Confirmez-vous la mise à jour des feuilles de matchs ?'
	langue['Confirmer_verrouillage'] = 'Confirmez-vous le verrouillage / déverrouillage des feuilles de présence ?'
	langue['MAJ_impossible'] = 'Mise à jour impossible'
	langue['Nom_equipe_vide'] = 'Le Nom de l\'Equipe est vide, ajout impossible'
	langue['Rechercher_equipe'] = 'Rechercher une équipe'
	langue['Selectionner_competition'] = 'Sélectionnez une compétition'
	langue['Selectionner_club'] = 'Sélectionnez un club'
	langue['Verrouiller_avant'] = 'Verrouillez les feuilles de présence avant !'
}

function changeCompetition () {
	jq("#ParamCmd").val('')
	jq("#formEquipe").submit()
}

function changeComiteReg () {
	jq("#ParamCmd").val('changeComiteReg')
	jq("#formEquipe").submit()
}

function changeComiteDep () {
	jq("#ParamCmd").val('changeComiteDep')
	jq("#formEquipe").submit()
}

function changeClub () {
	jq("#ParamCmd").val('changeClub')
	jq("#formEquipe").submit()
}

function validEquipe () {
	var histoEquipe = jq("#histoEquipe").val()

	if (histoEquipe != null && histoEquipe != '0' && histoEquipe != '') {
		return true // Une Equipe de l'historique est sélectionnée ...
	}

	var competition = jq("#competition").val()
	if (competition == '') {
		alert(langue['Selectionner_competition'] + " !")
		return false
	}

	var libelleEquipe = jq("#libelleEquipe").val()
	if ((histoEquipe == null || histoEquipe == '0') && libelleEquipe.length == 0) {
		alert(langue['Nom_equipe_vide'] + " !")
		return false
	}

	var codeClub = jq("#club").val()
	if (codeClub == '*') {
		alert(langue['Selectionner_club'] + " !")
	}
	if ((histoEquipe == null || histoEquipe == '0') && codeClub.length > 0 && codeClub != '*') {
		jq("#histoEquipe").val(0)
		return true // Le Code du Club est bon ...
	}

	alert(" !")
	return false
}

function validEquipe2 () {
	var EquipeNum = jq("#EquipeNum").val()

	if ((EquipeNum.length > 0) && (EquipeNum[0] != '0'))
		return true // Une Equipe est sélectionnée ...

	var libelleEquipe = jq("#EquipeNom").val()
	if (libelleEquipe.length == 0) {
		alert(langue['Rechercher_equipe'] + " !")
		return false
	}

	var competition = jq("#competition").val()
	if (competition == '') {
		alert(langue['Selectionner_competition'] + " !")
		return false
	}
}

function Add () {
	if (!validEquipe()) {
		return
	}
	jq("#Cmd").val('Add')
	jq("#ParamCmd").val('')
	jq("#formEquipe").submit()
}

function Add2 () {
	if (!validEquipe2())
		return
	jq("#Cmd").val('Add2')
	jq("#ParamCmd").val('')
	jq("#formEquipe").submit()
}

function UpdateLogos () {
	jq("#Cmd").val('updateLogos')
	jq("#ParamCmd").val('')
	jq("#formEquipe").submit()
}

function Tirage () {
	jq("#Cmd").val('Tirage')
	jq("#ParamCmd").val('')
	jq("#formEquipe").submit()
}

function changeHistoEquipe () {
	var histoEquipe = jq("#histoEquipe").val()

	if (histoEquipe == '0' || histoEquipe == '' || histoEquipe == null) {
		jq("#libelleEquipe").removeAttr('disabled')
	} else {
		jq("#libelleEquipe").attr('disabled', 'disabled').attr('placeholder', '')
	}
}

jq(document).ready(function () {
	//Init Titulaires
	jq('#InitTitulaireCompet').click(function () {
		var champs = 'Compet'
		var valeur = jq('#competition').val()
		var valeur2 = jq('#competition option:selected').text()
		if (valeur == '*') {
			alert(langue['Selectionner_competition'] + " !")
			return
		}
		if (jq('#verrouCompet').attr('data-verrou') == 'N') {
			alert(langue['Verrouiller_avant'])
			return
		}
		if (!confirm(langue['Confirmer_MAJ'] + '\n' + champs + ' : ' + valeur2)) {
			return
		}
		//ajax
		jq.post("InitTitulaireJQ.php", {
			champs: champs,
			valeur: valeur,
			valeur3: -1
		}, function (data) {
			alert(data)
		})
	})

	//Init Titulaires
	jq('#verrouCompet').click(function () {
		if (!confirm(langue['Confirmer_verrouillage'])) {
			return
		}
		//ajax
		jq.post("VerrouCompetJQ.php", {
			verrou: jq('#verrouCompet').attr('data-verrou'),
			compet: jq('#competition').val(),
		}, function (data) {
			if (data == 'O' || data == 'N') {
				jq('#verrouCompet').attr('src', '../img/verrou2' + data + '.gif')
					.attr('data-verrou', data)
			} else {
				alert(data)
			}
		})
	})

	jq.extend(jq.expr[':'], {
		'icontains': function (elem, i, match, array) {
			return (elem.textContent || elem.innerText || '').toLowerCase()
				.indexOf((match[3] || "").toLowerCase()) >= 0
		}
	})

	jq("#filtreText").keyup(function () {
		var str = jq(this).val()
		jq("#histoEquipe option")
			.hide()
			.filter(':icontains("' + str + '")')
			.show()
	})
	jq("#filtreTextButton").click(function () {
		var str = jq("#filtreText").val()
		jq("#histoEquipe option").show()
		if (str != '') {
			jq("#histoEquipe option").hide()
			jq("#histoEquipe option:icontains('" + str + "')").show()
		}
	})
	jq("#filtreAnnulButton").click(function () {
		jq("#filtreText").val('')
		jq("#histoEquipe option").show()
	})

	// Actualiser
	jq('#actuButton').click(function () {
		jq('#formEquipe').submit()
	})


	// Maskedinput
	jq.mask.definitions['h'] = "[A-Z]"
	//jq("#inputZone").mask("9");


	// Direct Input (date, heure, intitule)
	//Ajout title
	jq('.directInput').attr('title', langue['Cliquez_pour_modifier'])
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableEquipes').bind('keydown', function (e) {
		if (e.which == 13) {
			validationDonnee()
			return false
		}
	})
	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function () {
		var Classe = jq(this).attr('class')
		validationDonnee(Classe)
	})
	// focus sur un span du tableau => remplace le span par un input
	jq('#tableEquipes td > span.directInput').focus(function (event) {
		event.preventDefault()
		var valeur = jq(this).text()
		var tabindexVal = jq(this).attr('tabindex')
		jq(this).attr('tabindex', tabindexVal + 1000)
		if (jq(this).hasClass('textPoule')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="2" value="' + valeur + '">')
			jq('#inputZone').mask("h?h", { placeholder: " " }).select()
		}
		else if (jq(this).hasClass('textTirage')) {
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="' + tabindexVal + '" size="2" value="' + valeur + '">')
			jq('#inputZone').mask("9?9", { placeholder: " " }).select()
		}
		jq(this).hide()
	})

	// Validation des données 
	function validationDonnee (Classe) {
		var nouvelleValeur = jq('#inputZone').val()
		var tabindexVal = jq('#inputZone').attr('tabindex')
		if (Classe == 'directInputSpan') {
			jq('#inputZone + span').attr('tabindex', tabindexVal)
		} else if (Classe == 'directInputTd') {
			jq('#inputZone').parent('td').attr('tabindex', tabindexVal)
		}
		jq('#inputZone + span').show()
		var valeur = jq('#inputZone + span').text()
		var identifiant = jq('#inputZone + span').attr('id')
		var identifiant2 = identifiant.split('-')
		var typeValeur = identifiant2[0]
		var numMatch = identifiant2[1]
		var formatValeur = identifiant2[2]
		if (valeur != nouvelleValeur) {
			valeurTransmise = nouvelleValeur
			if (formatValeur == 'date') {
				valeurTransmise2 = valeurTransmise.split('/')
				valeurTransmise = valeurTransmise2[2] + '-' + valeurTransmise2[1] + '-' + valeurTransmise2[0]
			}
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

	//Autocomplete recherche equipe
	jq('#plEquipe').mask("h?h", { placeholder: " " })
	jq('#tirEquipe').mask("9?9", { placeholder: " " })
	jq('#cltChEquipe').mask("9?9", { placeholder: " " })
	jq('#cltCpEquipe').mask("9?9", { placeholder: " " })
	jq('#ShowCompo').hide()
	jq("#choixEquipe").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	})
	jq("#choixEquipe").result(function (event, data, formatted) {
		if (data) {
			var lequipe = data[1]
			var lasaison = jq("#Saison").val()
			jq("#EquipeNom").val(data[0])
			jq('#EquipeNum').val(lequipe)
			jq('#EquipeNumero').val(lequipe)
			jq('#ShowCompo').show()
			jq.get("Autocompl_getCompo.php", { q: lequipe, s: lasaison }).done(function (data2) {
				jq('#GetCompo').html(data2)//"REPRISE DES COMPOSITIONS D'EQUIPE:<br>"
			})
		}
	})
	jq("#annulEquipe2").click(function () {
		jq('#ShowCompo').hide()
		jq('#plEquipe').val('')
		jq('#tirEquipe').val('')
		jq('#cltChEquipe').val('')
		jq('#cltCpEquipe').val('')
	})

	// Edit equipe
	jq('.editEquipe').click(function () {
		jq('#editTeamLabel').text(jq(this).data('label'))
		jq('#editTeamId').val(jq(this).data('equipe'))
		jq('#editTeamLogo').val(jq(this).data('logo'))
		jq('#editTeamColor1').val(jq(this).data('color1'))
		jq('#editTeamColor2').val(jq(this).data('color2'))
		jq('#editTeamImg').attr('src', '../img/KIP/colors/' + jq(this).data('numero') + '-colors.png')
		jq('#editTeam').show()
		jq('#resetTeam').focus()
	})

	jq('#resetTeam').click(function () {
		jq('#editTeam').hide()
	})

	jq('#updateTeam').click(function () {
		jq.post(
			"ajax_update_team.php",
			{
				equipe: jq('#editTeamId').val(),
				logo: jq('#editTeamLogo').val(),
				colorChangeNext: jq('#editTeamColorChangeNext').attr('checked'),
				colorChangeLast: jq('#editTeamColorChangeLast').attr('checked'),
				colorChangeClub: jq('#editTeamColorChangeClub').attr('checked'),
				color1: jq('#editTeamColor1').val(),
				color2: jq('#editTeamColor2').val()
			})
			.done(function () {
				jq('#formEquipe').submit()
			})
	})

})

