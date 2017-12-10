jq = jQuery.noConflict();

jq(document).ready(function() { //Jquery

	// Maskedinput (ne fonctionne pas sur les champs dynamiques !
	jq(".champsPoints").mask("99");

	// Actualiser
	jq('#actuButton').click(function(){
		jq('#formClassementInit').submit();
	});
	//Ajout title
	jq('#tableauJQ td > a').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableauJQ').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function(){
		validationDonnee();
	});
	// focus sur un lien du tableau => remplace le lien par un input
	jq('#tableauJQ td > a').focus(function(event){
		event.preventDefault();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		jq(this).before('<input type="text" id="inputZone" class="champsPoints" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		jq(this).hide();
		setTimeout( function() { jq('#inputZone').select() }, 0 );
	});
	
	function validationDonnee(){
		var nouvelleValeur = jq('#inputZone').val();
		var tabindexVal = jq('#inputZone').attr('tabindex');
		jq('#inputZone + a').attr('tabindex',tabindexVal);
		jq('#inputZone + a').show();
		var valeur = jq('#inputZone + a').text();
		var identifiant = jq('#inputZone + a').attr('id');
		var identifiant2 = identifiant.split('-');
		var numEquipe = identifiant2[1];
		var typeValeur = identifiant2[0];
		if(valeur != nouvelleValeur){
			AjaxTableName = jq('#AjaxTableName').val();
			AjaxWhere = jq('#AjaxWhere').val();
			var AjaxUser = jq('#AjaxUser').val();
			jq.get("UpdateCellJQ.php",
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
						jq('#'+identifiant).text(nouvelleValeur);
					}
				}
			);
		};
		jq('#inputZone').remove();
	}
	// bouton RAZ
	jq('#raz').click(function(){
		if(confirm('Vous allez tout remettre à zéro ! Confirmez ?')){
			razDonnees();
		}
	});
	
	function razDonnees(){
		var tabindexVal = jq('#inputZone + a').attr('tabindex');
		jq('#inputZone + a').attr('tabindex',tabindexVal-100);
		jq('#inputZone + a').show();
		jq('#inputZone').remove();
		jq('#tableauJQ td > a').each(function(e){
			var identifiant = jq(this).attr('id');
			var valeur = jq(this).text();
			var nouvelleValeur = 0;
			var identifiant2 = identifiant.split('-');
			var numEquipe = identifiant2[1];
			var typeValeur = identifiant2[0];
			if(valeur != nouvelleValeur){
				AjaxTableName = jq('#AjaxTableName').val();
				AjaxWhere = jq('#AjaxWhere').val();
				jq.get("UpdateCellJQ.php",
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
							jq('#'+identifiant).text(nouvelleValeur);
						}
					}
				);
			};
		});
	}
	
	
});

