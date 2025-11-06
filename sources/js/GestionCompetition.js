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

	// Maskedinput removed - obsolete (dates use Flatpickr, departments use HTML5 pattern)
	//jq.mask.definitions['h'] = "[A-O]";
	// jq('.dpt').mask("?***")
	// if (lang == 'en') {
	// 	jq('.date').mask("9999-99-99")
	// } else {
	// 	jq('.date').mask("99/99/9999")
	// }
	//jq("#inputZone").mask("9");

	// jq("*").tooltip()

	// Migration jQuery autocomplete → Vanilla JS (format JSON)
	vanillaAutocomplete('#choixCompet', 'Autocompl_compet.php', {
		width: 350,
		maxResults: 30,
		minChars: 2,
		cacheLength: 0,
		dataType: 'json',
		formatItem: function(item) {
			return item.label;  // "Code - Libelle"
		},
		formatResult: function(item) {
			return item.value;
		},
		onSelect: function (item, index) {
			if (item) {
				jq("#codeCompet").val(item.code);
				jq("#labelCompet").val(item.libelle);
				jq("#niveauCompet").val(item.codeNiveau);
				jq("#codeRef").val(item.codeRef);
				jq("#codeTypeClt").val(item.codeTypeclt);
				jq("#etape").val(item.codeTour);
				jq("#qualifies").val(item.qualifies);
				jq("#elimines").val(item.elimines);
				jq('#points[value="' + item.points + '"]').attr('checked', 'checked');
				jq("#soustitre").val(item.soustitre);
				jq("#web").val(item.web);
				jq("#logoLink").val(item.logoLink);
				jq("#sponsorLink").val(item.sponsorLink);
				jq("#toutGroup").val(item.toutGroup);
				jq("#touteSaisons").val(item.touteSaisons);
				jq("#groupOrder").val(item.groupOrder);
				jq("#soustitre2").val(item.soustitre2);
				if (item.titreActif == 'O') {
					jq("#checktitre").attr('checked', 'checked');
				} else {
					jq("#checktitre").attr('checked', '');
				}
				if (item.logoActif == 'O') {
					jq("#checklogo").attr('checked', 'checked');
				} else {
					jq("#checklogo").attr('checked', '');
				}
				if (item.sponsorActif == 'O') {
					jq("#checksponsor").attr('checked', 'checked');
				} else {
					jq("#checksponsor").attr('checked', '');
				}
				if (item.kpiFfckActif == 'O') {
					jq("#checkkpiffck").attr('checked', 'checked');
				} else {
					jq("#checkkpiffck").attr('checked', '');
				}
				if (item.enActif == 'O') {
					jq("#checken").attr('checked', 'checked');
				} else {
					jq("#checken").attr('checked', '');
				}
				if (item.bandeauActif == 'O') {
					jq("#checkbandeau").attr('checked', 'checked');
				} else {
					jq("#checkbandeau").attr('checked', '');
				}
				jq("#bandeauLink").val(item.bandeauLink);
				jq("#goalaverage[value='" + item.goalaverage + "']").attr('checked', 'checked');
			}
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
	vanillaAutocomplete('#FusionSource', 'Autocompl_joueur.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionSource").val(item.matric);
				jq("#FusionSource").val(item.label);
			}
		}
	});
	vanillaAutocomplete('#FusionCible', 'Autocompl_joueur.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionCible").val(item.matric);
				jq("#FusionCible").val(item.label);
			}
		}
	});
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
	vanillaAutocomplete('#RenomSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numRenomSource").val(item.numero);
				jq("#RenomSource").val(item.libelle);
				jq("#RenomCible").val(item.libelle);
			}
		}
	});
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
	vanillaAutocomplete('#FusionEquipeSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionEquipeSource").val(item.numero);
				jq("#FusionEquipeSource").val(item.libelle);
			}
		}
	});
	vanillaAutocomplete('#FusionEquipeCible', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionEquipeCible").val(item.numero);
				jq("#FusionEquipeCible").val(item.libelle);
			}
		}
	});
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
	vanillaAutocomplete('#DeplaceEquipeSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#numDeplaceEquipeSource").val(item.numero);
				jq("#DeplaceEquipeSource").val(item.label);
			}
		}
	});
	vanillaAutocomplete('#DeplaceEquipeCible', 'Autocompl_club2.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#numDeplaceEquipeCible").val(item.code);
				jq("#DeplaceEquipeCible").val(item.label);
			}
		}
	});
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
	vanillaAutocomplete('#ChangeCodeRecherche', 'Autocompl_compet2.php', {
		width: 550,
		maxResults: 30,
		minChars: 2,
		cacheLength: 0,
		dataType: 'json',
		extraParams: {
			saison: jq('#saisonTravail').val()
		},
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#changeCodeSource").val(item.code);
			}
		}
	});
	jq("#ChangeCodeBtn").click(function () {
		var changeCodeSource = jq("#changeCodeSource").val()
		var changeCodeCible = jq("#changeCodeCible").val()
		var seasonText = document.getElementById("changeCodeAllSeason").checked ? 'TOUTES LES SAISONS' : 'LA SAISON EN COURS' 
		if (!confirm(`Confirmez-vous le changement de code pour ${seasonText} : ${changeCodeSource} => ${changeCodeCible}  ?`)) {
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