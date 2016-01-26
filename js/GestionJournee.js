

function changeEquipeA()
{
}

function changeEquipeB()
{
}

function validMatch()
{
		var dateMatch = document.forms['formJournee'].elements['Date_match'].value;
		if (dateMatch.length == 0)
		{
			alert("La date est Vide ..., Ajout Impossible !");
			return false;
		}
		
		var heureMatch = document.forms['formJournee'].elements['Heure_match'].value;
		if ((heureMatch.length != 5) || (heureMatch.charAt(2) != ':'))
		{
			if (!confirm("L'heure n'est pas valide (format hh:mm). Continuer ?")) 
				return false;
		}
		
		var journMatch = document.forms['formJournee'].elements['comboJournee'].value;
		if (journMatch == '*')
		{
			alert("Vous n'avez pas sélectionné de journée / phase pour votre match , Ajout Impossible !");
			return false;
		}
		
		return true;
}

function Add()
{
	if (!validMatch())
		return;
						
	changeCombo('formJournee','equipeA', 'idEquipeA', false);
	changeCombo('formJournee','equipeB', 'idEquipeB', false);
	
	document.forms['formJournee'].elements['Cmd'].value = 'Add';
	document.forms['formJournee'].elements['ParamCmd'].value = '';

	document.forms['formJournee'].submit();
}

function Update()
{
	if (!validMatch())
		return;
						
	changeCombo('formJournee','equipeA', 'idEquipeA', false);
	changeCombo('formJournee','equipeB', 'idEquipeB', false);
	
	document.forms['formJournee'].elements['Cmd'].value = 'Update';
	document.forms['formJournee'].elements['ParamCmd'].value = '';

	document.forms['formJournee'].submit();
}

function Raz()
{
	document.forms['formJournee'].elements['Cmd'].value = 'Raz';
	document.forms['formJournee'].elements['ParamCmd'].value = '';
	document.forms['formJournee'].submit();
}


function ParamMatch(idMatch)
{
	document.forms['formJournee'].elements['Cmd'].value = 'ParamMatch';
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch;
	document.forms['formJournee'].submit();
}


function ChangeOrderMatchs(Journee)
{
	document.forms['formJournee'].action = 'GestionJournee.php?idJournee=' + Journee;
	document.forms['formJournee'].submit();
}

function changeCompet()
{
	document.forms['formJournee'].elements['Cmd'].value = '';
	document.forms['formJournee'].elements['ParamCmd'].value = 'changeCompet';
	document.forms['formJournee'].submit();
}

function publiMatch(idMatch, pub)
{
	if(!confirm('Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'PubliMatch';
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch;
	document.forms['formJournee'].elements['Pub'].value = pub;
	document.forms['formJournee'].submit();
}
		
function verrouMatch(idMatch, verrou)
{
	if(!confirm('Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouMatch';
	document.forms['formJournee'].elements['ParamCmd'].value = idMatch;
	document.forms['formJournee'].elements['Verrou'].value = verrou;
	document.forms['formJournee'].submit();
}
 		
function publiMultiMatchs()
{
	if(!confirm('Publier/Dépublier les matchs sélectionnés. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'PubliMultiMatchs';
	document.forms['formJournee'].submit();
}
 		
function verrouMultiMatchs()
{
	if(!confirm('Verrouiller/Déverrouiller les matchs sélectionnés. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouMultiMatchs';
	document.forms['formJournee'].submit();
}

function verrouPubliMultiMatchs()
{
	var matchs = document.forms['formJournee'].elements['ParamCmd'].value;
	if(!confirm('Verrouiller & Publier les matchs sélectionnés '+matchs+'. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'VerrouPubliMultiMatchs';
	document.forms['formJournee'].submit();
}

function affectMultiMatchs()
{
	if(!confirm('Vous devez avoir recalculé le classement ! \n=> Modifiez manuellement les classements en cas d\'égalité dans les poules. \nConfirmez-vous l\'affectation automatiquement des équipes pour les matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'AffectMultiMatchs';
	document.forms['formJournee'].submit();
}

function annulMultiMatchs()
{
	if(!confirm('Etes-vous sûr de vouloir supprimer les équipes et arbitres des matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'AnnulMultiMatchs';
	document.forms['formJournee'].submit();
}

function changeMultiMatchs()
{
	var journ = $('#comboJournee').val();
	if(journ == '*')
	{
		alert('Selectionnez une journee / une phase / une poule !');
		$('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		return false;
	}
	if(!confirm('Etes-vous sûr de vouloir changer la journée / la phase / la poule des matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formJournee'].elements['Cmd'].value = 'ChangeMultiMatchs';
	document.forms['formJournee'].submit();
}


// ****************************************************************************************************

$(document).ready(function() { //Jquery + NoConflict='J'

	//sessionJournee
	//ajax
	var journ = $('#comboJournee').val();
	$.get("Autocompl_session_journee.php", {
		j: journ
	//},  function(data) {
	//	alert(data);
	});
	$('#comboJournee').change(function(){
		var journ = $('#comboJournee').val();
		$.get("Autocompl_session_journee.php", {
			j: journ
		//},  function(data) {
		//	alert(data);
		});
//		alert(journ+' !');
	});
	
	
	// AUTOCOMPLETE ARBITRES
	$("#arbitre1").focus(function() {
		var journ = $('#comboJournee').val();
		if(journ == '*')
		{
			//alert('Selectionnez une journee / une phase !');
			$('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		}
	});
	$("#arbitre1").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#arbitre1").result(function(event, data, formatted) {
		if (data) {
			if(data[1] == 'XXX')
			{
				$("#arbitre1_matric").val('');
				$("#arbitre1").val('');
			}
			else
			{
				if(data[4] != '')
					var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
				else
					var nomArb = data[2]+' '+data[3]+' '+data[5];
				$("#arbitre1_matric").val(data[1]);
				$("#arbitre1").val(nomArb);
			}
		}
	});

	$("#arbitre2").focus(function() {
		var journ = $('#comboJournee').val();
		if(journ == '*')
		{
			//alert('Selectionnez une journee / une phase !');
			$('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		}
	});
	$("#arbitre2").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#arbitre2").result(function(event, data, formatted) {
		if (data) {
			if(data[1] == 'XXX')
			{
				$("#arbitre2_matric").val('');
				$("#arbitre2").val('');
			}
			else
			{
				if(data[4] != '')
					var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
				else
					var nomArb = data[2]+' '+data[3]+' '+data[5];
				$("#arbitre2_matric").val(data[1]);
				$("#arbitre2").val(nomArb);
			}
		}
	});
	
	// Maskedinput
	$(".champsHeure").mask("99:99");
	$('.date').mask("99/99/9999");

	//Recherches arbitres
	$('#iframeRechercheLicenceIndi2').hide();
	$('#rechercheArbitre1').click(function(e){
		//$('#numeroChamps').val('1');
		$('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre1_matric&zoneIdentite=arbitre1');
		$('#iframeRechercheLicenceIndi2').toggle();
	});
	$('#rechercheArbitre2').click(function(e){
		//$('#numeroChamps').val('2');
		$('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre2_matric&zoneIdentite=arbitre2');
		$('#iframeRechercheLicenceIndi2').toggle();
	});

	//Init Titulaires
	$('#InitTitulaireCompet').click(function(e){
		e.preventDefault();
		var champs = 'Compet';
		var valeur = $('#comboCompet').val();
		var valeur2 = $('#comboCompet option:selected').text();
		if(valeur == '*'){
			alert('Sélectionnez une compétition ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	$('#InitTitulaireEquipeA').click(function(e){
		e.preventDefault();
		var champs = 'Equipe';
		var valeur = $('#equipeA').val();
		var valeur2 = $('#equipeA option:selected').text();
		if(valeur == '-1'){
			alert('Sélectionnez une équipe ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	$('#InitTitulaireJournee').click(function(e){
		e.preventDefault();
		var champs = 'Journee';
		var valeur = $('#comboJournee').val();
		var valeur2 = $('#comboJournee option:selected').text();
		if(valeur == '*'){
			alert('Sélectionnez une journée/phase ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	$('#InitTitulaireEquipeB').click(function(e){
		e.preventDefault();
		var champs = 'Equipe';
		var valeur = $('#equipeB').val();
		var valeur2 = $('#equipeB option:selected').text();
		if(valeur == '-1'){
			alert('Sélectionnez une équipe ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	function initTitu(champs, valeur, valeur2)
	{
		if(confirm('Voulez-vous supprimer tous les joueurs et ré-affecter\nles joueurs présents (sauf X-Inactifs et A-Arbitres)\npour les matchs non verrouillés de :\n'+champs+' : '+valeur2))
		{
			//ajax
			$.post("InitTitulaireJQ.php", {
				champs: champs,
				valeur: valeur,
				valeur3: -1
			},  function(data) {
				alert(data);
			});
		}
	}
	
	// Direct Input
	//Ajout title
	$('.directInput').attr('title','Cliquez pour modifier');
	$('.pbArb').attr('title','Arbitre non identifié');
	$('.undefTeam').attr('title','Equipe non définie : rechargez la page pour actualiser l\'encodage.');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	$('#tableMatchs').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	
	// focus sur un span du tableau => remplace le span par un input
	$("body").delegate("#tableMatchs td span.directInput", "focus", function(event){
	//$("body").on("focus", "#tableMatchs td > span.directInput", function(event){
		event.preventDefault();
		$('#inputZone2annul').click();
		var valeur = $(this).text();
		var tabindexVal = $(this).attr('tabindex');
		$(this).attr('tabindex',tabindexVal+1000);
		if($(this).hasClass('text'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="12" value="'+valeur+'">');
		}
		else if($(this).hasClass('numMatch'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="1" value="'+valeur+'">');
		}
		else if($(this).hasClass('date'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="8" value="'+valeur+'">');
			$('#inputZone').mask("99/99/9999");
		}
		else if($(this).hasClass('heure'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="4" value="'+valeur+'">');
			$('#inputZone').mask("99:99");
		}
		else if($(this).hasClass('terrain'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		else if($(this).hasClass('score'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		else if($(this).hasClass('equipe'))
		{
			$(this).before('<select id="selectZone" class="directInputSpan" tabindex="'+tabindexVal+'"></select>');
			$(this).before('<br /><input type="button" id="selectZoneAnnul" value="Annuler">');
			datamatch = $(this).attr('data-match');
			dataidEquipe = $(this).attr('data-idequipe');
			dataequipe = $(this).attr('data-equipe');
			datajournee = $(this).attr('data-journee');
			$.post(
				'v2/getEquipesMatch.php', // Le fichier cible côté serveur.
				{
					idMatch : datamatch,	// variables transmises
					idJournee : datajournee,
				},
				function(data){ // callback
					if(data){
						 for(var key in data) {
							if(data[key].Id == dataidEquipe){
								$('#selectZone').append('<option value="'+data[key].Id+'" selected="selected">'+data[key].Libelle+'</option>');
							}else{
								$('#selectZone').append('<option value="'+data[key].Id+'">'+data[key].Libelle+'</option>');
							}
						}
					}
				},
				'json' // Format des données reçues.
			);
			$('#selectZone').change(function(){
				$('#selectZoneAnnul').remove();
			});
			$('#selectZoneAnnul').click(function(){
				$('#selectZone ~ span').show();
				$('#selectZone + br').remove();
				$('#selectZoneAnnul').remove();
				$('#selectZone').remove();
			});
			$('#selectZone').blur(function(){
				newIdEquipe = $(this).val();
				newEquipe = $('#selectZone option:selected').text();
				if(newIdEquipe != dataidEquipe){
					$.post(
						'v2/setEquipesMatch.php', // Le fichier cible côté serveur.
						{
							idMatch : datamatch,	// variables transmises
							idEquipe : newIdEquipe,
							equipe : dataequipe
						},
						function(data){ // callback
							if(data){
								$('#selectZone ~ span').attr('data-idequipe', newIdEquipe).text(newEquipe).show();
								if(newIdEquipe == '0'){
									$('#selectZone ~ span').addClass('undefTeam').attr('title','Equipe non définie : rechargez la page pour actualiser l\'encodage.');
								}else{
									$('#selectZone ~ span').removeClass('undefTeam').attr('title','Cliquez pour modifier');
								}
								$('#selectZone + br').remove();
								$('#selectZoneAnnul').remove();
								$('#selectZone').remove();
							}
						},
						'text' // Format des données reçues.
					);
				}else{
					$('#selectZone ~ span').show();
					$('#selectZone + br').remove();
					$('#selectZoneAnnul').remove();
					$('#selectZone').remove();
				}
			});
		}
		else if($(this).hasClass('arbitre'))
		{
			$(this).before('<input type="text" id="inputZone2" class="directInputSpan" tabindex="'+tabindexVal+'" size="22" value="'+valeur+'">');
			$(this).before('<br /><input type="button" id="inputZone2valid" data-value2="0" value="valider"><input type="button" id="inputZone2annul" value="Annuler">');
			datamatch = $(this).attr('data-match');
			datajournee = $(this).attr('data-journee');
			dataid = $(this).attr('data-id');
			// AUTOCOMPLETE ARBITRES
			$("#inputZone2").autocomplete('Autocompl_arb.php', {
				width: 320,
				max: 80,
				mustMatch: false,
				minChars: 2,
				cacheLength: 0,
				scrollHeight: 320,
				extraParams: {
					journee: datajournee,
					sessionMatch: datamatch
				}	
			});
			$("#inputZone2").result(function(event, data, formatted) {
				if (data) {
					if(data[1] == 'XXX')
					{
						//$("#inputZone2").val('');
					}
					else
					{
						if(data[4] != '')
							var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
						else
							var nomArb = data[2]+' '+data[3]+' '+data[5];
						$("#inputZone2valid").attr('data-match', datamatch);
						$("#inputZone2valid").attr('data-id', dataid);
						$("#inputZone2valid").attr('data-value', nomArb);
						$("#inputZone2valid").attr('data-value2', data[1]);
						$("#inputZone2").val(nomArb);
					}
				}
			});
			
		}
		$(this).hide();
		setTimeout( function() { 
			$('#selectZone').select();
			$('#inputZone').select();
			$('#inputZone2').select();
		}, 0 );
	});
	
	// blur d'une input => validation de la donnée
	$('#inputZone').live('blur', function(){
		var Classe = $(this).attr('class');
		validationDonnee(Classe);
	});
	$('#inputZone2annul').live('click', function(event){
		event.preventDefault;
		$('#inputZone2annul + span').show();
		$('#inputZone2 + br').remove();
		$('#inputZone2').remove();
		$('#inputZone2valid').remove();
		$('#inputZone2annul').remove();
	});
	$('#inputZone2valid').live('click', function(event){
		event.preventDefault;
		lavaleur = $(this).attr('data-value');
		lavaleur2 = $(this).attr('data-value2');
		lavaleur3 = lavaleur + '|' + lavaleur2;
		lidMatch = $(this).attr('data-match');
		lid = $(this).attr('data-id');
		$.post(
			'v2/saveArbitres.php', // Le fichier cible côté serveur.
			{
				idMatch : lidMatch,
				id : lid,
				value : lavaleur3
			},
			function(data){ // callback
				if(data){
					lavaleur = lavaleur.replace(' (',' <br />(');
					lavaleur = lavaleur.replace(') ',')<br /> ');
					$('#inputZone2annul + span').html(lavaleur);
					if(lavaleur2 == 0){
						$('#inputZone2annul ~ span').addClass('pbArb').attr('title','Arbitre non identifié');
					}else{
						$('#inputZone2annul ~ span').removeClass('pbArb').attr('title','Cliquez pour modifier');
					}
					//compléter format(retour ligne, contrôle valeur n°arbitre)
					$('#inputZone2annul + span').show();
					$('#inputZone2 + br').remove();
					$('#inputZone2').remove();
					$('#inputZone2valid').remove();
					$('#inputZone2annul').remove();
				}
			},
			'text' // Format des données reçues.
		);
	});
	function validationDonnee(Classe){
		var nouvelleValeur = $('#inputZone').val();
		var tabindexVal = $('#inputZone').attr('tabindex');
		if(Classe == 'directInputSpan'){
			$('#inputZone + span').attr('tabindex',tabindexVal);
		}else if(Classe == 'directInputTd'){
			$('#inputZone').parent('td').attr('tabindex',tabindexVal);
		}
		$('#inputZone + span').show();
		var valeur = $('#inputZone + span').text();
		var identifiant = $('#inputZone + span').attr('id');
		var identifiant2 = identifiant.split('-');
		var typeValeur = identifiant2[0];
		var numMatch = identifiant2[1];
		var formatValeur = identifiant2[2];
		if(valeur != nouvelleValeur){
			valeurTransmise = nouvelleValeur;
			if(formatValeur == 'date'){
				valeurTransmise2 = valeurTransmise.split('/');
				valeurTransmise = valeurTransmise2[2]+'-'+valeurTransmise2[1]+'-'+valeurTransmise2[0];
			}
			var AjaxWhere = $('#AjaxWhere').val();
			var AjaxTableName = $('#AjaxTableName').val();
			var AjaxAnd = '';
			var AjaxUser = $('#AjaxUser').val();
			$.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numMatch,
					AjId2: '',
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
	function validationDonnee2(){
		var nouvelleValeur = $('#inputZone2').val();
		var tabindexVal = $('#inputZone2').attr('tabindex');
		$('#inputZone2 + span').attr('tabindex',tabindexVal);
		$('#inputZone2 + span').show();
		var valeur = $('#inputZone2 + span').text();
		var identifiant = $('#inputZone2 + span').attr('id');
		var identifiant2 = identifiant.split('-');
		var typeValeur = identifiant2[0];
		var numMatch = identifiant2[1];
		var formatValeur = identifiant2[2];
		if(valeur != nouvelleValeur && confirm('Confirmez-vous le changement pour ' + nouvelleValeur + ' ?')){
			valeurTransmise = nouvelleValeur;
			if(formatValeur == 'date'){
				valeurTransmise2 = valeurTransmise.split('/');
				valeurTransmise = valeurTransmise2[2]+'-'+valeurTransmise2[1]+'-'+valeurTransmise2[0];
			}
			var AjaxWhere = $('#AjaxWhere').val();
			var AjaxTableName = $('#AjaxTableName').val();
			var AjaxAnd = '';
			var AjaxUser = $('#AjaxUser').val();
			
/*			$.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numMatch,
					AjId2: '',
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
*/
						$('#'+identifiant).text(nouvelleValeur);
						$('#'+identifiant).attr('data-idArb', $('#inputZone2').attr('data-idArb'));

		};
		$('#inputZone2').remove();
	}

	
	
	//Affiche, masque formulaire
	$('#clickdown').toggle();
	$('#clickup').click(function() {
		$('.hideTr').toggle();
		$('#clickdown').toggle();
	});
	$('#clickdown').click(function() {
		$('.hideTr').toggle();
		$('#clickdown').toggle();
	});
	if($('#Num_match').val() == ''){
		$('#clickup').click();
	}
	
	//Surligne l'événement filtré
	if($('#evenement').val() != '-1'){
		$('#evenement').addClass('highlight4');
	}
	//Surligne la competition filtrée
	if($('#comboCompet').val() != '*'){
		$('td>span.compet').addClass('highlight3');
		$('#comboCompet').addClass('highlight3');
	}
	//Surligne la phase, le lieu filtrés
	if($('#comboJournee2').val() != '*'){
		$('td>span.phase').addClass('highlight3');
		$('td>span.lieu').addClass('highlight3');
		$('#comboJournee2').addClass('highlight3');
	}
	//Surligne la date filtrée
	if($('#filtreJour').val() != ''){
		$('td>span.date').addClass('highlight3');
		$('#filtreJour').addClass('highlight3');
	}
	//Surligne le terrain filtré
	if($('#filtreTerrain').val() != ''){
		$('td>span.terrain').addClass('highlight3');
		$('#filtreTerrain').addClass('highlight3');
	}
	
	// Highlight
    $('#reach').bind('keyup change', function(ev) {
        // pull in the new value
        var searchTerm = $(this).val();
        // remove any old highlighted terms
        $('.tableau').removeHighlight();
        // disable highlighting if empty
        if ( searchTerm ) {
            // highlight the new term
            $('.tableau').highlight( searchTerm );
        }
    });

	$("body").delegate(".typeMatch", "click", function(){
	//$("body").on("click", ".typeMatch", function(){
		//if(confirm('Confirmez-vous le changement de statut ?')){
			leMatch = $(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'C'){
				changeType = 'E';
				textType = 'Elimination';
			}else{
				changeType = 'C';
				textType = 'Classement';
			}
			$.post(
				'v2/StatutPeriode.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Match : leMatch.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Type'
				},
				function(data){ // callback
					if(data == 'OK'){
						leMatch.attr('src', '../img/type' + changeType + '.png');
						leMatch.attr('data-valeur', changeType);
						leMatch.attr('title', textType);
					}
					else{
						custom_alert('Changement impossible', 'Attention');
						leMatch.attr('src', '../img/type' + leMatch.attr('data-valeur') + '.png');
						leMatch.attr('data-valeur', leMatch.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
		//}
	});
	$("#typeMatch1").click(function(){
		if($("#Type").val() == 'C'){
			$("#Type").val("E");
			$("#typeMatch1").attr("src","../img/typeE.png").attr("alt","Elimination").attr("title", "Match éliminatoire");
		}else{
			$("#Type").val("C");
			$("#typeMatch1").attr("src","../img/typeC.png").attr("alt","Classement").attr("title", "Match de classement");
		}
	});
	$("#comboJournee").change(function(){
		loption = $(this).val();
		leType = $("#comboJournee option[value=" + loption + "]").attr('data-type');
		if(leType == 'E'){
			$("#Type").val("E");
			$("#typeMatch1").attr("src","../img/typeE.png").attr("alt","Elimination").attr("title", "Match éliminatoire");
		}else{
			$("#Type").val("C");
			$("#typeMatch1").attr("src","../img/typeC.png").attr("alt","Classement").attr("title", "Match de classement");
		}
	});
	$(".publiMatch").click(function(){
		//if(confirm('Confirmez-vous le changement de publication ?')){
			leMatch = $(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non public';
			}else{
				changeType = 'O';
				textType = 'Public';
			}
			$.post(
				'v2/StatutPeriode.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Match : leMatch.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Publication'
				},
				function(data){ // callback
					if(data == 'OK'){
						leMatch.attr('src', '../img/oeil2' + changeType + '.gif');
						leMatch.attr('data-valeur', changeType);
						leMatch.attr('title', textType);
					}
					else{
						custom_alert('Changement impossible', 'Attention');
						leMatch.attr('src', '../img/oeil2' + leMatch.attr('data-valeur') + '.gif');
						leMatch.attr('data-valeur', leMatch.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
		//}
	});
	$(".verrouMatch").click(function(){
		//if(confirm('Confirmez-vous le changement de publication ?')){
			leMatch = $(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non validé (score non public)';
			}else{
				changeType = 'O';
				textType = 'Validé / verrouillé (score public)';
			}
			$.post(
				'v2/StatutPeriode.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Match : leMatch.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Validation'
				},
				function(data){ // callback
					if(data == 'OK'){
						leMatch.attr('src', '../img/verrou2' + changeType + '.gif');
						leMatch.attr('data-valeur', changeType);
						leMatch.attr('title', textType);
						if(changeType == 'O'){
							leMatch.parent().parent().find('.directInput').addClass('directInputOff').removeClass('directInput');
							leMatch.parent().parent().find('.showOn').addClass('showOff').removeClass('showOn');
							leMatch.parent().parent().find('.typeMatch').addClass('typeMatchOff').removeClass('typeMatch');
						}else{
							leMatch.parent().parent().find('.directInputOff').addClass('directInput').removeClass('directInputOff');
							leMatch.parent().parent().find('.showOff').addClass('showOn').removeClass('showOff');
							leMatch.parent().parent().find('.typeMatchOff').addClass('typeMatch').removeClass('typeMatchOff');
						}
					}
					else{
						custom_alert('Changement impossible', 'Attention');
						leMatch.attr('src', '../img/verrou2' + leMatch.attr('data-valeur') + '.gif');
						leMatch.attr('data-valeur', leMatch.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
		//}
	});
	
	
	

});


