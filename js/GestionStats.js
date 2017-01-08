jq = jQuery.noConflict();

jq(document).ready(function() { //Jquery + NoConflict='J'

	// Highlight
    jq('#reach').bind('keyup change', function(ev) {
        // pull in the new value
        var searchTerm = jq(this).val();
        // remove any old highlighted terms
        jq('.tableau').removeHighlight();
        // disable highlighting if empty
        if ( searchTerm ) {
            // highlight the new term
            jq('.tableau').highlight( searchTerm );
			jq('.tableau thead').removeHighlight();
        }
    });

	//Recherches athletes
	//jq('#iframeRechercheLicenceIndi2').hide();
	/*
	jq('#rechercheAthlete').click(function(e){
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=Athlete&zoneIdentite=choixJoueur');
		jq('#iframeRechercheLicenceIndi2').toggle();
	});
	*/
	jq("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			jq("#Athlete").val(data[1]);
			var athl = data[1];
			jq("#choixJoueur").val(data[0]);
			jq("#rechercheAthlete").attr( "href", "GestionAthlete.php?Athlete="+athl );
		}
	});
	

});

