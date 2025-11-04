jq = jQuery.noConflict();

function Add()
{

	document.forms['formAthlete'].elements['Cmd'].value = 'Add';
	document.forms['formAthlete'].elements['ParamCmd'].value = '';
	document.forms['formAthlete'].submit();
}


// ****************************************************************************************************

jq(document).ready(function() { //Jquery + NoConflict='J'
	//jq('#TableauDataTable').dataTable();
	//Recherches athletes
	jq('#iframeRechercheLicenceIndi2').hide();
	jq('#rechercheAthlete').click(function(e){
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=Athlete&zoneIdentite=choixJoueur');
		jq('#iframeRechercheLicenceIndi2').toggle();
	});

	vanillaAutocomplete('#choixJoueur', 'Autocompl_joueur3.php', {
		width: 550,
		maxResults: 50,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#Athlete").val(item.matric);
				jq("#choixJoueur").val(item.value);
			}
		}
	});

	jq('#update_button').click(function(e){
        e.preventDefault();
        if(confirm('Modifier cet athlète ?')) {
            jq('#Cmd').val('Update');
            jq('#formAthlete').submit();
        }
    });

    //Fusion joueurs (only if elements exist)
	if (jq('#FusionSource').length) {
		vanillaAutocomplete('#FusionSource', 'Autocompl_joueur.php', {
			width: 550,
			maxResults: 50,
			dataType: 'json',
			extraParams: {
				format: 'json'
			},
			formatItem: (item) => item.label,
			formatResult: (item) => item.value,
			onSelect: function(item) {
				if (item) {
					jq("#numFusionSource").val(item.matric);
					jq("#FusionSource").val(item.value);
				}
			}
		});
	}
	if (jq('#FusionCible').length) {
		vanillaAutocomplete('#FusionCible', 'Autocompl_joueur.php', {
			width: 550,
			maxResults: 50,
			dataType: 'json',
			extraParams: {
				format: 'json'
			},
			formatItem: (item) => item.label,
			formatResult: (item) => item.value,
			onSelect: function(item) {
				if (item) {
					jq("#numFusionCible").val(item.matric);
					jq("#FusionCible").val(item.value);
				}
			}
		});
	}
	jq("#FusionJoueurs").click(function() {
		var fusSource = jq("#FusionSource").val();
		var fusCible = jq("#FusionCible").val();
		if(!confirm('Confirmez-vous la fusion : '+fusSource+' => '+fusCible+' ?'))
		{
			return false;
		}
		jq('#Cmd').val('FusionJoueurs');
		jq('#formAthlete').submit();
	});

    //Changement club (only if element exists)
	if (jq('#update_club').length) {
		vanillaAutocomplete('#update_club', 'Autocompl_club2.php', {
			width: 550,
			maxResults: 50,
			dataType: 'json',
			extraParams: {
				format: 'json'
			},
			formatItem: (item) => item.label,
			formatResult: (item) => item.libelle,
			onSelect: function(item) {
				if (item) {
					jq("#update_club").val(item.code);
					jq("#update_cd").val(item.codeComiteDep);
					jq("#update_cr").val(item.codeComiteReg);
				}
			}
		});
	}
});


