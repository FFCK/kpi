jq = jQuery.noConflict();

function validEvenement()
{
		var libelle = document.forms['formEvenement'].elements['Libelle'].value;
		if (libelle.length == 0)
		{
			alert("Le Libellé de l'Evénement est Vide ..., Ajout Impossible !");
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
	if(!confirm('Confirmez-vous le changement ?'))
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
			alert("Le libellé est Vide ..., Ajout Impossible !");
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