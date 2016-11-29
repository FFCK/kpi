jq = jQuery.noConflict();
/*
/*
 * Reprise de la fonction ajax saveOfficiel (officiels matchs)
 * A adapter pour sauvegarde des officiels de journée
 */
jq(document).ready(function() {
//    /* OFFICIELS */
//    jq('.editOfficiel').editable('ajax/saveOfficielJournee.php', {
//        style   : 'display: inline',
//        submit  : 'OK',
//        cssclass : 'autocompleteOfficiel',
//        indicator : '<img src="images/indicator.gif">',
//        /* TODO: adapter pour officiels journee */
//        submitdata : {idJournee: 2}, 
//        type      : 'autocomplete',
//        placeholder : '<i class="placehold">Cliquez pour modifier...</i>', 
//        //tooltip   : "Clic pour modifier",
//        //onblur    : "submit",
//        autocomplete : { //parametres transmis au plugin autocomplete
//            minLength  : 2,
//            delay: 200,
//            source     : '../Autocompl_joueur2.php'
//        }
//    });

	jq("#Representant").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Representant").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7] + ' ' + data[6] + ' (' + data[12] + ')';
			jq("#Representant").val(nom);
		}
	});
});


