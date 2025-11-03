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
	vanillaAutocomplete('#choixJoueur', 'Autocompl_joueur.php', {
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
				jq("#rechercheAthlete").attr("href", "GestionAthlete.php?Athlete=" + item.matric);
			}
		}
	});
	

});

