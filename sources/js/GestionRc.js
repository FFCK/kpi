jq = jQuery.noConflict();

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

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

function CopyRc()
{
	var saisonSource = jq('#saisonSourceRc').val();
	var saisonCible = jq('#saisonCibleRc').val();

	if (!saisonSource || !saisonCible) {
		alert('Veuillez sélectionner une saison source et une saison cible.');
		return;
	}

	if (saisonSource == saisonCible) {
		alert('Les saisons source et cible doivent être différentes.');
		return;
	}

	if (!confirm('Confirmez-vous la copie des RC de la saison ' + saisonSource + ' vers la saison ' + saisonCible + ' ?')) {
		return;
	}

	document.forms['formRc'].elements['Cmd'].value = 'CopyRc';
	document.forms['formRc'].elements['ParamCmd'].value = '';
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

    vanillaAutocomplete('#Nom', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom;
				jq("#NomSelectionne").text(nom);
				jq("#Matric").val(item.matric);
			}
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