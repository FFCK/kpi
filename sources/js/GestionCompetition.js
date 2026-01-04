jq = jQuery.noConflict()

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

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

				// Remplir les champs MULTI si la compétition est de type MULTI
				if (item.codeTypeclt === 'MULTI') {
					if (item.pointsGrid) {
						jq("#pointsGrid").val(item.pointsGrid);
					}
					if (item.rankingStructureType) {
						jq("#rankingStructureType").val(item.rankingStructureType);
					}
					if (item.multiCompetitions) {
						jq("#multiCompetitionsHidden").val(item.multiCompetitions);
					}
				}

				// Appeler changeCodeTypeClt() pour afficher/masquer les champs selon le type
				if (typeof changeCodeTypeClt === 'function') {
					changeCodeTypeClt();
				}
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

	// Attacher l'event listener au champ codeTypeClt pour changer dynamiquement (vanilla JS)
	var codeTypeCltField = document.getElementById('codeTypeClt');
	if (codeTypeCltField) {
		codeTypeCltField.addEventListener('change', function() {
			if (typeof changeCodeTypeClt === 'function') {
				changeCodeTypeClt();
			}
		});
	}

	// Initialisation au chargement de la page
	changeCodeTypeClt();
	initMultiCompetitionsSync();
	initPointsGrid();

})

// ========== Fonctions pour la gestion des compétitions MULTI ==========

// Fonction pour afficher/masquer les champs MULTI selon le type de compétition
function changeCodeTypeClt() {
	var typeCompet = document.getElementById('codeTypeClt');
	var pointsGridRow = document.getElementById('pointsGridRow');
	var rankingStructureTypeRow = document.getElementById('rankingStructureTypeRow');
	var multiCompetitionsRow = document.getElementById('multiCompetitionsRow');

	if (typeCompet && pointsGridRow && rankingStructureTypeRow && multiCompetitionsRow) {
		if (typeCompet.value === 'MULTI') {
			pointsGridRow.style.display = 'table-row';
			rankingStructureTypeRow.style.display = 'table-row';
			multiCompetitionsRow.style.display = 'table-row';
		} else {
			pointsGridRow.style.display = 'none';
			rankingStructureTypeRow.style.display = 'none';
			multiCompetitionsRow.style.display = 'none';
		}
	}
}

// Fonction pour synchroniser le select avec le champ hidden
function syncMultiCompetitions() {
	var select = document.getElementById('multiCompetitionsSelect');
	var hidden = document.getElementById('multiCompetitionsHidden');

	if (select && hidden) {
		var selected = [];
		for (var i = 0; i < select.options.length; i++) {
			if (select.options[i].selected) {
				selected.push(select.options[i].value);
			}
		}
		// Stocker la liste en JSON
		hidden.value = JSON.stringify(selected);
	}
}

// Fonction pour convertir le select multiple en JSON avant la soumission
function updateCompet() {
	// S'assurer que le champ hidden est à jour
	syncMultiCompetitions();

	// Appeler la fonction originale updateCompet si elle existe
	if (typeof window.originalUpdateCompet === 'function') {
		window.originalUpdateCompet();
	} else {
		document.getElementById('Cmd').value = 'UpdateCompet';
		document.forms['formCompet'].submit();
	}
}

// Attacher l'event listener sur le select pour synchroniser automatiquement
function initMultiCompetitionsSync() {
	var select = document.getElementById('multiCompetitionsSelect');
	if (select) {
		select.addEventListener('change', syncMultiCompetitions);
		// Synchroniser immédiatement au chargement
		syncMultiCompetitions();
	}
}

// Fonction pour ouvrir l'éditeur de grille de points
function openGridEditor() {
	var pointsGrid = document.getElementById('pointsGrid');
	var currentJson = pointsGrid ? pointsGrid.value : '';

	// Ouvrir la page dans une nouvelle fenêtre
	var url = 'GestionGrillePoints.php';
	if (currentJson) {
		url += '?pointsGrid=' + encodeURIComponent(currentJson);
	}

	window.open(url, 'GridEditor', 'width=900,height=700,scrollbars=yes,resizable=yes');
}

// Fonction pour initialiser le champ pointsGrid depuis data-json-value
function initPointsGrid() {
	var input = document.getElementById('pointsGrid');
	if (input && input.hasAttribute('data-json-value')) {
		var jsonValue = input.getAttribute('data-json-value');
		if (jsonValue) {
			// Décoder les entités HTML et définir la valeur
			var textarea = document.createElement('textarea');
			textarea.innerHTML = jsonValue;
			input.value = textarea.value;
		}
	}
}


if (top.location != self.document.location) {
	alert('Vous quittez le site parent !')
	top.location = self.document.location
}