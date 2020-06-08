jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Annuler'] = 'Cancel';
    langue['Arbitre_non_identifie'] = 'Unidentified referee';
    langue['Cliquez_pour_modifier'] = 'Click to edit';
    langue['Compet'] = 'Competition';
    langue['Confirm_affect'] = 'You should have recalculate group ranking first, confirm auto assignment ?';
    langue['Confirm_delete'] = 'Delete teams & referees from selected games ?';
    langue['Confirm_update'] = 'Confirm update ?';
    langue['Confirmer_MAJ'] = 'Confirm composition update ?';
    langue['Date_vide'] = 'Date is empty, unable to create !';
    langue['Equipe'] = 'Team';
    langue['Equipe_non_definie'] = 'Unidentified team';
    langue['Heure_invalide'] = 'Time is invalid (format hh:mm), continue anymore ?';
    langue['InitTitu'] = 'Delete all players and re-assign team rosters\n(excluding X-Unavailables and et A-Referees)\nfor unlocked games of :\n';
    langue['Journee'] = 'matchday / phase / group';
    langue['MAJ_impossible'] = 'Unable to update';
    langue['Match_de_classement'] = 'Classifying game';
    langue['Match_eliminatoire'] = 'Playoffs';
    langue['Non_valide'] = 'Unvalidated (private score)';
    langue['Select_journee'] = 'Select a matchday / phase / group.';
    langue['Selection_journee'] = 'Select a matchday / phase / group first, unable to create !';
    langue['Selection_competition'] = 'Select a competition !';
    langue['Selection_equipe'] = 'Select a team !';
    langue['Valider'] = 'Valid';
    langue['Valide'] = 'Validated, locked (public score)';
    langue['Vider'] = 'Empty';
} else {
    langue['Annuler'] = 'Annuler';
    langue['Arbitre_non_identifie'] = 'Arbitre non identifié';
    langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier';
    langue['Compet'] = 'Compétition';
    langue['Confirm_affect'] = 'Vous devez avoir recalculé le classement, Confirmer l\affectation automatique ?';
    langue['Confirm_delete'] = 'Supprimer les équipes et arbitres des matchs sélectionnés ?';
    langue['Confirm_update'] = 'Confirmer le changement ?';
    langue['Confirmer_MAJ'] = 'Confirmez-vous la mise à jour des feuilles de matchs ?';
    langue['Date_vide'] = 'Date vide, ajout impossible !';
    langue['Equipe'] = 'Equipe';
    langue['Equipe_non_definie'] = 'Equipe non définie';
    langue['Heure_invalide'] = 'Heure invalide (format hh:mm), continuer ?';
    langue['InitTitu'] = 'Supprimer tous les joueurs et ré-affecter\nles joueurs présents (sauf X-Inactifs et A-Arbitres)\npour les matchs non verrouillés de :\n';
    langue['Journee'] = 'journée / phase / poule';
    langue['MAJ_impossible'] = 'Mise à jour impossible';
    langue['Match_de_classement'] = 'Match de classement';
    langue['Match_eliminatoire'] = 'Match éliminatoire';
    langue['Non_valide'] = 'Non validé (score non public)';
    langue['Select_journee'] = 'Sélectionner une journée / phase / poule.';
    langue['Selection_journee'] = 'Sélectionner une journée / phase / poule, ajout impossible !';
    langue['Selection_competition'] = 'Sélectionner une compétition !';
    langue['Selection_equipe'] = 'Sélectionner une équipe !';
    langue['Valider'] = 'Valider';
    langue['Valide'] = 'Validé / verrouillé (score public)';
    langue['Vider'] = 'Vider';
}
jq(document).ready(function() {
	jq("#Representant").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 0,
		scrollHeight: 320,
	});
	jq("#Representant").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7] + ' ' + data[6] + ' (' + data[12] + ')';
			jq("#Representant").val(nom);
		}
	});

	jq('.directInput').attr('title',langue['Cliquez_pour_modifier']);

	jq("body").delegate("span.directInput", "click", function(event){
		event.preventDefault();
        var valeur = jq(this).text();
        var typeChamps = jq(this).attr('data-target');
		if (jq(this).hasClass('arbitre')) {
			jq(this).before('<input type="text" id="inputZone2" class="directInputSpan" size="22" value="'+valeur+'">');
			jq(this).before('<br>\n\
							<input type="button" id="inputZone2valid" value="' + langue['Valider'] + '">\n\
							<input type="button" id="inputZone2annul" value="' + langue['Annuler'] + '">\n\
							<input type="button" id="inputZone2vid" value="' + langue['Vider'] + '">');
			dataid = jq(this).attr('data-id');
			jq("#inputZone2valid").attr('data-id', dataid);
			jq("#inputZone2valid").attr('data-value', '');
			jq("#inputZone2vid").attr('data-target', typeChamps);
			// AUTOCOMPLETE ARBITRES
			jq("#inputZone2").autocomplete('Autocompl_joueur3.php', {
				width: 320,
				max: 80,
				mustMatch: false,
				minChars: 2,
				cacheLength: 0,
				scrollHeight: 320,
			});
			jq("#inputZone2").result(function(event, data, formatted) {
				if (data) {
					if(typeof(data[1]) == 'undefined' || data[1] == 'XXX') {
						jq("#inputZone2valid").attr('data-id', dataid);
						jq("#inputZone2valid").attr('data-target', typeChamps);
						jq("#inputZone2valid").attr('data-value', '');
					} else {
						var nomArb = data[3]+' '+data[2]+' ('+data[1]+')';
						jq("#inputZone2valid").attr('data-id', dataid);
						jq("#inputZone2valid").attr('data-target', typeChamps);
						jq("#inputZone2valid").attr('data-value', nomArb);
						jq("#inputZone2").val(nomArb);
					}
				}
			});
		}
        jq(this).hide();
		setTimeout( function() { 
			jq('#inputZone').select();
		}, 0 );
    });
    
    jq('#inputZone').live('keydown',function(e){
		if(e.which == 13) {
			jq(this).blur();
			return false;
		}
	}); 
    
    jq('#inputZone').live('blur', function(){
        var thisSpan = jq('#inputZone + span:first');
        var nouvelleValeur = jq(this).val();
        var typeChamps = jq(this).attr('type');
        var valeurEntier = nouvelleValeur | 0;
        if (typeChamps == 'tel' && valeurEntier < 1){
            jq(this).focus().css('border', '1px solid red');

		} else {
            if (nouvelleValeur != jq(this).attr('data-anciennevaleur')){
                var AjaxWhere = jq('#AjaxWhere').val();
                var AjaxTableName = jq('#AjaxTableName').val();
                var AjaxAnd = '';
                var AjaxUser = jq('#AjaxUser').val();
                var numJournee = thisSpan.attr('data-id');
                var typeValeur = thisSpan.attr('data-target');
                jq.get("UpdateCellJQ.php",
                    {
                        AjTableName: AjaxTableName,
                        AjWhere: AjaxWhere,
                        AjTypeValeur: typeValeur,
                        AjValeur: nouvelleValeur,
                        AjId: numJournee,
                        AjId2: '',
                        AjUser: AjaxUser,
                        AjOk: 'OK'
                    },
                    function(data){
                        if(data != 'OK!'){
                            alert(langue['MAJ_impossible'] + ' : ' + data);
                        }else{
                            thisSpan.text(nouvelleValeur);
                        }
                    }
                );
            }
            thisSpan.show();
            jq(this).remove();
        }
	});

	jq('#inputZone2annul').live('click', function(event){
		event.preventDefault;
		jq('#inputZone2vid ~ span').show();
		jq('#inputZone2 + br').remove();
		jq('#inputZone2').remove();
		jq('#inputZone2valid').remove();
		jq('#inputZone2annul').remove();
		jq('#inputZone2vid').remove();
	});
	jq('#inputZone2valid').live('click', function(event){
		event.preventDefault;
		if (jq(this).attr('data-value') != '') {
            lavaleur = jq(this).attr('data-value');
        } else {
            lavaleur = jq('#inputZone2').val();
        }
		AjaxTableName = jq('#AjaxTableName').val();
		AjaxWhere = jq('#AjaxWhere').val();
		AjaxUser = jq('#AjaxUser').val();
		lid = jq(this).attr('data-id');
		latarget = jq(this).attr('data-target');
		jq.get("UpdateCellJQ.php",
		{
			AjTableName: AjaxTableName,
			AjWhere: AjaxWhere,
			AjTypeValeur: latarget,
			AjValeur: lavaleur,
			AjId: lid,
			AjId2: '',
			AjUser: AjaxUser,
			AjOk: 'OK'
		},
		function(data){
			if (data != 'OK!') {
				alert(langue['MAJ_impossible'] + ' : ' + data);
			} else {
				jq('#inputZone2vid ~ span:first').html(lavaleur);
				jq('#inputZone2vid ~ span:first').show();
				jq('#inputZone2 + br').remove();
				jq('#inputZone2').remove();
				jq('#inputZone2valid').remove();
				jq('#inputZone2annul').remove();
				jq('#inputZone2vid').remove();
				if (latarget == 'Responsable_R1') {
					jq('#Responsable_R1b').html(lavaleur);
				}
				if (latarget == 'Delegue') {
					jq('#Delegueb').html(lavaleur);
				}
			}
		});
	});

	jq('#inputZone2vid').live('click', function(event){
		event.preventDefault;
		AjaxTableName = jq('#AjaxTableName').val();
		AjaxWhere = jq('#AjaxWhere').val();
		AjaxUser = jq('#AjaxUser').val();
		lavaleur = '';
		lid = dataid;
		latarget = jq(this).attr('data-target');
		jq.get("UpdateCellJQ.php",
		{
			AjTableName: AjaxTableName,
			AjWhere: AjaxWhere,
			AjTypeValeur: latarget,
			AjValeur: lavaleur,
			AjId: lid,
			AjId2: '',
			AjUser: AjaxUser,
			AjOk: 'OK'
		},
		function(data){
			if (data != 'OK!') {
				alert(langue['MAJ_impossible'] + ' : ' + data);
			} else {
				jq('#inputZone2vid ~ span:first').html(lavaleur);
				jq('#inputZone2vid ~ span:first').show();
				jq('#inputZone2 + br').remove();
				jq('#inputZone2').remove();
				jq('#inputZone2valid').remove();
				jq('#inputZone2annul').remove();
				jq('#inputZone2vid').remove();
				if (latarget == 'Responsable_R1') {
					jq('#Responsable_R1b').html(lavaleur);
				}
				if (latarget == 'Delegue') {
					jq('#Delegueb').html(lavaleur);
				}
			}
		});

	});
});

