/* 
 * Feuille de marque en ligne
 * Javascript partie C
 */

            $(function() {
                /* VALIDATION SCORE */
				$('#validScore').buttonset();
				/****************************************************/
				$('#validScore').click(function( event ){
					event.preventDefault();
					if(confirm(lang.Valider_score + $('#scoreA').text() + '-' + $('#scoreB').text() + ' ?')){
						$.post(
							'v2/StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : idMatch,
								Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
								TypeUpdate : 'ValidScore'
							},
							function(data){ // callback
								if(data == 'OK'){
									window.location = 'FeuilleMarque2.php?idMatch=' + idMatch;
								}
								else{
									custom_alert(lang.Action_impossible, lang.Attention);
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				/*****************************************************/
				
				/* OFFICIELS */
				$('.editOfficiel').editable('v2/saveOfficiel.php', {
					style   : 'display: inline',
					submit  : 'OK',
					cssclass : 'autocompleteOfficiel',
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: idMatch},
					type      : 'autocomplete',
					placeholder : '<i class="placehold">Cliquez pour modifier...</i>', 
					//tooltip   : "Clic pour modifier",
					//onblur    : "submit",
					autocomplete : { //parametres transmis au plugin autocomplete
						minLength  : 2,
						delay: 200,
						source     : 'Autocompl_joueur2.php'
					}
				});
				$('.editArbitres').editable('v2/saveArbitres.php', {
					style   : 'display: inline',
					submit  : 'OK',
					type      : 'catcomplete',
					placeholder : '<i class="placehold">Cliquez pour modifier...</i>', 
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: idMatch},
					//tooltip   : "Clic pour modifier",
					//onblur    : "submit",
					autocomplete : { //parametres transmis au plugin autocomplete
						minLength  : 2,
						delay: 200,
						source     : 'Autocompl_arb3.php?idMatch=' + idMatch,
						//select: function( event, ui ) {
						//	$( "#MatricTransmit" ).val( ui.item.matric );
						//	return false;
						//}
					},
				});
				// COMPO EQUIPES
				$('.editStatut').editable('v2/saveStatut.php', {
					data   : " {'-':'Joueur','C':'Capitaine','E':'Entraîneur'}",
					placeholder : '-', 
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: idMatch},
					type   : 'select',
					submit  : 'OK',
					callback : function(value, settings) {
						idjoueur = $(this).attr('id').split('-');
						console.log(idjoueur[1]);
						if(value == 'C')
							attrSatut = ' (Cap.)';
						else if(value == 'E')
							attrSatut = ' (Coach)';
						else
							attrSatut = '';
						$('.joueurs[data-id=' + idjoueur[1] + '] .StatutJoueur').text(attrSatut);
					}
				});
				$('.editNo').editable('v2/saveNo.php', {
					style   : 'display: inline',
					placeholder : '-', 
					submit  : 'OK',
					indicator : '<img src="images/indicator.gif">',
					submitdata : {idMatch: idMatch},
					type      : 'spinner',
					callback : function(value, settings) {
						idjoueur = $(this).attr('id').split('-');
						console.log(idjoueur[1]);
						$('.joueurs[data-id=' + idjoueur[1] + ']').attr('data-nb',value).find('.NumJoueur').text(value);
					}
				});
				// SUPPRESSION JOUEUR
				$('.suppression').click(function(){
					matricSupp = $(this).attr('id').split('-');
					$('div.simple_alert').remove();
					$("<div></div>").html(lang.Confirm_suppression_joueur + ' ' + matricSupp[2] + ' ' + lang.Equipe + ' ' + matricSupp[1] + ' ?').dialog({
						dialogClass:'simple_alert',
						title: lang.Suppression_joueur,
						resizable: false,
						modal: true,
						buttons: {
							"Ok": function() {
								$( this ).dialog( "close" );
								$.post(
									'v2/delJoueur.php', // Le fichier cible côté serveur.
									{
										Id_Match : idMatch,
										Matric : matricSupp[2],
										Equipe : matricSupp[1]
									},
									function(data){ // callback
										if(data == 'OK'){
											$('#No-'+matricSupp[2]).parent().remove();
											$('a.joueurs[data-id='+matricSupp[2]+']').remove();
											custom_alert(lang.Joueur_supprime + ' ' + matricSupp[2], lang.Attention);
										}
										else{
											custom_alert(lang.Action_impossible, lang.Attention);
										}
									},
									'text' // Format des données reçues.
								);
							},
							"Annuler/Dismiss": function() {
								$( this ).dialog( "close" );
								custom_alert(lang.Joueur_non_supprime, lang.Attention);
							}
						}
					});
				});
				// Réinitialisation des présents
				$('#initA').click(function(){
					$.post(
						'v2/initPresents.php', // Le fichier cible côté serveur.
						{
							idMatch : idMatch,
							codeEquipe : 'A',
							idEquipe : idEquipeA
						},
						function(data){ // callback
							if(data == 'OK'){
								window.location = 'FeuilleMarque2.php?idMatch=' + idMatch;
							} else {
								custom_alert(lang.Action_impossible, lang.Attention);
							}
						},
						'text' // Format des données reçues.
					);
				});
				$('#initB').click(function(){
					$.post(
						'v2/initPresents.php', // Le fichier cible côté serveur.
						{
							idMatch : idMatch,
							codeEquipe : 'B',
							idEquipe : idEquipeB
						},
						function(data){ // callback
							if(data == 'OK'){
								window.location = 'FeuilleMarque2.php?idMatch=' + idMatch;
							}
							else{
								custom_alert(lang.Action_impossible, lang.Attention);
							}
						},
						'text' // Format des données reçues.
					);
				});
				// COMMENTAIRES
				$('#comments').editable('v2/saveComments.php', {
					style   : 'display: inline',
					placeholder : lang.Cliquez_pour_modifier + '...', 
					type : 'textarea',
					indicator : '<img src="images/indicator.gif">',
					tooltip   : lang.Cliquez_pour_modifier,
					submitdata : {idMatch: idMatch},
					submit  : 'OK',
				});

				/* TYPE MATCH */
				$('#typeMatchElimination').click(function(){
					if(confirm(lang.Vainqueur_obligatoire_confirmez)){
						$.post(
							'v2/StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : idMatch,
								Valeur : 'E',
								TypeUpdate : 'Type'
							},
							function(data){ // callback
								if(data == 'OK'){
									typeMatch = 'E';
									$('#P1, #P2, #TB').show();
									$('#typeMatchImg').attr('src', '../img/typeE.png');
								}
								else{
									custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				$('#typeMatchClassement').click(function(){
					if(confirm(lang.Egalite_possible)){
						$.post(
							'v2/StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : idMatch,
								Valeur : 'C',
								TypeUpdate : 'Type'
							},
							function(data){ // callback
								if(data == 'OK'){
									typeMatch = 'C';
									$('#P1, #P2, #TB').hide();
									$('#typeMatchImg').attr('src', '../img/typeC.png');
								}
								else{
									custom_alert(lang.Action_impossible + '<br>' + data, lang.Attention);
								}
							},
							'text' // Format des données reçues.
						);
					}
				});
				/* STATUT */
				$('.statut').click(function( event ) {
					event.preventDefault();
					valeur = $(this).attr('id');
					$.post(
						'v2/StatutPeriode.php', // Le fichier cible côté serveur.
						{ // variables
							Id_Match : idMatch,
							Valeur : valeur,
							TypeUpdate : 'Statut'
						},
						function(data){  // callback
							if(data == 'OK'){
								$('.statut').removeClass('actif');
								$('#'+valeur).addClass('actif');
								statutActive(valeur, 'O');
								$('#reset_evt').click();
						//		valeur2 = $('.periode[class*="actif"]').attr('id');
								if(valeur == 'ON')// && valeur2 == ''
									$('#M1').click();
							}else{
								custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
							}
						},
						'text' // Format des données reçues.
					);
				});
				

                $( "#dialog_end_opener" ).click(function() {
					$('#periode_end').text(minut_max + ':' + second_max);
					$( "#dialog_end" ).dialog( "open" );
				});
				
				/* PERIODE */
				$('.periode').click(function( event ) {
					event.preventDefault();
					valeur = $(this).attr('id');
					if(	$('#update_evt').attr('data-id') == ''){
						periode_en_cours = valeur;
						$.post(
							'v2/StatutPeriode.php', // Le fichier cible côté serveur.
							{ // variables
								Id_Match : idMatch,
								Valeur : valeur,
								TypeUpdate : 'Periode'
							},
							function(data){  // callback
								if(data == 'OK'){
									$('.statut').removeClass('actif');
									$('#ON').addClass('actif');
									$('#end_match_time').removeClass('actif').addClass('inactif')
									$('.joueurs, .equipes, .evtButton, .chronoButton, .evtButton2').removeClass('inactif');
									$('.periode').removeClass('actif');
									$('#'+valeur).addClass('actif');
									switch (valeur) {
										case 'P1':
											texte = lang.period_P1 + ' : 5 minutes';
											minut_max = duree_prolongations;
											second_max = '00';
											break;
										case 'P2':
											texte = lang.period_P2 + ' : 5 minutes';
											minut_max = duree_prolongations;
											second_max = '00';
											break;
										case 'TB':
											texte = lang.period_TB;
											minut_max = '01';
											second_max = '00';
											break;
										case 'M2':
											texte = lang.period_M2 + ' : 10 minutes';
											minut_max = '10';
											second_max = '00';
											break;
										default:
											texte = lang.period_M1 + ' : 10 minutes';
											minut_max = '10';
											second_max = '00';
											break;
									}
									avertissement(texte);
//									$('#chrono_ajust').val($('#heure').val());
									$('#periode_ajust, #chrono_ajust').val(minut_max + ':' + second_max);
									$('#dialog_ajust_periode').html($('.periode[class*="actif"]').html());
									$( "#dialog_ajust" ).dialog( "open" );
								}else{
									custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
								}
							},
							'text' // Format des données reçues.
						);
					}else{
						$('.periode').removeClass('actif');
						$('#'+valeur).addClass('actif');
                        $('#time_evt').focus();
					}
				});

                $( "#dialog_ajust_opener, #heure" ).click(function() {
					$('#chrono_ajust').val($('#heure').val());
					$('#periode_ajust').val(minut_max + ':' + second_max);
					$('#dialog_ajust_periode').text($('.periode[class="actif"]').attr('id'));
					$( "#dialog_ajust" ).dialog( "open" );
				});
				/* BOUTONS MATCH */
				$('.joueurs, .equipes').click(function( event ) {
					event.preventDefault();
					$('.joueurs, .equipes').removeClass('actif');
					$(this).addClass('actif');
                    $('#time_evt').focus();
				});
				$('.evtButton').click(function( event ) {
					event.preventDefault();
					$('.evtButton').removeClass('actif');
					$(this).addClass('actif');
					if($('#update_evt').attr('data-id') == ''){
						$('#time_evt').val($('#heure').val());
					}
					$('#valid_evt').removeClass('inactif');
                    $('#time_evt').focus();
				});
                
                $('#time_evt').keypress(function(e){
                    if( e.which == 13 ){
                        $(this).focus().blur();
                        if($('#update_evt').attr('data-id') == ''){
                            $('#valid_evt').click();
                        } else {
                            $('#update_evt').click();
                        }
                    }
                });
                
				/* BUT = TEMPS MORT SYSTEMATIQUE */
                $('#evt_but').click(function( event ) {
                    if(arret_chrono_sur_but){
                        $('#stop_button').click();
                    }
                });
                        
				//var id_tr = 0;
				// INSERT 
				$('#valid_evt').click(function( event ) {
					event.preventDefault();
					if (theInEvent)
						return;
					
					theInEvent = true;
					//	enregistrement, ajout ligne...
                    //	$('#valid_evt').html('Enregistrement...');
					var texte;
					var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player');
					var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb');
					var ligne_num = ligne_nb + ' - ';
					var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id');
					var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe');
					var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt');
					if(ligne_evt === undefined) {
                        theInEvent = false;
                        return;
                    }
					var carton_equipe = 0;
					if(ligne_nom === undefined) {
						carton_equipe = 1;
						ligne_nom = $('.equipes[class*="actif"]').attr('data-player');
						ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe');
						ligne_num = '';
						ligne_id_joueur = '';
						if(ligne_nom === undefined) {
							custom_alert(lang.Selectionnez_equipe_joueur);
                            theInEvent = false;
							return;
						}
					}
					var code_ligne = $('.periode[class*="actif"]').attr('id');
					code_ligne += '-' + $('#time_evt').val();
					code_ligne += '-' + $('.evtButton[class*="actif"]').attr('data-code');
					code_ligne += '-' + ligne_equipe;
					code_ligne += '-' + ligne_id_joueur;
					code_ligne += '-' + ligne_nb;
					texte  = $('#time_evt').val() + ' ' + ligne_evt;
					texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom;
					/* BUT = TEMPS MORT OPTIONNEL ?  */
/*					if(ligne_evt == 'But') {
						$('div.simple_alert').remove();
						$("<div></div>").html('Temps mort ?').dialog({
							dialogClass:'simple_alert',
							title: 'But',
							resizable: false,
							modal: true,
							buttons: {
								"Oui": function() {
									$( this ).dialog( "close" );
									$('#stop_button').click();
								},
								"Non": function() {
									$( this ).dialog( "close" );
								},
							}
						});
					}
*/					
					$.post(
						'v2/evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : idMatch,
							ligne : code_ligne,
							idLigne : 0,
							type : 'insert'
						},
						function(data){ // callback
                            //			$('#valid_evt').html('Valider');
                            if($.isNumeric(data)){
                                avertissement(texte);
                                if(ligne_evt == 'Tir') {
                                    $('#nb_tirs_' + ligne_equipe).text(parseInt($('#nb_tirs_' + ligne_equipe).text()) + 1);
                                    $('.evtButton, .joueurs, .equipes').removeClass('actif');
                                    $('#valid_evt').addClass('inactif');
                                } else if(ligne_evt == 'Arret') {
                                    $('#nb_arrets_' + ligne_equipe).text(parseInt($('#nb_arrets_' + ligne_equipe).text()) + 1);
                                    $('.evtButton, .joueurs, .equipes').removeClass('actif');
                                    $('#valid_evt').addClass('inactif');
                                } else {
//                                    texteNom += ' (' + lang.Tir + ')';
                                    texteTR = '<tr id="ligne_' + data + '">';
                                    texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>';
                                    texteBut = '<td class="list_evt">';
                                    if(ligne_evt == 'But') {
                                        texteBut += '<img src="v2/but1.png" />';
                                        $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="v2/but1.png" />');
                                    }
                                    texteBut += '</td>';
                                    texteNom = '<td class="list_nom">' + ligne_num + ligne_nom ;
                                    
                                    texteNom += '</td>';
                                    texteVert = '<td class="list_evt">';
                                    if(ligne_evt == 'Carton vert') {
                                        texteVert += '<img src="v2/carton_vert.png" />';
                                        $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                        //si 2 verts...
                                        var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length;
                                        if(nb_cartons == 2) {
                                            custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_ce_joueur + '<br>' + lang.Verifier_type_faute, lang.Attention);
                                        }
                                        //si 3 verts...
                                        var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length;
                                        if(nb_cartons >= 3) {
                                            custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_vert.png" /> ' + lang.pour_ce_joueur + '<br />' + lang.Avertir_arbitre, lang.Attention);
                                        }
                                        // Carton d'équipe
                                    /*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
                                            $('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                            code_ligne += '-teamCard';
                                        }
                                        if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
                                            $('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                            code_ligne += '-teamCard';
                                        }
                                    */
                                    }
                                    texteVert += '</td>';
                                    texteJaune = '<td class="list_evt">';
                                    if(ligne_evt == 'Carton jaune') {
                                        texteJaune += '<img src="v2/carton_jaune.png" />';
                                        $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune.png" />');
                                        //si 2 jaunes...
                                        var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune.png"]').length;
                                        if(nb_cartons >= 2) {
                                            custom_alert(nb_cartons + ' <img class="c_carton" src="v2/carton_jaune.png" /> ' + lang.pour_ce_joueur, lang.Attention);
                                        }
                                    }
                                    texteJaune += '</td>';
                                    texteRouge = '<td class="list_evt">';
                                    if(ligne_evt == 'Carton rouge') {
                                        texteRouge += '<img src="v2/carton_rouge.png" />';
                                        $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_rouge.png" />');
                                    }
                                    texteRouge += '</td>';
                                    texteVide = '<td colspan="5"></td>';
                                    texteTR2 = '</tr>';
                                    $('.evtButton, .joueurs, .equipes').removeClass('actif');
                                    $('#valid_evt').addClass('inactif');
                                    if(ligne_equipe == 'A'){
                                        if(ligne_evt == 'But')
                                            $('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1);
                                        if(ordre_actuel == 'up'){
                                            $('#list').prepend(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2);
                                        }else{
                                            $('#list').append(texteTR + texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide + texteTR2);
                                        }
                                    }else{
                                        if(ligne_evt == 'But')
                                            $('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1);
                                        if(ordre_actuel == 'up'){
                                            $('#list').prepend(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2);
                                        }else{
                                            $('#list').append(texteTR + texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge + texteTR2);
                                        }
                                    }
                                    $('tr[id="ligne_' + data + '"]').attr('data-code', code_ligne);
                                    $.post(
                                        'v2/StatutPeriode.php', // Le fichier cible côté serveur.
                                        { // variables
                                            Id_Match : idMatch,
                                            Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
                                            TypeUpdate : 'ValidScoreDetail'
                                        },
                                        function(data){ },
                                        'text' // Format des données reçues.
                                    );
                                }
							}else{
								custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
							}
							theInEvent = false;

						},
						'text' // Format des données reçues.
					);

				});
				// RESET
				$('#reset_evt').click(function( event ) {
					event.preventDefault();
					$('.evtButton, .joueurs, .equipes').removeClass('actif');
					$('#valid_evt').addClass('inactif').show();
					$('#list tr').removeClass('actif');
					$('#time_evt').val('');
					$('#update_evt').attr('data-id', '').attr('data-code', '');
					$('#zoneTemps a').removeClass('actif2');
					$('#update_evt, #delete_evt').hide();
					//$('#reset_evt').removeClass('evtButton3');
					if(periode_en_cours != ''){
						$('.periode').removeClass('actif');
						$('#'+periode_en_cours).addClass('actif');
					}
				});
				// EDIT
				$('#list').on( "click", 'tr', function() {
					$('#reset_evt').click();
					//periode_en_cours = $('.periode[class*="actif"]').attr('id');
					$('.periode').removeClass('actif');
					$(this).addClass('actif'); //Efface la ligne !
					code_ligne = $(this).attr('data-code');
					id_ligne = $(this).attr('id');
					$('#zoneTemps a').addClass('actif2');
					$('#update_evt').show().attr('data-code',code_ligne).attr('data-id',id_ligne);
					$('#delete_evt').show();
					//$('#reset_evt').addClass('evtButton3');
					$('#valid_evt').hide();
					code_split = code_ligne.split('-');
					ancienne_ligne = code_ligne;
					$('a[id="' + code_split[0] + '"]').addClass('actif');
					$('#time_evt').val(code_split[1]);
					$('a[data-code="' + code_split[2] + '"]').addClass('actif');
					if(code_split[4] != '') {
						$('a[data-id="' + code_split[4] + '"]').addClass('actif');
					}else{
						$('a[data-player="Equipe ' + code_split[3] + '"]').addClass('actif');
					}
                    $('#time_evt').focus();
				});
				// UPDATE
				$('#update_evt').click(function( event ) {
					event.preventDefault();
					var texte;
					var ligne_nom = $('.joueurs[class*="actif"]').attr('data-player');
					var ligne_nb = $('.joueurs[class*="actif"]').attr('data-nb');
					var ligne_num = ligne_nb + ' - ';
					var ligne_id_joueur = $('.joueurs[class*="actif"]').attr('data-id');
					var ligne_equipe = $('.joueurs[class*="actif"]').attr('data-equipe');
					var ligne_evt = $('.evtButton[class*="actif"]').attr('data-evt');
					if(ligne_evt === undefined) {return;}
					var carton_equipe = 0;
					if(ligne_nom === undefined) {
						carton_equipe = 1;
						ligne_nom = $('.equipes[class*="actif"]').attr('data-player');
						ligne_equipe = $('.equipes[class*="actif"]').attr('data-equipe');
						ligne_num = '';
						ligne_id_joueur = '';
						if(ligne_nom === undefined) {
							custom_alert(lang.Selectionnez_equipe_joueur);
							return;
						}
					}
					var code_ligne = $('.periode[class*="actif"]').attr('id');
					code_ligne += '-' + $('#time_evt').val();
					code_ligne += '-' + $('.evtButton[class*="actif"]').attr('data-code');
					code_ligne += '-' + ligne_equipe;
					code_ligne += '-' + ligne_id_joueur;
					code_ligne += '-' + ligne_nb;
					texte  = $('#time_evt').val() + ' ' + ligne_evt;
					texte += ' éq.' + ligne_equipe + ' ' + ligne_num + ligne_nom;
					$.post(
						'v2/evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : idMatch,
							ligne : code_ligne,
							ancienneLigne : ancienne_ligne,
							idLigne : id_ligne,
							type : 'update'
						},
						function(data){ // callback
							if(data == 'OK'){
                                // suppression anciens éléments
                                if(code_split[2] == 'B'){
                                    $('#score'+code_split[3]+', #score'+code_split[3]+'2, #score'+code_split[3]+'3').text(parseInt($('#score'+code_split[3]).text()) - 1);
                                    $('a[data-id="'+code_split[4]+'"] img[class="c_but"]').first().remove();
                                }
                                if(code_split[2] == 'V'){
                                    $('a[data-id="'+code_split[4]+'"] img[src="v2/carton_vert.png"]').first().remove();
                                    if(code_split[5] == 'teamCard') {
                                        // PREMIER CARTON VERT DE CHAQUE JOUEUR !
                                        $('.joueurs[data-equipe="'+code_split[3]+'"]').each(function(){
                                            $(this).find('img[src="v2/carton_vert.png"]').first().remove();
                                        });
                                    }
                                }
                                if(code_split[2] == 'J'){
                                    $('a[data-id="'+code_split[4]+'"] img[src="v2/carton_jaune.png"]').first().remove();
                                }
                                if(code_split[2] == 'R'){
                                    $('a[data-id="'+code_split[4]+'"] img[src="v2/carton_rouge.png"]').first().remove();
                                }
                                $('tr[id="'+id_ligne+'"] td').remove();
                                // insertion nouveaux éléments
                                avertissement(texte);
                                texteTR = '<tr id="ligne_' + data + '">';
                                texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>';
                                texteBut = '<td class="list_evt">';
                                if(ligne_evt == 'But') {
                                    texteBut += '<img src="v2/but1.png" />';
                                    $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_but" src="v2/but1.png" />');
                                }
                                texteBut += '</td>';
                                texteNom = '<td class="list_nom">' + ligne_num + ligne_nom ;
                                if(ligne_evt == 'Arret')
                                    texteNom += ' (' + lang.Tir_contre + ')';
                                if(ligne_evt == 'Tir')
                                    texteNom += ' (' + lang.Tir + ')';
                                texteNom += '</td>';
                                texteVert = '<td class="list_evt">'
                                if(ligne_evt == 'Carton vert') {
                                    texteVert += '<img src="v2/carton_vert.png" />';
                                    $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                    //si 2 verts...
                                    var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length;
                                    if(nb_cartons == 2) {
                                        custom_alert(nb_cartons + 'e <img class="c_carton" src="v2/carton_vert.png" /> pour ce joueur !<br />Vérifier type de faute.', 'Attention');
                                    }
                                    //si 3 verts...
                                    var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_vert.png"]').length;
                                    if(nb_cartons >= 3) {
                                        custom_alert(nb_cartons + 'e <img class="c_carton" src="v2/carton_vert.png" /> pour ce joueur !<br />Avertir l\'arbitre, modifier en jaune.', 'Attention');
                                    }
                                /*	if(carton_equipe == 1 && ligne_equipe == 'A' && confirm('Carton d\'équipe pour l\'équipe A ?')) {
                                        $('.joueurs[data-equipe="A"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                        code_ligne += '-teamCard';
                                    }
                                    if(carton_equipe == 1 && ligne_equipe == 'B' && confirm('Carton d\'équipe pour l\'équipe B ?')) {
                                        $('.joueurs[data-equipe="B"]>.c_evt').append('<img class="c_carton" src="v2/carton_vert.png" />');
                                        code_ligne += '-teamCard';
                                    }
                                */
                                }
                                texteVert += '</td>';
                                texteJaune = '<td class="list_evt">';
                                if(ligne_evt == 'Carton jaune') {
                                    texteJaune += '<img src="v2/carton_jaune.png" />';
                                    $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_jaune.png" />');
                                    //si 2 jaunes...
                                    var nb_cartons = $('.joueurs[class*="actif"] img[src="v2/carton_jaune.png"]').length;
                                    if(nb_cartons >= 2) {
                                        custom_alert(nb_cartons + 'e <img class="c_carton" src="v2/carton_jaune.png" /> pour ce joueur !', 'Attention');
                                    }
                                }
                                texteJaune += '</td>';
                                texteRouge = '<td class="list_evt">';
                                if(ligne_evt == 'Carton rouge') {
                                    texteRouge += '<img src="v2/carton_rouge.png" />';
                                    $('.joueurs[class*="actif"]>.c_evt').append('<img class="c_carton" src="v2/carton_rouge.png" />');
                                }
                                texteRouge += '</td>';
                                texteVide = '<td colspan="5"></td>';
                                texteTR2 = '</tr>';
                                $('.evtButton, .joueurs, .equipes').removeClass('actif');
                                $('#valid_evt').addClass('inactif');
                                if(ligne_equipe == 'A'){
                                    if(ligne_evt == 'But')
                                        $('#scoreA, #scoreA2, #scoreA3').text(parseInt($('#scoreA').text()) + 1);
                                    texte2 = texteVert + texteJaune + texteRouge + texteNom + texteBut + texteChrono + texteVide;
                                }else{
                                    if(ligne_evt == 'But')
                                        $('#scoreB, #scoreB2, #scoreB3').text(parseInt($('#scoreB').text()) + 1);
                                    texte2 = texteVide + texteChrono + texteBut + texteNom + texteVert + texteJaune + texteRouge;
                                }


                                $('tr[id="'+id_ligne+'"]').attr('data-code', code_ligne).append(texte2);
                                $('#reset_evt').click();
                                $.post(
                                    'v2/StatutPeriode.php', // Le fichier cible côté serveur.
                                    { // variables
                                        Id_Match : idMatch,
                                        Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
                                        TypeUpdate : 'ValidScoreDetail'
                                    },
                                    function(data){ },
                                    'text' // Format des données reçues.
                                );
							}else{
								custom_alert('Changement impossible<br />' + data, 'suppression');
							}
						},
						'text' // Format des données reçues.
					);

				});
				
				// DELETE
				$('#delete_evt').click(function( event ) {
					event.preventDefault();

					$.post(
						'v2/evt_match.php', // Le fichier cible côté serveur.
						{ // variables
							idMatch : idMatch,
							ligne : code_ligne,
							ancienneLigne : ancienne_ligne,
							idLigne : id_ligne,
							type : 'delete',
						},
						function(data){ // callback
							if(data == 'OK'){
								// suppression éléments
								if(code_split[2] == 'B'){
									$('#score'+code_split[3]+', #score'+code_split[3]+'2, #score'+code_split[3]+'3').text(parseInt($('#score'+code_split[3]).text()) - 1);
									$('a[data-id="'+code_split[4]+'"] img[class="c_but"]').first().remove();
								}
								if(code_split[2] == 'V'){
									$('a[data-id="'+code_split[4]+'"] img[src="v2/carton_vert.png"]').first().remove();
									if(code_split[5] == 'teamCard') {
										// PREMIER CARTON VERT DE CHAQUE JOUEUR !
										$('.joueurs[data-equipe="'+code_split[3]+'"]').each(function(){
											$(this).find('img[src="v2/carton_vert.png"]').first().remove();
										});
									}
								}
								if(code_split[2] == 'J'){
									$('a[data-id="'+code_split[4]+'"] img[src="v2/carton_jaune.png"]').first().remove();
								}
								if(code_split[2] == 'R'){
									$('a[data-id="'+code_split[4]+'"] img[src="v2/carton_rouge.png"]').first().remove();
								}
								$('tr[id="'+id_ligne+'"]').hide();
								$('#reset_evt').click();
								$.post(
									'v2/StatutPeriode.php', // Le fichier cible côté serveur.
									{ // variables
										Id_Match : idMatch,
										Valeur : $('#scoreA').text() + '-' + $('#scoreB').text(),
										TypeUpdate : 'ValidScoreDetail'
									},
									function(data){ },
									'text' // Format des données reçues.
								);
							}else{
								custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
							}
						},
						'text' // Format des données reçues.
					);
				});
				
				/**************** CHRONO *******************/
				
				Raz();
				$('#stop_button').hide();
				$('#run_button').hide();
				$.post(
					'v2/getChrono.php',
					{
						idMatch : idMatch,
					},
					function(data){
						if(data.action == 'start' || data.action == 'run'){
							temp_time = new Date();
							start_time = new Date();
							run_time = new Date();
							start_time.setTime(data.start_time);
							$('#start_time_display').text(start_time.toLocaleString()); //debug
							run_time.setTime(temp_time.getTime() - start_time.getTime());
							max_time = data.max_time;
							split_period = max_time.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							$('#start_button').hide();
							$('#run_button').hide();
							$('#stop_button').show();
					//		$('#chrono_moins').hide();
					//		$('#chrono_plus').hide();
							$('#heure').css('background-color', '#009900');
							timer = setInterval(Horloge, 500);
							avertissement(lang.Chrono + ' ' + lang.en_cours);
							$('#tabs-2_link').click();
						} else if(data.action == 'stop'){
							temp_time = new Date();
							start_time = new Date();
							run_time = new Date();
							start_time.setTime(data.start_time);
							run_time.setTime(data.run_time);
							$('#start_time_display').text(start_time.toLocaleString()); //debug
							$('#run_time_display').text(run_time.toLocaleString()); //debug
							max_time = data.max_time;
							split_period = max_time.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							$('#start_button').hide();
							$('#run_button').show();
							$('#stop_button').hide();
							$('#chrono_moins').show();
							$('#chrono_plus').show();
							$('#heure').css('background-color', '#990000');
							var minut_ = run_time.getMinutes();
							if (minut_ < 10) {minut_ = '0' + minut_;}
							var second_ = run_time.getSeconds();
							if (second_ < 10) {second_ = '0' + second_;}
							$('#heure').val(minut_ + ':' + second_);
							avertissement(lang.Chrono + ' ' + lang.arrete);
							$('#tabs-2_link').click();
						}
					},
					'json'
				);
				$('#chrono_moins').click(function(){
					start_time.setTime(start_time.getTime() - 1000);
					run_time.setTime(run_time.getTime() - 1000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				$('#chrono_plus').click(function(){
					start_time.setTime(start_time.getTime() + 1000);
					run_time.setTime(run_time.getTime() + 1000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				$('#chrono_moins10').click(function(){
					start_time.setTime(start_time.getTime() - 10000);
					run_time.setTime(run_time.getTime() - 10000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				$('#chrono_plus10').click(function(){
					start_time.setTime(start_time.getTime() + 10000);
					run_time.setTime(run_time.getTime() + 10000);
					var minut_ = run_time.getMinutes();
					if (minut_ < 10) {minut_ = '0' + minut_;}
					var second_ = run_time.getSeconds();
					if (second_ < 10) {second_ = '0' + second_;}
					$('#heure').val(minut_ + ':' + second_);
					$('#chronoText').hide();
					$('#updateChrono').show();
				});
				//$('#time_evt').val('00:00');
				//alert($('#time_evt').val());
				$('#time_plus60').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]) + 1;
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2);
					if (second_2 > 60) {second_2 = 60;}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_moins60').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]) - 1;
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2);
					if (second_2 > 60) {second_2 = 60;}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_plus10').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) + 10;
					if (second_2 > 59) {second_2 = second_2 - 60; $('#time_plus60').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_moins10').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) - 10;
					if (second_2 < 0) {second_2 = second_2 + 60; $('#time_moins60').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_plus1').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) + 1;
					if (second_2 > 59) {second_2 = 0; $('#time_plus60').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#time_moins1').click(function(){
					var temp_time2 = $('#time_evt').val();
					temp_time2 = temp_time2.split(':');
					minut_2 = Number(temp_time2[0]);
					if(minut_2 > 99) {minut_2 = 99;}
					if (minut_2 < 0) {minut_2 = 0;}
					if (minut_2 < 10) {minut_2 = '0' + minut_2;}
					var second_2 = temp_time2[1];
					if(isNaN(second_2)) {second_2 = 0;}
					second_2 = Number(second_2) - 1;
					if (second_2 < 0) {second_2 = 59; $('#time_moins60').click();}
					if (second_2 < 10) {second_2 = '0' + second_2;}
					$('#time_evt').val(minut_2 + ':' + second_2);
				});
				$('#updateChrono').click(function() {
					$.post(
						'v2/ajax_updateChrono.php',
						{
							idMatch : idMatch,
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
						},
						function(data){
							if(data == 'OK'){
								avertissement('Start chrono');
							}
						},
						'text'
					);
					$('#chronoText').show();
					$('#updateChrono').hide();
				});
				
				$('#start_button').click(function(){
					start_time = new Date();
					run_time = new Date();
					run_time.setTime(0);
					run_time2 = new Date();
					run_time2.setTime(0);
					Horloge();
					timer = setInterval(Horloge, 500);
					$('#start_time_display').text(start_time.toLocaleString()); //debug
					$('#run_time_display').text(run_time.toLocaleString()); //debug
					$('#start_button').hide();
					$('#run_button').hide();
					$('#stop_button').show();
				//	$('#chrono_moins').hide();
				//	$('#chrono_plus').hide();
					$('#heure').css('background-color', '#009900');
					//alert(run_time.getTime());
					$.post(
						'v2/setChrono.php',
						{
							idMatch : idMatch,
							action : 'start',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Start chrono');
							}
						},
						'text'
					);
				});
				$('#stop_button').click(function(){
					if (run_time)
						$('#stop_time_display').text(run_time.toLocaleString()); //debug
					clearInterval(timer);
					$('#run_button').show();
					$('#start_button').hide();
					$('#stop_button').hide();
					$('#chrono_moins').show();
					$('#chrono_plus').show();
					$('#heure').css('background-color', '#990000');
					$.post(
						'v2/setChrono.php',
						{
							idMatch : idMatch,
							action : 'stop',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Stop chrono');
							}
						},
						'text' 
					);
				});
				$('#run_button').click(function(){
					start_time = new Date();
					// chrono
					// start_time.setTime(start_time.getTime() - run_time.getTime());
					// compte à rebours
					var max_time1 = (minut_max * 60000) + (second_max * 1000);
					start_time.setTime(run_time.getTime() - max_time1 + start_time.getTime());
					Horloge();
					timer = setInterval(Horloge, 500);
					$('#start_time_display').text(start_time.toLocaleString()); //debug
					$('#run_time_display').text(run_time.toLocaleString()); //debug
					$('#run_button').hide();
					$('#stop_button').show();
				//	$('#chrono_moins').hide();
				//	$('#chrono_plus').hide();
					$('#heure').css('background-color', '#009900');
					$.post(
						'v2/setChrono.php', // : replace table chrono ligne idMatch...
						{
							idMatch : idMatch,
							action : 'run',
							start_time : start_time.getTime(),
							run_time : run_time.getTime(),
							max_time : minut_max + ':' + second_max
						},
						function(data){
							if(data == 'OK'){
								avertissement('Run chrono');
							}
						},
						'text'
					);
				});
				$('#raz_button').click(function(){
					$('#start_button').show();
					$('#run_button').hide();
					$('#stop_button').hide();
					$('#chrono_moins').show();
					$('#chrono_plus').show();
					clearInterval(timer);
					Raz();
					$('#heure').css('background-color', '#444444');
					$.post(
						'v2/setChrono.php',
						{
							idMatch : idMatch,
							action : 'RAZ',
						},
						function(data){
							if(data == 'OK'){
								avertissement(lang.RAZ + ' ' + lang.chrono);
							}
						},
						'text'
					);
				});
            });