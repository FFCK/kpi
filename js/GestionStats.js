$(document).ready(function() { //Jquery + NoConflict='J'

	// Highlight
    $('#reach').bind('keyup change', function(ev) {
        // pull in the new value
        var searchTerm = $(this).val();
        // remove any old highlighted terms
        $('.tableau').removeHighlight();
        // disable highlighting if empty
        if ( searchTerm ) {
            // highlight the new term
            $('.tableau').highlight( searchTerm );
			$('.tableau thead').removeHighlight();
        }
    });

	//Recherches athletes
	//$('#iframeRechercheLicenceIndi2').hide();
	/*
	$('#rechercheAthlete').click(function(e){
		$('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=Athlete&zoneIdentite=choixJoueur');
		$('#iframeRechercheLicenceIndi2').toggle();
	});
	*/
	$("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	$("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			$("#Athlete").val(data[1]);
			var athl = data[1];
			$("#choixJoueur").val(data[0]);
			$("#rechercheAthlete").attr( "href", "GestionAthlete.php?Athlete="+athl );
		}
	});
	

});

