
function Add()
{
						
	document.forms['formAthlete'].elements['Cmd'].value = 'Add';
	document.forms['formAthlete'].elements['ParamCmd'].value = '';
	document.forms['formAthlete'].submit();
}


// ****************************************************************************************************

$(document).ready(function() { //Jquery + NoConflict='J'
	//$('#TableauDataTable').dataTable();
	//Recherches athletes
	$('#iframeRechercheLicenceIndi2').hide();
	$('#rechercheAthlete').click(function(e){
		$('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=Athlete&zoneIdentite=choixJoueur');
		$('#iframeRechercheLicenceIndi2').toggle();
	});

	$("#choixJoueur").autocomplete('Autocompl_joueur3.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	$("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			$("#Athlete").val(data[1]);
			$("#choixJoueur").val(data[0]);
		}
	});
	
	
	
});


