/* 
 * Feuille de marque en ligne
 * Javascript partie C
 */

            $(function() {

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

				
				/* PERIODE */
				$('.periode').click(function( event ) {
					event.preventDefault();
					valeur = $(this).attr('id');
					if(	$('#update_evt').attr('data-id') == ''){
                        
						periode_en_cours = valeur;
                        $('.joueurs, .equipes, .evtButton, .chronoButton, .evtButton2').removeClass('inactif');
                        $('.periode').removeClass('actif');
                        $('#'+valeur).addClass('actif');
					}else{
						$('.periode').removeClass('actif');
						$('#'+valeur).addClass('actif');
					}
				});

				/* BOUTONS MATCH */
				$('.joueurs, .equipes').click(function( event ) {
					event.preventDefault();
					$('.joueurs, .equipes').removeClass('actif');
					$(this).addClass('actif');
				});
				$('.evtButton').click(function( event ) {
					event.preventDefault();
					$('.evtButton').removeClass('actif');
					$(this).addClass('actif');
//					if($('#update_evt').attr('data-id') == ''){
//						$('#time_evt').val($('#heure').val());
//					}
					$('#valid_evt').removeClass('inactif');
				});
				/* BUT = TEMPS MORT SYSTEMATIQUE */
                //		$('#evt_but').click(function( event ) {
                //			$('#stop_button').click();
                //		});
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
                                }
                                texteTR = '<tr id="ligne_' + data + '">';
                                texteChrono = '<td class="list_chrono">' + $('.periode[class*="actif"]').attr('id') + ' ' + $('#time_evt').val() + '</td>';
                                texteNom = '<td class="list_nom">' + ligne_num + ligne_nom ;
                                if(ligne_evt == 'Arret')
                                    texteNom += ' (' + lang.Tir_contre + ')';
                                if(ligne_evt == 'Tir')
                                    texteNom += ' (' + lang.Tir + ')';
                                texteNom += '</td>';
                                texteVide = '<td></td>';
                                texteTR2 = '</tr>';
                                $('.evtButton, .joueurs, .equipes').removeClass('actif');
                                $('#valid_evt').addClass('inactif');
                                if(ligne_equipe == 'A'){
                                    if(ordre_actuel == 'up'){
                                        $('#list').prepend(texteTR + texteNom + texteChrono + texteVide + texteTR2);
                                    }else{
                                        $('#list').append(texteTR + texteNom + texteChrono + texteVide + texteTR2);
                                    }
                                }else{
                                    if(ordre_actuel == 'up'){
                                        $('#list').prepend(texteTR + texteVide + texteChrono + texteNom + texteTR2);
                                    }else{
                                        $('#list').append(texteTR + texteVide + texteChrono + texteNom + texteTR2);
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
//					$('#time_evt').val('');
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
								if(ligne_evt == 'Tir contre')
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

				
                
				
            });