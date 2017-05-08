/* 
 * Feuille de marque en ligne
 * Javascript partie D
 */

       
            $(function() {    

				/* END MATCH */
				/* Charge nouvelle feuille */
                
                $('#btn_change_match').click(function(event){
					event.preventDefault();
					$("#dialog_change_match").dialog();
                });
                
                
				$('#chargeFeuille').click(function(event){
					event.preventDefault();
					window.location = 'FeuilleMarque3stats.php?idMatch=' + $('#idFeuille').val();
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
				
            });

