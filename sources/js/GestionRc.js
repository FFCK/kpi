jq = jQuery.noConflict();

var langue = [];

if (lang == 'en')  {
    langue['Confirmer_MAJ'] = 'Confirm update ?';
    langue['Nom_rc_vide'] = 'Event name is empty, unable to create';
    langue['Confirmer'] = 'Confirm ?';
} else {
    langue['Confirmer_MAJ'] = 'Confirmez-vous le changement ?';
    langue['Nom_rc_vide'] = 'Le Nom de l\'événement est vide, ajout impossible';
    langue['Confirmer'] = 'Confirmez-vous ?';
}

function addRc()
{
	if (!validRc())
		return;

	document.forms['formRc'].elements['Cmd'].value = 'Add';
	document.forms['formRc'].elements['ParamCmd'].value = '';

	document.forms['formRc'].submit();
}
			
function validRc()
{
		var Matric = document.forms['formRc'].elements['Matric'].value;
		if (Matric.length == 0 && Matric != 0) {
			alert(langue['Nom_rc_vide']);
			return false;
		}
		return true;
}


function updateRc()
{
	if (!validRc())
		return;
						
	document.forms['formRc'].elements['Cmd'].value = 'UpdateRc';
	document.forms['formRc'].elements['ParamCmd'].value = '';
	document.forms['formRc'].submit();
}

function razRc()
{
	document.forms['formRc'].elements['Cmd'].value = 'RazRc';
	document.forms['formRc'].elements['ParamCmd'].value = '';
	document.forms['formRc'].submit();
}


function paramRc(idRc)
{
	document.forms['formRc'].elements['Cmd'].value = 'ParamRc';
	document.forms['formRc'].elements['ParamCmd'].value = idRc;
	document.forms['formRc'].submit();
}

function sessionSaison()
{
	if(!confirm(langue['Confirmer']))
	{
		document.forms['formRc'].reset;
		return;
	} else {
		document.forms['formRc'].elements['Cmd'].value = 'SessionSaison';
		document.forms['formRc'].elements['ParamCmd'].value = document.forms['formRc'].elements['saisonTravail'].value;
		document.forms['formRc'].submit();
	}
}

function changeAffiche()
{
	document.forms['formRc'].elements['Cmd'].value = '';
	document.forms['formRc'].elements['ParamCmd'].value = '';
	document.forms['formRc'].submit();
}

jq(document).ready(function() {
    
	// Maskedinput
	jq('#Ordre').mask("9");

    jq("#Nom").autocomplete('Autocompl_joueur3.php', {
		width: 420,
		max: 80,
		mustMatch: true,
		minChars: 2,
		cacheLength: 0,
		scrollHeight: 320,
	});
	jq("#Nom").result(function(event, data, formatted) {
		if (data) {
			var nom = data[3]+' '+data[2];
			jq("#NomSelectionne").text(nom);
			jq("#Matric").val(data[1]);
		}
	});

	jq('#filtreCompetition').change(function(){
		var compet = jq(this).val();
		if (compet == '') {
			jq('#tableRC tbody tr').show();
		} else {
			jq('#tableRC tbody tr').hide();
			jq('#tableRC tbody tr[data-code="'+compet+'"]').show();
		}
	});
	if (jq('#filtreCompetition').val() != '') {
		jq('#filtreCompetition').change();
	}
});