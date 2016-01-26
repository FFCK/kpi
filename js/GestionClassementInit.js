$(document).ready(function() { //Jquery

	// Maskedinput (ne fonctionne pas sur les champs dynamiques !
	$(".champsPoints").mask("99");

	// Actualiser
	$('#actuButton').click(function(){
		$('#formClassementInit').submit();
	});
	//Ajout title
	$('#tableauJQ td > a').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	$('#tableauJQ').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	// blur d'une input => validation de la donnée
	$('#inputZone').live('blur', function(){
		validationDonnee();
	});
	// focus sur un lien du tableau => remplace le lien par un input
	$('#tableauJQ td > a').focus(function(event){
		event.preventDefault();
		var valeur = $(this).text();
		var tabindexVal = $(this).attr('tabindex');
		$(this).attr('tabindex',tabindexVal+1000);
		$(this).before('<input type="text" id="inputZone" class="champsPoints" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		$(this).hide();
		setTimeout( function() { $('#inputZone').select() }, 0 );
	});
	
	function validationDonnee(){
		var nouvelleValeur = $('#inputZone').val();
		var tabindexVal = $('#inputZone').attr('tabindex');
		$('#inputZone + a').attr('tabindex',tabindexVal);
		$('#inputZone + a').show();
		var valeur = $('#inputZone + a').text();
		var identifiant = $('#inputZone + a').attr('id');
		var identifiant2 = identifiant.split('-');
		var numEquipe = identifiant2[1];
		var typeValeur = identifiant2[0];
		if(valeur != nouvelleValeur){
			AjaxTableName = $('#AjaxTableName').val();
			AjaxWhere = $('#AjaxWhere').val();
			var AjaxUser = $('#AjaxUser').val();
			$.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: nouvelleValeur,
					AjId: numEquipe,
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function(data){
					if(data != 'OK!'){
						alert('mise à jour impossible : '+data);
					}else{
						$('#'+identifiant).text(nouvelleValeur);
					}
				}
			);
		};
		$('#inputZone').remove();
	}
	// bouton RAZ
	$('#raz').click(function(){
		if(confirm('Vous allez tout remettre à zéro ! Confirmez ?')){
			razDonnees();
		}
	});
	
	function razDonnees(){
		var tabindexVal = $('#inputZone + a').attr('tabindex');
		$('#inputZone + a').attr('tabindex',tabindexVal-100);
		$('#inputZone + a').show();
		$('#inputZone').remove();
		$('#tableauJQ td > a').each(function(e){
			var identifiant = $(this).attr('id');
			var valeur = $(this).text();
			var nouvelleValeur = 0;
			var identifiant2 = identifiant.split('-');
			var numEquipe = identifiant2[1];
			var typeValeur = identifiant2[0];
			if(valeur != nouvelleValeur){
				AjaxTableName = $('#AjaxTableName').val();
				AjaxWhere = $('#AjaxWhere').val();
				$.get("UpdateCellJQ.php",
					{
						AjTableName: AjaxTableName,
						AjWhere: AjaxWhere,
						AjTypeValeur: typeValeur,
						AjValeur: nouvelleValeur,
						AjId: numEquipe,
						AjOk: 'OK'
					},
					function(data){
						if(data=='Erreur'){
							alert('mise à jour impossible');
						}else{
							$('#'+identifiant).text(nouvelleValeur);
						}
					}
				);
			};
		});
	}
	
	
});

