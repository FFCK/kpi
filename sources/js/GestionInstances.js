jq = jQuery.noConflict();

// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

jq(document).ready(function() {
	vanillaAutocomplete('#Representant', 'Autocompl_joueur.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		cacheLength: 0,
		scrollHeight: 320,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.nom + ' ' + item.prenom + ' (' + item.clubLibelle + ')';
				jq("#Representant").val(nom);
			}
		}
	});

	jq('.directInput').attr('title',langue['Cliquez_pour_modifier']);

	jq("body").delegate("span.directInput", "click", function(event){
		event.preventDefault();
        var valeur = jq(this).text();
        var typeChamps = jq(this).attr('data-target');
		if (jq(this).hasClass('arbitre')) {
			jq('#inputZone2annul').click();
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
			vanillaAutocomplete('#inputZone2', 'Autocompl_joueur3.php', {
				width: 320,
				maxResults: 80,
				minChars: 2,
				cacheLength: 0,
				scrollHeight: 320,
				dataType: 'json',
				extraParams: {
					format: 'json'
				},
				formatItem: (item) => item.label,
				formatResult: (item) => item.value,
				onSelect: function(item) {
					if (item) {
						if(typeof(item.matric) == 'undefined' || item.matric == 'XXX') {
							jq("#inputZone2valid").attr('data-id', dataid);
							jq("#inputZone2valid").attr('data-target', typeChamps);
							jq("#inputZone2valid").attr('data-value', '');
						} else {
							var nomArb = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
							jq("#inputZone2valid").attr('data-id', dataid);
							jq("#inputZone2valid").attr('data-target', typeChamps);
							jq("#inputZone2valid").attr('data-value', nomArb);
							jq("#inputZone2").val(nomArb);
						}
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

	jq('.rcpick').click(function(){
		var rc = jq(this).attr('title');
		jq('#inputZone2 + br').remove();
		jq('#inputZone2').remove();
		jq('#inputZone2valid').remove();
		jq('#inputZone2annul').remove();
		jq('#inputZone2vid').remove();

		jq('span[data-target="Responsable_insc"]').click();
		jq("#inputZone2valid").attr('data-id', dataid);
		jq("#inputZone2valid").attr('data-target', jq('span[data-target="Responsable_insc"]').attr('data-target'));
		jq("#inputZone2valid").attr('data-value', rc);
		jq("#inputZone2").val(rc);
	});
	

});

