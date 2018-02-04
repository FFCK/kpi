jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Confirmer_MAJ'] = 'Confirm update ?';
    langue['Nom_evt_vide'] = 'Event name is empty, unable to create';
} else {
    langue['Confirmer_MAJ'] = 'Confirmez-vous le changement ?';
    langue['Nom_evt_vide'] = 'Le Nom de l\'événement est vide, ajout impossible';
}

function validEvenement()
{
		var libelle = document.forms['formEvenement'].elements['Libelle'].value;
		if (libelle.length == 0)
		{
			alert(langue['Nom_evt_vide']);
			return false;
		}
		
		return true;
}

function addEvt()
{
	if (!validEvenement())
		return;

	document.forms['formEvenement'].elements['Cmd'].value = 'Add';
document.forms['formEvenement'].elements['ParamCmd'].value = '';

document.forms['formEvenement'].submit();
}
		
function publiEvt(idEvt, pub)
{
	if(!confirm(langue['Confirmer_MAJ']))
	{
		return false;
	}
		
	document.forms['formEvenement'].elements['Cmd'].value = 'PubliEvt';
	document.forms['formEvenement'].elements['ParamCmd'].value = idEvt;
	document.forms['formEvenement'].elements['Pub'].value = pub;
	document.forms['formEvenement'].submit();
}
	
function validEvt()
{
		var libelle = document.forms['formEvenement'].elements['Libelle'].value;
		if (libelle.length == 0)
		{
			alert(langue['Nom_evt_vide']);
			return false;
		}
		return true;
}


function updateEvt()
{
	if (!validEvt())
		return;
						
	document.forms['formEvenement'].elements['Cmd'].value = 'UpdateEvt';
	document.forms['formEvenement'].elements['ParamCmd'].value = '';
	document.forms['formEvenement'].submit();
}

function razEvt()
{
	document.forms['formEvenement'].elements['Cmd'].value = 'RazEvt';
	document.forms['formEvenement'].elements['ParamCmd'].value = '';
	document.forms['formEvenement'].submit();
}


function paramEvt(idEvt)
{
	document.forms['formEvenement'].elements['Cmd'].value = 'ParamEvt';
	document.forms['formEvenement'].elements['ParamCmd'].value = idEvt;
	document.forms['formEvenement'].submit();
}
	
jq(document).ready(function() {
    
	// Maskedinput
	//jq.mask.definitions['h'] = "[A-O]";
	jq('.dpt').mask("?***");
	jq('.date').mask("99/99/9999");
	//jq("#inputZone").mask("9");

});