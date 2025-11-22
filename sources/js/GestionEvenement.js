jq = jQuery.noConflict()

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

function validEvenement () {
	var libelle = document.forms['formEvenement'].elements['Libelle'].value
	if (libelle.length == 0) {
		alert(langue['Nom_evt_vide'])
		return false
	}

	return true
}

function addEvt () {
	if (!validEvenement())
		return

	document.forms['formEvenement'].elements['Cmd'].value = 'Add'
	document.forms['formEvenement'].elements['ParamCmd'].value = ''

	document.forms['formEvenement'].submit()
}

function publiEvt (idEvt, pub) {
	if (!confirm(langue['Confirmer_MAJ'])) {
		return false
	}

	document.forms['formEvenement'].elements['Cmd'].value = 'PubliEvt'
	document.forms['formEvenement'].elements['ParamCmd'].value = idEvt
	document.forms['formEvenement'].elements['Pub'].value = pub
	document.forms['formEvenement'].submit()
}

function appEvt (idEvt, app) {
	if (!confirm(langue['Confirmer_MAJ'])) {
		return false
	}

	document.forms['formEvenement'].elements['Cmd'].value = 'AppEvt'
	document.forms['formEvenement'].elements['ParamCmd'].value = idEvt
	document.forms['formEvenement'].elements['App'].value = app
	document.forms['formEvenement'].submit()
}

function validEvt () {
	var libelle = document.forms['formEvenement'].elements['Libelle'].value
	if (libelle.length == 0) {
		alert(langue['Nom_evt_vide'])
		return false
	}
	return true
}


function updateEvt () {
	if (!validEvt())
		return

	document.forms['formEvenement'].elements['Cmd'].value = 'UpdateEvt'
	document.forms['formEvenement'].elements['ParamCmd'].value = ''
	document.forms['formEvenement'].submit()
}

function razEvt () {
	document.forms['formEvenement'].elements['Cmd'].value = 'RazEvt'
	document.forms['formEvenement'].elements['ParamCmd'].value = ''
	document.forms['formEvenement'].submit()
}


function paramEvt (idEvt) {
	document.forms['formEvenement'].elements['Cmd'].value = 'ParamEvt'
	document.forms['formEvenement'].elements['ParamCmd'].value = idEvt
	document.forms['formEvenement'].submit()
}
