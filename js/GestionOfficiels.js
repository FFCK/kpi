jq = jQuery.noConflict();

function changeEquipeA()
{
}

function changeEquipeB()
{
}

function validMatch()
{
		var dateMatch = document.forms['formOfficiels'].elements['Date_match'].value;
		if (dateMatch.length == 0)
		{
			alert("La date est Vide ..., Ajout Impossible !");
			return false;
		}
		
		var heureMatch = document.forms['formOfficiels'].elements['Heure_match'].value;
		if ((heureMatch.length != 5) || (heureMatch.charAt(2) != ':'))
		{
			if (!confirm("L'heure n'est pas valide (format hh:mm). Continuer ?")) 
				return false;
		}
		
		var journMatch = document.forms['formOfficiels'].elements['comboJournee'].value;
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
						
	changeCombo('formOfficiels','equipeA', 'idEquipeA', false);
	changeCombo('formOfficiels','equipeB', 'idEquipeB', false);
	
	document.forms['formOfficiels'].elements['Cmd'].value = 'Add';
	document.forms['formOfficiels'].elements['ParamCmd'].value = '';

	document.forms['formOfficiels'].submit();
}

function Update()
{
	if (!validMatch())
		return;
						
	changeCombo('formOfficiels','equipeA', 'idEquipeA', false);
	changeCombo('formOfficiels','equipeB', 'idEquipeB', false);
	
	document.forms['formOfficiels'].elements['Cmd'].value = 'Update';
	document.forms['formOfficiels'].elements['ParamCmd'].value = '';

	document.forms['formOfficiels'].submit();
}

function Raz()
{
	document.forms['formOfficiels'].elements['Cmd'].value = 'Raz';
	document.forms['formOfficiels'].elements['ParamCmd'].value = '';
	document.forms['formOfficiels'].submit();
}


function ParamMatch(idMatch)
{
	document.forms['formOfficiels'].elements['Cmd'].value = 'ParamMatch';
	document.forms['formOfficiels'].elements['ParamCmd'].value = idMatch;
	document.forms['formOfficiels'].submit();
}


function ChangeOrderMatchs(Journee)
{
	document.forms['formOfficiels'].action = 'GestionOfficiels.php?idJournee=' + Journee;
	document.forms['formOfficiels'].submit();
}

function changeCompet()
{
	document.forms['formOfficiels'].elements['Cmd'].value = '';
	document.forms['formOfficiels'].elements['ParamCmd'].value = 'changeCompet';
	document.forms['formOfficiels'].submit();
}

function publiMatch(idMatch, pub)
{
	if(!confirm('Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'PubliMatch';
	document.forms['formOfficiels'].elements['ParamCmd'].value = idMatch;
	document.forms['formOfficiels'].elements['Pub'].value = pub;
	document.forms['formOfficiels'].submit();
}
		
function verrouMatch(idMatch, verrou)
{
	if(!confirm('Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'VerrouMatch';
	document.forms['formOfficiels'].elements['ParamCmd'].value = idMatch;
	document.forms['formOfficiels'].elements['Verrou'].value = verrou;
	document.forms['formOfficiels'].submit();
}
 		
function publiMultiMatchs()
{
	if(!confirm('Publier/Dépublier les matchs sélectionnés. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'PubliMultiMatchs';
	document.forms['formOfficiels'].submit();
}
 		
function verrouMultiMatchs()
{
	if(!confirm('Verrouiller/Déverrouiller les matchs sélectionnés. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'VerrouMultiMatchs';
	document.forms['formOfficiels'].submit();
}

function verrouPubliMultiMatchs()
{
	var matchs = document.forms['formOfficiels'].elements['ParamCmd'].value;
	if(!confirm('Verrouiller & Publier les matchs sélectionnés '+matchs+'. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'VerrouPubliMultiMatchs';
	document.forms['formOfficiels'].submit();
}

function affectMultiMatchs()
{
	if(!confirm('Vous devez avoir recalculé le classement ! \n=> Modifiez manuellement les classements en cas d\'égalité dans les poules. \nConfirmez-vous l\'affectation automatiquement des équipes pour les matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'AffectMultiMatchs';
	document.forms['formOfficiels'].submit();
}

function annulMultiMatchs()
{
	if(!confirm('Etes-vous sûr de vouloir supprimer les équipes et arbitres des matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'AnnulMultiMatchs';
	document.forms['formOfficiels'].submit();
}

function changeMultiMatchs()
{
	var journ = jq('#comboJournee').val();
	if(journ == '*')
	{
		alert('Selectionnez une journee / une phase / une poule !');
		jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		return false;
	}
	if(!confirm('Etes-vous sûr de vouloir changer la journée / la phase / la poule des matchs sélectionnés ?'))
	{
		return false;
	}
	document.forms['formOfficiels'].elements['Cmd'].value = 'ChangeMultiMatchs';
	document.forms['formOfficiels'].submit();
}


// ****************************************************************************************************

jq(document).ready(function() { //Jquery + NoConflict='J'

	//sessionJournee
	//ajax
	var journ = jq('#comboJournee').val();
	jq.get("Autocompl_session_journee.php", {
		j: journ
	//},  function(data) {
	//	alert(data);
	});
	jq('#comboJournee').change(function(){
		var journ = jq('#comboJournee').val();
		jq.get("Autocompl_session_journee.php", {
			j: journ
		//},  function(data) {
		//	alert(data);
		});
//		alert(journ+' !');
	});
	
	
	// AUTOCOMPLETE ARBITRES
	jq("#arbitre1").focus(function() {
		var journ = jq('#comboJournee').val();
		if(journ == '*')
		{
			//alert('Selectionnez une journee / une phase !');
			jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		}
	});
	jq("#arbitre1").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#arbitre1").result(function(event, data, formatted) {
		if (data) {
			if(data[1] == 'XXX')
			{
				jq("#arbitre1_matric").val('');
				jq("#arbitre1").val('');
			}
			else
			{
				if(data[4] != '')
					var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
				else
					var nomArb = data[2]+' '+data[3]+' '+data[5];
				jq("#arbitre1_matric").val(data[1]);
				jq("#arbitre1").val(nomArb);
			}
		}
	});

	jq("#arbitre2").focus(function() {
		var journ = jq('#comboJournee').val();
		if(journ == '*')
		{
			//alert('Selectionnez une journee / une phase !');
			jq('#comboJournee').fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).focus();
		}
	});
	jq("#arbitre2").autocomplete('Autocompl_arb.php', {
		width: 320,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#arbitre2").result(function(event, data, formatted) {
		if (data) {
			if(data[1] == 'XXX')
			{
				jq("#arbitre2_matric").val('');
				jq("#arbitre2").val('');
			}
			else
			{
				if(data[4] != '')
					var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
				else
					var nomArb = data[2]+' '+data[3]+' '+data[5];
				jq("#arbitre2_matric").val(data[1]);
				jq("#arbitre2").val(nomArb);
			}
		}
	});
	
	// Maskedinput
	jq(".champsHeure").mask("99:99");
	jq('.date').mask("99/99/9999");

	//Recherches arbitres
	jq('#iframeRechercheLicenceIndi2').hide();
	jq('#rechercheArbitre1').click(function(e){
		//jq('#numeroChamps').val('1');
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre1_matric&zoneIdentite=arbitre1');
		jq('#iframeRechercheLicenceIndi2').toggle();
	});
	jq('#rechercheArbitre2').click(function(e){
		//jq('#numeroChamps').val('2');
		jq('#iframeRechercheLicenceIndi2').attr('src', 'RechercheLicenceIndi2.php?zoneMatric=arbitre2_matric&zoneIdentite=arbitre2');
		jq('#iframeRechercheLicenceIndi2').toggle();
	});

	//Init Titulaires
	jq('#InitTitulaireCompet').click(function(e){
		e.preventDefault();
		var champs = 'Compet';
		var valeur = jq('#comboCompet').val();
		var valeur2 = jq('#comboCompet option:selected').text();
		if(valeur == '*'){
			alert('Sélectionnez une compétition ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	jq('#InitTitulaireEquipeA').click(function(e){
		e.preventDefault();
		var champs = 'Equipe';
		var valeur = jq('#equipeA').val();
		var valeur2 = jq('#equipeA option:selected').text();
		if(valeur == '-1'){
			alert('Sélectionnez une équipe ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	jq('#InitTitulaireJournee').click(function(e){
		e.preventDefault();
		var champs = 'Journee';
		var valeur = jq('#comboJournee').val();
		var valeur2 = jq('#comboJournee option:selected').text();
		if(valeur == '*'){
			alert('Sélectionnez une journée/phase ci-dessous !');
		}else{
			initTitu(champs, valeur, valeur2);
		}
	});
	jq('#InitTitulaireEquipeB').click(function(e){
		e.preventDefault();
		var champs = 'Equipe';
		var valeur = jq('#equipeB').val();
		var valeur2 = jq('#equipeB option:selected').text();
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
			jq.post("InitTitulaireJQ.php", {
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
	jq('.directInput').attr('title','Cliquez pour modifier');
	jq('.pbArb').attr('title','Arbitre non identifié');
	jq('.undefTeam').attr('title','Equipe non définie : rechargez la page pour actualiser l\'encodage.');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableMatchs').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	
	// focus sur un span du tableau => remplace le span par un input
	jq("body").delegate("#tableMatchs td span.directInput", "focus", function(event){
	//jq("body").on("focus", "#tableMatchs td > span.directInput", function(event){
		event.preventDefault();
		jq('#inputZone2annul').click();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		if(jq(this).hasClass('text'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="12" value="'+valeur+'">');
		}
		else if(jq(this).hasClass('numMatch'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="1" value="'+valeur+'">');
		}
		else if(jq(this).hasClass('date'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="8" value="'+valeur+'">');
			jq('#inputZone').mask("99/99/9999");
		}
		else if(jq(this).hasClass('heure'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="4" value="'+valeur+'">');
			jq('#inputZone').mask("99:99");
		}
		else if(jq(this).hasClass('terrain'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		else if(jq(this).hasClass('score'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		else if(jq(this).hasClass('equipe'))
		{
			jq(this).before('<select id="selectZone" class="directInputSpan" tabindex="'+tabindexVal+'"></select>');
			jq(this).before('<br /><input type="button" id="selectZoneAnnul" value="Annuler">');
			datamatch = jq(this).attr('data-match');
			dataidEquipe = jq(this).attr('data-idequipe');
			dataequipe = jq(this).attr('data-equipe');
			datajournee = jq(this).attr('data-journee');
			jq.post(
				'v2/getEquipesMatch.php', // Le fichier cible côté serveur.
				{
					idMatch : datamatch,	// variables transmises
					idJournee : datajournee,
				},
				function(data){ // callback
					if(data){
						 for(var key in data) {
							if(data[key].Id == dataidEquipe){
								jq('#selectZone').append('<option value="'+data[key].Id+'" selected="selected">'+data[key].Libelle+'</option>');
							}else{
								jq('#selectZone').append('<option value="'+data[key].Id+'">'+data[key].Libelle+'</option>');
							}
						}
					}
				},
				'json' // Format des données reçues.
			);
			jq('#selectZone').change(function(){
				jq('#selectZoneAnnul').remove();
			});
			jq('#selectZoneAnnul').click(function(){
				jq('#selectZone ~ span').show();
				jq('#selectZone + br').remove();
				jq('#selectZoneAnnul').remove();
				jq('#selectZone').remove();
			});
			jq('#selectZone').blur(function(){
				newIdEquipe = jq(this).val();
				newEquipe = jq('#selectZone option:selected').text();
				if(newIdEquipe != dataidEquipe){
					jq.post(
						'v2/setEquipesMatch.php', // Le fichier cible côté serveur.
						{
							idMatch : datamatch,	// variables transmises
							idEquipe : newIdEquipe,
							equipe : dataequipe
						},
						function(data){ // callback
							if(data){
								jq('#selectZone ~ span').attr('data-idequipe', newIdEquipe).text(newEquipe).show();
								if(newIdEquipe == '0'){
									jq('#selectZone ~ span').addClass('undefTeam').attr('title','Equipe non définie : rechargez la page pour actualiser l\'encodage.');
								}else{
									jq('#selectZone ~ span').removeClass('undefTeam').attr('title','Cliquez pour modifier');
								}
								jq('#selectZone + br').remove();
								jq('#selectZoneAnnul').remove();
								jq('#selectZone').remove();
							}
						},
						'text' // Format des données reçues.
					);
				}else{
					jq('#selectZone ~ span').show();
					jq('#selectZone + br').remove();
					jq('#selectZoneAnnul').remove();
					jq('#selectZone').remove();
				}
			});
		}
		else if(jq(this).hasClass('arbitre'))
		{
			jq(this).before('<input type="text" id="inputZone2" class="directInputSpan" tabindex="'+tabindexVal+'" size="22" value="'+valeur+'">');
			jq(this).before('<br>\n\
                            <input type="button" id="inputZone2valid" data-value2="0" value="valider">\n\
                            <input type="button" id="inputZone2annul" value="Annuler">\n\
                            <input type="button" id="inputZone2vid" data-value2="0" value="vider">');
			datamatch = jq(this).attr('data-match');
			datajournee = jq(this).attr('data-journee');
			dataid = jq(this).attr('data-id');
			// AUTOCOMPLETE ARBITRES
			jq("#inputZone2").autocomplete('Autocompl_arb.php', {
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
			jq("#inputZone2").result(function(event, data, formatted) {
				if (data) {
					if(data[1] == 'XXX') {
						jq("#inputZone2").val('');
						jq("#inputZone2valid").attr('data-match', datamatch);
						jq("#inputZone2valid").attr('data-id', dataid);
						jq("#inputZone2valid").attr('data-value', '');
						jq("#inputZone2valid").attr('data-value2', 0);
					} else {
						if(data[4] != '')
							var nomArb = data[2]+' '+data[3]+' ('+data[4]+') '+data[5];
						else
							var nomArb = data[2]+' '+data[3]+' '+data[5];
						jq("#inputZone2valid").attr('data-match', datamatch);
						jq("#inputZone2valid").attr('data-id', dataid);
						jq("#inputZone2valid").attr('data-value', nomArb);
						jq("#inputZone2valid").attr('data-value2', data[1]);
						jq("#inputZone2").val(nomArb);
					}
				}
			});
			
		}
		jq(this).hide();
		setTimeout( function() { 
			jq('#selectZone').select();
			jq('#inputZone').select();
			jq('#inputZone2').select();
		}, 0 );
	});
	
	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function(){
		var Classe = jq(this).attr('class');
		validationDonnee(Classe);
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
		lavaleur = jq(this).attr('data-value');
		lavaleur2 = jq(this).attr('data-value2');
		lavaleur3 = lavaleur + '|' + lavaleur2;
		lidMatch = jq(this).attr('data-match');
		lid = jq(this).attr('data-id');
		jq.post(
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
					jq('#inputZone2vid ~ span').html(lavaleur);
					if(lavaleur2 == 0){
						jq('#inputZone2vid ~ span').addClass('pbArb').attr('title','Arbitre non identifié');
					}else{
						jq('#inputZone2vid ~ span').removeClass('pbArb').attr('title','Cliquez pour modifier');
					}
					//compléter format(retour ligne, contrôle valeur n°arbitre)
					jq('#inputZone2vid ~ span').show();
					jq('#inputZone2 + br').remove();
					jq('#inputZone2').remove();
					jq('#inputZone2valid').remove();
					jq('#inputZone2annul').remove();
					jq('#inputZone2vid').remove();
				}
			},
			'text' // Format des données reçues.
		);
	});
	jq('#inputZone2vid').live('click', function(event){
		event.preventDefault;
		lavaleur = '';
		lavaleur2 = 0;
		lavaleur3 = lavaleur + '|' + lavaleur2;
		lidMatch = datamatch;
		lid = dataid;
		jq.post(
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
					jq('#inputZone2vid ~ span').html(lavaleur);
					if(lavaleur2 == 0){
						jq('#inputZone2vid ~ span').addClass('pbArb').attr('title','Arbitre non identifié');
					}else{
						jq('#inputZone2vid ~ span').removeClass('pbArb').attr('title','Cliquez pour modifier');
					}
					//compléter format(retour ligne, contrôle valeur n°arbitre)
					jq('#inputZone2vid ~ span').show();
					jq('#inputZone2 + br').remove();
					jq('#inputZone2').remove();
					jq('#inputZone2valid').remove();
					jq('#inputZone2annul').remove();
					jq('#inputZone2vid').remove();
				}
			},
			'text' // Format des données reçues.
		);
	});
	function validationDonnee(Classe){
		var nouvelleValeur = jq('#inputZone').val();
		var tabindexVal = jq('#inputZone').attr('tabindex');
		if(Classe == 'directInputSpan'){
			jq('#inputZone + span').attr('tabindex',tabindexVal);
		}else if(Classe == 'directInputTd'){
			jq('#inputZone').parent('td').attr('tabindex',tabindexVal);
		}
		jq('#inputZone + span').show();
		var valeur = jq('#inputZone + span').text();
		var identifiant = jq('#inputZone + span').attr('id');
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
			var AjaxWhere = jq('#AjaxWhere').val();
			var AjaxTableName = jq('#AjaxTableName').val();
			var AjaxAnd = '';
			var AjaxUser = jq('#AjaxUser').val();
			jq.get("UpdateCellJQ.php",
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
						jq('#'+identifiant).text(nouvelleValeur);
					}
				}
			);
		};
		jq('#inputZone').remove();
	}
	function validationDonnee2(){
		var nouvelleValeur = jq('#inputZone2').val();
		var tabindexVal = jq('#inputZone2').attr('tabindex');
		jq('#inputZone2 + span').attr('tabindex',tabindexVal);
		jq('#inputZone2 + span').show();
		var valeur = jq('#inputZone2 + span').text();
		var identifiant = jq('#inputZone2 + span').attr('id');
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
			var AjaxWhere = jq('#AjaxWhere').val();
			var AjaxTableName = jq('#AjaxTableName').val();
			var AjaxAnd = '';
			var AjaxUser = jq('#AjaxUser').val();
			
/*			jq.get("UpdateCellJQ.php",
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
						jq('#'+identifiant).text(nouvelleValeur);
					}
				}
			);
*/
						jq('#'+identifiant).text(nouvelleValeur);
						jq('#'+identifiant).attr('data-idArb', jq('#inputZone2').attr('data-idArb'));

		};
		jq('#inputZone2').remove();
	}

	
	
	//Affiche, masque formulaire
	jq('#clickdown').toggle();
	jq('#clickup').click(function() {
		jq('.hideTr').toggle();
		jq('#clickdown').toggle();
	});
	jq('#clickdown').click(function() {
		jq('.hideTr').toggle();
		jq('#clickdown').toggle();
	});
	if(jq('#Num_match').val() == ''){
		jq('#clickup').click();
	}
	
	//Surligne l'événement filtré
	if(jq('#evenement').val() != '-1'){
		jq('#evenement').addClass('highlight4');
	}
	//Surligne la competition filtrée
	if(jq('#comboCompet').val() != '*'){
		jq('td>span.compet').addClass('highlight3');
		jq('#comboCompet').addClass('highlight3');
	}
	//Surligne la phase, le lieu filtrés
	if(jq('#comboJournee2').val() != '*'){
		jq('td>span.phase').addClass('highlight3');
		jq('td>span.lieu').addClass('highlight3');
		jq('#comboJournee2').addClass('highlight3');
	}
	//Surligne la date filtrée
	if(jq('#filtreJour').val() != ''){
		jq('td>span.date').addClass('highlight3');
		jq('#filtreJour').addClass('highlight3');
	}
	//Surligne le terrain filtré
	if(jq('#filtreTerrain').val() != ''){
		jq('td>span.terrain').addClass('highlight3');
		jq('#filtreTerrain').addClass('highlight3');
	}
	
	// Highlight
    jq('#reach').bind('keyup change', function(ev) {
        // pull in the new value
        var searchTerm = jq(this).val();
        // remove any old highlighted terms
        jq('.tableau').removeHighlight();
        // disable highlighting if empty
        if ( searchTerm ) {
            // highlight the new term
            jq('.tableau').highlight( searchTerm );
        }
    });

	jq("body").delegate(".typeMatch", "click", function(){
	//jq("body").on("click", ".typeMatch", function(){
		//if(confirm('Confirmez-vous le changement de statut ?')){
			leMatch = jq(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'C'){
				changeType = 'E';
				textType = 'Elimination';
			}else{
				changeType = 'C';
				textType = 'Classement';
			}
			jq.post(
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
	jq("#typeMatch1").click(function(){
		if(jq("#Type").val() == 'C'){
			jq("#Type").val("E");
			jq("#typeMatch1").attr("src","../img/typeE.png").attr("alt","Elimination").attr("title", "Match éliminatoire");
		}else{
			jq("#Type").val("C");
			jq("#typeMatch1").attr("src","../img/typeC.png").attr("alt","Classement").attr("title", "Match de classement");
		}
	});
	jq("#comboJournee").change(function(){
		loption = jq(this).val();
		leType = jq("#comboJournee option[value=" + loption + "]").attr('data-type');
		if(leType == 'E'){
			jq("#Type").val("E");
			jq("#typeMatch1").attr("src","../img/typeE.png").attr("alt","Elimination").attr("title", "Match éliminatoire");
		}else{
			jq("#Type").val("C");
			jq("#typeMatch1").attr("src","../img/typeC.png").attr("alt","Classement").attr("title", "Match de classement");
		}
	});
	jq(".publiMatch").click(function(){
		//if(confirm('Confirmez-vous le changement de publication ?')){
			leMatch = jq(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non public';
			}else{
				changeType = 'O';
				textType = 'Public';
			}
			jq.post(
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
	jq(".verrouMatch").click(function(){
		//if(confirm('Confirmez-vous le changement de publication ?')){
			leMatch = jq(this);
			leMatch.attr('src', 'v2/images/indicator.gif');
			if(leMatch.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non validé (score non public)';
			}else{
				changeType = 'O';
				textType = 'Validé / verrouillé (score public)';
			}
			jq.post(
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


