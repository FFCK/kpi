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

	jq("#choixJoueur").autocomplete('Autocompl_joueur3.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			jq("#Athlete").val(data[1]);
			jq("#choixJoueur").val(data[0]);
		}
	});
	
	jq('#update_button').click(function(e){
        e.preventDefault();
        if(confirm('Modifier cet athlète ?')) {
            jq('#Cmd').val('Update');
            jq('#formAthlete').submit();
        }
    });
    
    //Fusion joueurs
	jq("#FusionSource").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#FusionSource").result(function(event, data, formatted) {
		if (data) {
			jq("#numFusionSource").val(data[1]);
			jq("#FusionSource").val(data[0]);
		}
	});
	jq("#FusionCible").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#FusionCible").result(function(event, data, formatted) {
		if (data) {
			jq("#numFusionCible").val(data[1]);
			jq("#FusionCible").val(data[0]);
		}
	});
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
	
    //Fusion joueurs
	jq("#update_club").autocomplete('Autocompl_club2.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#update_club").result(function(event, data, formatted) {
		if (data) {
			jq("#update_club").val(data[2]);
			jq("#update_cd").val(data[3]);
			jq("#update_cr").val(data[4]);
		}
	});
});


