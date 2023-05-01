jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Cliquez_pour_modifier'] = 'Click to edit'
	langue['Confirmer'] = 'Confirm ?'
	langue['Confirmer_verrouillage'] = 'Confirm presence sheets lock / unlock ?'
	langue['MAJ_impossible'] = 'Unable to update'
	langue['Code_competition_vide'] = 'Competition code is empty, unable to create'
	langue['Label_competition_vide'] = 'Competition label is empty, unable to create'
	langue['Rechercher_equipe'] = 'Search team'
	langue['Selectionner_competition'] = 'Select a competition'
	langue['Selectionner_club'] = 'Select a club'
	langue['Verrouiller_avant'] = 'Lock presence sheets before !'
} else {
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
	langue['Confirmer'] = 'Confirmez-vous ?'
	langue['Confirmer_verrouillage'] = 'Confirmez-vous le verrouillage / déverrouillage des feuilles de présence ?'
	langue['MAJ_impossible'] = 'Mise à jour impossible'
	langue['Code_competition_vide'] = 'Code compétition vide, ajout impossible'
	langue['Label_competition_vide'] = 'Label compétition vide, ajout impossible'
	langue['Rechercher_equipe'] = 'Rechercher une équipe'
	langue['Selectionner_competition'] = 'Sélectionnez une compétition'
	langue['Selectionner_club'] = 'Sélectionnez un club'
	langue['Verrouiller_avant'] = 'Verrouillez les feuilles de présence avant !'
}

function uploadLogo () {
	document.forms['formCompet'].elements['Cmd'].value = 'UploadLogo'
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}

function dropLogo () {
	if (!confirm(langue['Confirmer'])) {
		return
	}
	else {
		document.forms['formCompet'].elements['Cmd'].value = 'DropLogo'
		document.forms['formCompet'].elements['ParamCmd'].value = ''
		document.forms['formCompet'].submit()
	}
}

function changeAffiche () {
	document.forms['formCompet'].elements['Cmd'].value = ''
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}

function changeAuthSaison () {
	document.forms['formCompet'].elements['Cmd'].value = 'ChangeAuthSaison'
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}

function verrou (VerrouCompet, verrouEtat) {
	if (!confirm(langue['Confirmer'])) {
		return
	}
	else {
		document.forms['formCompet'].elements['Cmd'].value = 'Verrou'
		document.forms['formCompet'].elements['verrouCompet'].value = VerrouCompet
		document.forms['formCompet'].elements['Verrou'].value = verrouEtat
		document.forms['formCompet'].submit()
	}
}

function AddSaison () {
	if (!confirm(langue['Confirmer'])) {
		return
	}
	else {
		document.forms['formCompet'].elements['Cmd'].value = 'AddSaison'
		document.forms['formCompet'].elements['ParamCmd'].value = ''
		document.forms['formCompet'].submit()
	}
}

function validCompet () {
	var codeCompet = document.forms['formCompet'].elements['codeCompet'].value
	if (codeCompet.length == 0) {
		alert(langue['Code_competition_vide'])
		return false
	}

	var labelCompet = document.forms['formCompet'].elements['labelCompet'].value
	if (labelCompet.length == 0) {
		alert(langue['Label_competition_vide'])
		return false
	}

	return true
}

function Add () {
	if (!validCompet())
		return

	document.forms['formCompet'].elements['Cmd'].value = 'Add'
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}

function sessionSaison () {
	if (!confirm(langue['Confirmer'])) {
		document.forms['formCompet'].reset
		return
	} else {
		document.forms['formCompet'].elements['Cmd'].value = 'SessionSaison'
		document.forms['formCompet'].elements['ParamCmd'].value = document.forms['formCompet'].elements['saisonTravail'].value
		document.forms['formCompet'].submit()
	}
}

function activeSaison () {
	if (!confirm(langue['Confirmer'])) {
		document.forms['formCompet'].reset
		return
	} else if (!confirm(langue['Confirmer'])) {
		document.forms['formCompet'].reset
		return
	} else {
		document.forms['formCompet'].elements['Cmd'].value = 'ActiveSaison'
		document.forms['formCompet'].elements['ParamCmd'].value = document.forms['formCompet'].elements['saisonActive'].value
		document.forms['formCompet'].submit()
	}
}

function publiCompet (idCompet, pub) {
	if (!confirm(langue['Confirmer'])) {
		return false
	}

	document.forms['formCompet'].elements['Cmd'].value = 'PubliCompet'
	document.forms['formCompet'].elements['ParamCmd'].value = idCompet
	document.forms['formCompet'].elements['Pub'].value = pub
	document.forms['formCompet'].submit()
}

function updateCompet () {
	if (!validCompet())
		return

	document.forms['formCompet'].elements['Cmd'].value = 'UpdateCompet'
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}

function razCompet () {
	document.forms['formCompet'].elements['Cmd'].value = 'RazCompet'
	document.forms['formCompet'].elements['ParamCmd'].value = ''
	document.forms['formCompet'].submit()
}


function paramCompet (idCompet) {
	document.forms['formCompet'].elements['Cmd'].value = 'ParamCompet'
	document.forms['formCompet'].elements['ParamCmd'].value = idCompet
	document.forms['formCompet'].submit()
}

jq(document).ready(function () {

	// Maskedinput
	//jq.mask.definitions['h'] = "[A-O]";
	jq('.dpt').mask("?***")
	if (lang == 'en') {
		jq('.date').mask("9999-99-99")
	} else {
		jq('.date').mask("99/99/9999")
	}
	//jq("#inputZone").mask("9");

	// jq("*").tooltip()

	jq("#choixCompet").autocomplete('Autocompl_compet.php', {
		width: 350,
		max: 30,
		mustMatch: true,
		minLength: 2,
		cacheLength: 0,
		//multiple: true,
		//matchContains: true,
		//formatItem: formatItem,
		//formatResult: formatResult
		//selectFirst: false
	})
	jq("#choixCompet").result(function (event, data, formatted) {
		if (data) {
			jq("#codeCompet").val(data[1])
			jq("#labelCompet").val(data[2])
			jq("#niveauCompet").val(data[3])
			jq("#codeRef").val(data[4])
			jq("#codeTypeClt").val(data[5])
			jq("#etape").val(data[6])
			jq("#qualifies").val(data[7])
			jq("#elimines").val(data[8])
			jq('#points[value="' + data[9] + '"]').attr('checked', 'checked')
			jq("#soustitre").val(data[10])
			jq("#web").val(data[11])
			jq("#logoLink").val(data[12])
			jq("#sponsorLink").val(data[13])
			jq("#toutGroup").val(data[14])
			jq("#touteSaisons").val(data[15])
			jq("#groupOrder").val(data[16])
			jq("#soustitre2").val(data[17])
			if (data[18] == 'O') {
				jq("#checktitre").attr('checked', 'checked')
			} else {
				jq("#checktitre").attr('checked', '')
			}
			if (data[19] == 'O') {
				jq("#checklogo").attr('checked', 'checked')
			} else {
				jq("#checklogo").attr('checked', '')
			}
			if (data[20] == 'O') {
				jq("#checksponsor").attr('checked', 'checked')
			} else {
				jq("#checksponsor").attr('checked', '')
			}
			if (data[21] == 'O') {
				jq("#checkkpiffck").attr('checked', 'checked')
			} else {
				jq("#checkkpiffck").attr('checked', '')
			}
			if (data[22] == 'O') {
				jq("#checken").attr('checked', 'checked')
			} else {
				jq("#checken").attr('checked', '')
			}
			if (data[23] == 'O') {
				jq("#checkbandeau").attr('checked', 'checked')
			} else {
				jq("#checkbandeau").attr('checked', '')
			}
			jq("#bandeauLink").val(data[23])
			jq("#goalaverage[value='" + data[25] + "']").attr('checked', 'checked')
		}
	})
	jq("#bandeauLink").blur(function () {
		lien = jq("#bandeauLink").val()
		if (lien.indexOf('http') != -1) {
			jq("#bandeauprovisoire").attr('src', lien)
		} else {
			jq("#bandeauprovisoire").attr('src', '../img/logo/' + lien)
		}
	})
	jq("#logoLink").blur(function () {
		lien = jq("#logoLink").val()
		if (lien.indexOf('http') != -1) {
			jq("#logoprovisoire").attr('src', lien)
		} else {
			jq("#logoprovisoire").attr('src', '../img/logo/' + lien)
		}
	})
	jq("#sponsorLink").blur(function () {
		lien = jq("#sponsorLink").val()
		if (lien.indexOf('http') != -1) {
			jq("#sponsorprovisoire").attr('src', lien)
		} else {
			jq("#sponsorprovisoire").attr('src', '../img/logo/' + lien)
		}
	})
	jq("#bandeauLink").blur()
	jq("#logoLink").blur()
	jq("#sponsorLink").blur()

	jq("#toutGroup").bind('click', function () {
		alert('Vous allez affecter ces données (Sous-titre, Lien web, logo, sponsor) à toutes les compétitions du groupe ET perdre les données des autres compétitions du groupe !')
	})
	jq("#touteSaisons").bind('click', function () {
		alert('Vous allez affecter ces données (Sous-titre, Lien web, logo, sponsor) à toutes les saisons de la compétition ou du groupe ET perdre les données des autres saisons !')
	})
	//Fusion joueurs
	jq("#FusionSource").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#FusionSource").result(function (event, data, formatted) {
		if (data) {
			jq("#numFusionSource").val(data[1])
			jq("#FusionSource").val(data[0])
		}
	})
	jq("#FusionCible").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#FusionCible").result(function (event, data, formatted) {
		if (data) {
			jq("#numFusionCible").val(data[1])
			jq("#FusionCible").val(data[0])
		}
	})
	jq("#FusionJoueurs").click(function () {
		var fusSource = jq("#FusionSource").val()
		var fusCible = jq("#FusionCible").val()
		if (!confirm('Confirmez-vous la fusion : ' + fusSource + ' => ' + fusCible + ' ?')) {
			return false
		}
		document.forms['formCompet'].elements['Cmd'].value = 'FusionJoueurs'
		document.forms['formCompet'].submit()
	})

	//Renomme Equipe
	jq("#RenomSource").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#RenomSource").result(function (event, data, formatted) {
		if (data) {
			jq("#numRenomSource").val(data[1])
			jq("#RenomSource").val(data[2])
			jq("#RenomCible").val(data[2])
		}
	})
	jq("#RenomEquipe").click(function () {
		var renSource = jq("#RenomSource").val()
		var renCible = jq("#RenomCible").val()
		if (!confirm('Confirmez-vous la modification :\n' + renSource + ' => ' + renCible + ' ?')) {
			return false
		}
		document.forms['formCompet'].elements['Cmd'].value = 'RenomEquipe'
		document.forms['formCompet'].submit()
	})

	//Fusion équipes
	jq("#FusionEquipeSource").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#FusionEquipeSource").result(function (event, data, formatted) {
		if (data) {
			jq("#numFusionEquipeSource").val(data[1])
			jq("#FusionEquipeSource").val(data[2])
		}
	})
	jq("#FusionEquipeCible").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#FusionEquipeCible").result(function (event, data, formatted) {
		if (data) {
			jq("#numFusionEquipeCible").val(data[1])
			jq("#FusionEquipeCible").val(data[2])
		}
	})
	jq("#FusionEquipes").click(function () {
		var fusSource = jq("#FusionEquipeSource").val()
		var fusCible = jq("#FusionEquipeCible").val()
		if (!confirm('Confirmez-vous la fusion : ' + fusSource + ' => ' + fusCible + ' ?')) {
			return false
		}
		jq('#Cmd').val('FusionEquipes')
		jq('#formCompet').submit()
	})

	//Déplacement équipe
	jq("#DeplaceEquipeSource").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#DeplaceEquipeSource").result(function (event, data, formatted) {
		if (data) {
			jq("#numDeplaceEquipeSource").val(data[1])
			jq("#DeplaceEquipeSource").val(data[0])
		}
	})
	jq("#DeplaceEquipeCible").autocomplete('Autocompl_club2.php', {
		width: 550,
		max: 50,
		mustMatch: false,
		cacheLength: 0
	})
	jq("#DeplaceEquipeCible").result(function (event, data, formatted) {
		if (data) {
			jq("#numDeplaceEquipeCible").val(data[2])
			jq("#DeplaceEquipeCible").val(data[0])
		}
	})
	jq("#DeplaceEquipe").click(function () {
		var depSource = jq("#DeplaceEquipeSource").val()
		var depCible = jq("#DeplaceEquipeCible").val()
		if (!confirm('Confirmez-vous le déplacement : ' + depSource + ' => ' + depCible + ' ?')) {
			return false
		}
		jq('#Cmd').val('DeplaceEquipe')
		jq('#formCompet').submit()
	})

	//Changement code competition
	jq("#ChangeCodeRecherche").autocomplete('Autocompl_compet.php?saison=' + jq('#saisonTravail').val(), {
		width: 550,
		max: 30,
		mustMatch: true,
		minLength: 2,
		cacheLength: 0
	})
	jq("#ChangeCodeRecherche").result(function (event, data, formatted) {
		if (data) {
			jq("#changeCodeSource").val(data[1])
		}
	})
	jq("#ChangeCodeBtn").click(function () {
		var changeCodeSource = jq("#changeCodeSource").val()
		var changeCodeCible = jq("#changeCodeCible").val()
		if (!confirm('Confirmez-vous le changement de code pour la saison : ' + changeCodeSource + ' => ' + changeCodeCible + ' ?')) {
			return false
		}
		jq('#Cmd').val('ChangeCode')
		jq('#formCompet').submit()
	})

	//TitreJournee labelCompet
	jq("#TitreJournee").focus(function () {
		var TitreJournee = jq("#labelCompet").val()
		jq("#TitreJournee").val(TitreJournee)
	})
	//Accès Feuille
	jq("#accesFeuilleBtn").click(function () {
		var accesFeuille = jq("#accesFeuille").val()
		if (!confirm('Confirmez-vous l\'accès à la feuille ' + accesFeuille + ' ?')) {
			return false
		}
		window.open('FeuilleMarque2.php?idMatch=' + accesFeuille, 'Feuille')
	})

	jq(".publiCompet").click(function () {
		//if(confirm('Confirmez-vous le changement de publication ?')){
		laCompet = jq(this)
		laCompet.attr('src', 'v2/images/indicator.gif')
		laSaison = jq('#saisonTravail').val()
		if (laCompet.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = 'Non public'
		} else {
			changeType = 'O'
			textType = 'Public'
		}
		jq.post(
			'v2/StatutCompet.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Compet: laCompet.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Publication',
				idSaison: laSaison
			},
			function (data) { // callback
				if (data == 'OK') {
					laCompet.attr('src', '../img/oeil2' + changeType + '.gif')
					laCompet.attr('data-valeur', changeType)
					laCompet.attr('title', textType)
				} else {
					alert('Changement impossible <br />' + data)
					laCompet.attr('src', '../img/oeil2' + laCompet.attr('data-valeur') + '.gif')
					laCompet.attr('data-valeur', laCompet.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
		//}
	})
	jq(".verrouCompet").click(function () {
		//if(confirm('Confirmez-vous le changement de publication ?')){
		laCompet = jq(this)
		laCompet.attr('src', 'v2/images/indicator.gif')
		laSaison = jq('#saisonTravail').val()
		if (laCompet.attr('data-valeur') == 'O') {
			changeType = 'N'
			textType = 'Feuilles de présence modifiables'
		} else {
			changeType = 'O'
			textType = 'Feuilles de présence verrouillées'
		}
		jq.post(
			'v2/StatutCompet.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Compet: laCompet.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Verrou',
				idSaison: laSaison
			},
			function (data) { // callback
				if (data == 'OK') {
					laCompet.attr('src', '../img/verrou2' + changeType + '.gif')
					laCompet.attr('data-valeur', changeType)
					laCompet.attr('title', textType)
				}
				else {
					alert('Changement impossible <br />' + data)
					laCompet.attr('src', '../img/verrou2' + laCompet.attr('data-valeur') + '.gif')
					laCompet.attr('data-valeur', laCompet.attr('data-valeur'))
				}
			},
			'text' // Format des données reçues.
		)
		//}
	})
	jq(".statutCompet").click(function () {
		laCompet = jq(this)
		statut = laCompet.text()
		laCompet.html('<img src="v2/images/indicator.gif" height="23">')
		laSaison = jq('#saisonTravail').val()
		if (statut == '0' || statut == 'ATT') {
			changeType = 'ON'
		} else if (statut == 'ON') {
			changeType = 'END'
		} else {
			changeType = 'ATT'
		}
		jq.post(
			'v2/StatutCompet.php', // Le fichier cible côté serveur.
			{ // variables
				Id_Compet: laCompet.attr('data-id'),
				Valeur: changeType,
				TypeUpdate: 'Statut',
				idSaison: laSaison
			},
			function (data) { // callback
				if (data == 'OK') {
					laCompet.html(changeType)
					laCompet.removeClass('statutCompetATT statutCompetON statutCompetEND').addClass('statutCompet' + changeType)
				}
				else {
					laCompet.html(statut)
					alert('Changement impossible <br />' + data)
				}
			},
			'text' // Format des données reçues.
		)

	})

	//jq('#tableCompet').fixedHeaderTable('hide');


})


if (top.location != self.document.location) {
	alert('Vous quittez le site parent !')
	top.location = self.document.location
}