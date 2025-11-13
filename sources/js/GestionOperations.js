jq = jQuery.noConflict()

var langue = []

if (lang == 'en') {
	langue['Cliquez_pour_modifier'] = 'Click to edit'
} else {
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
}

function ExportEvt () {
	jq("#ParamCmd").val(jq('#evenementExport').val())
	jq("#Cmd").val('ExportEvt')
	jq("#formOperations").submit()
}

function ImportEvt () {
	jq("#ParamCmd").val(jq('#evenementImport').val())
	jq("#Cmd").val('ImportEvt')
	jq("#formOperations").submit()
}

function changeAuthSaison () {
	document.forms['formOperations'].elements['Cmd'].value = 'ChangeAuthSaison'
	document.forms['formOperations'].elements['ParamCmd'].value = ''
	document.forms['formOperations'].submit()
}

function AddSaison () {
	if (!confirm(langue['Confirmer'])) {
		return
	}
	else {
		document.forms['formOperations'].elements['Cmd'].value = 'AddSaison'
		document.forms['formOperations'].elements['ParamCmd'].value = ''
		document.forms['formOperations'].submit()
	}
}

function activeSaison () {
	if (!confirm(langue['Confirmer'])) {
		document.forms['formOperations'].reset
		return
	} else if (!confirm(langue['Confirmer'])) {
		document.forms['formOperations'].reset
		return
	} else {
		document.forms['formOperations'].elements['Cmd'].value = 'ActiveSaison'
		document.forms['formOperations'].elements['ParamCmd'].value = document.forms['formOperations'].elements['saisonActive'].value
		document.forms['formOperations'].submit()
	}
}

jq(document).ready(function () {

	// Add language strings
	if (!langue) {
		langue = []
	}
	if (lang == 'en') {
		langue['Confirmer'] = 'Confirm ?'
	} else {
		langue['Confirmer'] = 'Confirmez-vous ?'
	}

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
		document.forms['formOperations'].elements['Cmd'].value = 'FusionJoueurs'
		document.forms['formOperations'].submit()
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
		document.forms['formOperations'].elements['Cmd'].value = 'RenomEquipe'
		document.forms['formOperations'].submit()
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
		jq('#formOperations').submit()
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
		jq('#formOperations').submit()
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
		jq('#formOperations').submit()
	})

})

