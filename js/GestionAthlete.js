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
	
	
	
});


