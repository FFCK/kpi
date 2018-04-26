/* 
 * Feuille de marque en ligne
 * Javascript partie D
 */

       
            $(function() {    
                /* DIALOG END MATCH */
				$( "#dialog_end_match" ).dialog({
					autoOpen: false,
					modal: true,
					width: 500,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							$.post(
								'v2/saveComments.php', // Le fichier cible côté serveur.
								{ // variables
									idMatch : idMatch,
									value : $('#commentaires').val(),
									heure_fin_match : $('#time_end_match').val()
								},
								function(data){ // callback
									if(data == $('#commentaires').val()){
										$('#end_match_time').removeClass('inactif').addClass('actif');
										$('#end_match_time').val($('#time_end_match').val());
										$('#raz_button').click();
										$('#run_button').hide();
										$('.statut').removeClass('actif');
										$('#END').addClass('actif');
										$('#zoneTemps, .periode, #zoneChrono').hide();
										$('.endmatch').show();
										$('#comments').text($('#commentaires').val());
									}else{
										custom_alert(lang.Action_impossible + '<br />' + data, lang.Attention);
									}
								},
								'text' // Format des données reçues.
							)
                            .fail(function(xhr, status, error){
                                custom_alert(lang.Action_impossible + '<br>' + error, lang.Attention);
                            });
						},
						'Annuler/Dismiss': function() {
							$( this ).dialog( "close" );
						}
					}
				});
				/* DIALOG END */
				$( "#dialog_end" ).dialog({
					autoOpen: false,
					modal: true,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							$('#heure').val(minut_max + ':' + second_max);
						}
					}
				});
			
				/* DIALOG AJUST */
				$( "#dialog_ajust" ).dialog({
					autoOpen: false,
					modal: true,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
							var split_period = $('#periode_ajust').val();
							split_period = split_period.split(':');
							minut_max = split_period[0];
							second_max = split_period[1];
							var split_chrono = $('#chrono_ajust').val();
							split_chrono = split_chrono.split(':');
							run_time.setTime(split_chrono[0] * 60000 + split_chrono[1] * 1000);
							$('#stop_button').click();
							$('#run_time_display').text(run_time.toLocaleString()); //debug
							$('#heure').val($('#chrono_ajust').val());
                            $('#time_evt').val('');
							if($('#chrono_ajust').val() == '00:00'){
								$('#run_button').hide();
							}
						},
						"Annuler/Dismiss": function() {
							$( this ).dialog( "close" );
						}
					}
				});
                
                /* DIALOG MOTIF */
				$( "#dialog_motif" ).dialog({
					autoOpen: false,
					modal: true,
					width: 800,
					buttons: {
						"Annuler/Dismiss": function() {
							$( this ).dialog( "close" );
							$('#motif').val('');
							$('#motif_texte').val('');
                            $('#time_evt').focus();
						}
					}
				});
			
				/* TABS */
				$('#tabs-1_link').hide();
				$('#tabs-2').hide();
				$('.fm_tabs').click(function() {
					$('.fm_tabs').toggle();
					$('.tabs_content').toggle();
				});
				/* END MATCH */
				/* Charge nouvelle feuille */
				$('#chargeFeuille').click(function(event){
					event.preventDefault();
					window.location = 'FeuilleMarque2.php?idMatch=' + $('#idFeuille').val();
				});
                $('#idFeuille').keypress(function(e){
                    if( e.which == 13 && $(this).val() != ''){
                        $('#chargeFeuille').click();
                    }
                });
				/* ORDRE EVTS */
				$('#change_ordre').click(function() {
					if(ordre_actuel == 'up'){
						ordre_actuel = 'down';
						$('#change_ordre img').attr('src','../img/down.png');
						$('#list tr').each(function(){
							$(this).prependTo('#list');
						});
					}else{
						ordre_actuel = 'up';
						$('#change_ordre img').attr('src','../img/up.png');
						$('#list tr').each(function(){
							$(this).prependTo('#list');
						});
					}
				});
				
				// VERSION PDF
				$('#pdfFeuille').buttonset();
				$('#pdfFeuille').click(function(event) {
					event.preventDefault();
					window.open('FeuilleMatchMulti.php?listMatch=' + idMatch, '_blank');
				});
                
                // Stats
				$('#btn_stats').click(function(event) {
					event.preventDefault();
					window.open('FeuilleMarque2stats.php?idMatch=' + idMatch);
				});
            });

