function changeCompetition()
{
	$("#ParamCmd").val('');
	$("#formEquipe").submit();
}

function changeComiteReg()
{
	$("#ParamCmd").val('changeComiteReg');
	$("#formEquipe").submit();
}

function changeComiteDep()
{
	$("#ParamCmd").val('changeComiteDep');
	$("#formEquipe").submit();
}

function changeClub()
{
	$("#ParamCmd").val('changeClub');
	$("#formEquipe").submit();
}

function validEquipe()	
{
	var histoEquipe = $("#histoEquipe").val();
	  
	if ( (histoEquipe.length > 0) && (histoEquipe[0] != '0') )
		return true; // Une Equipe de l'historique est sélectionnée ...

	var libelleEquipe = $("#libelleEquipe").val();
	if (histoEquipe[0] == '0' && libelleEquipe.length == 0)
	{
		alert("Le Nom de l'Equipe est Vide ..., Ajout nouvelle équipe impossible !");
		return false;
	}

	var competition = $("#competition").val();
	if (competition == '')
	{
		alert("Sélectionnez une compétition !");
		return false;
	}

	var codeClub = $("#club").val();
	if (histoEquipe[0] == '0' &&  codeClub.length > 0 && codeClub != '*' )
		return true; // Le Code du Club est bon ...
	if	(codeClub == '*')
		alert("Le Club n'est pas renseigné ..., Ajout nouvelle équipe impossible ! Sélectionnez un CD/PAYS et un CLUB !");
	if	(histoEquipe[0] != '0')
		alert("Sélectionnez NOUVELLE EQUIPE");
	return false;
}

function validEquipe2()	
{
	var EquipeNum = $("#EquipeNum").val();
	  
	if ( (EquipeNum.length > 0) && (EquipeNum[0] != '0') )
		return true; // Une Equipe est sélectionnée ...

	var libelleEquipe = $("#EquipeNom").val();
	if (libelleEquipe.length == 0)
	{
		alert("Recherchez une équipe !");
		return false;
	}

	var competition = $("#competition").val();
	if (competition == '')
	{
		alert("Sélectionnez une compétition !");
		return false;
	}
}

function Add()
{
	if (!validEquipe())
		return;
	$("#Cmd").val('Add');
	$("#ParamCmd").val('');
	$("#formEquipe").submit();
}

function Add2()
{
	if (!validEquipe2())
		return;
	$("#Cmd").val('Add2');
	$("#ParamCmd").val('');
	$("#formEquipe").submit();
}

function Tirage()
{
	$("#Cmd").val('Tirage');
	$("#ParamCmd").val('');
	$("#formEquipe").submit();
}

function dupliEquipe()
{
	if (confirm("Voulez-vous Dupliquer les Equipes ?")) 
	{
		$("#Cmd").val('Duplicate');
		$("#ParamCmd").val('');
		$("#formEquipe").submit();
	}
}
		
function removeanddupliEquipe()
{
	if (confirm("Voulez-vous Supprimer puis Dupliquer les Equipes ?")) 
 	{
		$("#Cmd").val('RemoveAndDuplicate');
		$("#ParamCmd").val('');
		$("#formEquipe").submit();
  	}
}
		
function changeHistoEquipe()
{
		var combo = document.forms['formEquipe'].elements['histoEquipe'];
		var data = combo.options[combo.selectedIndex].value;
		
		if (data == '0')
			document.forms['formEquipe'].elements['libelleEquipe'].disabled = false;
		else
			document.forms['formEquipe'].elements['libelleEquipe'].disabled = true;
}

//jQuery.expr[':'].icontains = function(a, i, m) {
//	return jQuery(a).text().toUpperCase()
//		.indexOf(m[3].toUpperCase()) >= 0;
//};


$(document).ready(function() {
	$.extend($.expr[':'], {
	  'icontains': function(elem, i, match, array) {
		return (elem.textContent || elem.innerText || '').toLowerCase()
			.indexOf((match[3] || "").toLowerCase()) >= 0;
	  }
	});

	$("#filtreText").keyup(function(){
		var str = $(this).val();
		$("#histoEquipe option")
			.hide()
			.filter(':icontains("' + str + '")')
			.show();
	});
	$("#filtreTextButton").click(function(){
		var str = $("#filtreText").val();
		$("#histoEquipe option").show();
		if(str != '')
		{
			$("#histoEquipe option").hide();
			$("#histoEquipe option:icontains('"+str+"')").show();
		}
	});
	$("#filtreAnnulButton").click(function(){
		$("#filtreText").val('');
		$("#histoEquipe option").show();
	});

	// Actualiser
	$('#actuButton').click(function(){
		$('#formEquipe').submit();
	});
	
	
	// Maskedinput
	$.mask.definitions['h'] = "[A-O]";
	//$("#inputZone").mask("9");
	
	
	// Direct Input (date, heure, intitule)
	//Ajout title
	$('.directInput').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante. Lettre A à O pour les poules, nombre 0 à 99 pour le tirage');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	$('#tableEquipes').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	// blur d'une input => validation de la donnée
	$('#inputZone').live('blur', function(){
		var Classe = $(this).attr('class');
		validationDonnee(Classe);
	});
	// focus sur un span du tableau => remplace le span par un input
	$('#tableEquipes td > span.directInput').focus(function(event){
		event.preventDefault();
		var valeur = $(this).text();
		var tabindexVal = $(this).attr('tabindex');
		$(this).attr('tabindex',tabindexVal+1000);
		if($(this).hasClass('textPoule'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			$('#inputZone').mask("h",{placeholder:" "});
		}
		else if($(this).hasClass('textTirage'))
		{
			$(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			$('#inputZone').mask("9?9",{placeholder:" "});
		}
		$(this).hide();
		setTimeout( function() { $('#inputZone').select() }, 0 );
	});
	// focus sur une cellule du tableau => remplace le span par un input
	$('#tableEquipes td.directInput').focus(function(event){
		event.preventDefault();
		var valeur = $(this).text();
		var tabindexVal = $(this).attr('tabindex');
		$(this).attr('tabindex',tabindexVal+1000);
		if($(this).hasClass('textPoule'))
		{
			$(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			$('#inputZone').mask("h",{placeholder:" "});
		}
		else if($(this).hasClass('textTirage'))
		{
			$(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			$('#inputZone').mask("9?9",{placeholder:" "});
		}
		$(this).children("span").hide();
		setTimeout( function() { $('#inputZone').select() }, 0 );
		
	});
	// Validation des données 
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

	//Autocomplete recherche equipe
	$('#plEquipe').mask("h",{placeholder:" "});
	$('#tirEquipe').mask("9?9",{placeholder:" "});
	$('#cltChEquipe').mask("9?9",{placeholder:" "});
	$('#cltCpEquipe').mask("9?9",{placeholder:" "});
	$('#ShowCompo').hide();	
	$("#choixEquipe").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	$("#choixEquipe").result(function(event, data, formatted) {
		if (data) {
			var lequipe = data[1];
			var lasaison = $("#Saison").val();
			$("#EquipeNom").val(data[0]);
			$('#EquipeNum').val(lequipe);
			$('#EquipeNumero').val(lequipe);
			$('#ShowCompo').show();	
			$.get("Autocompl_getCompo.php", { q: lequipe, s: lasaison }).done(function(data2) {
				$('#GetCompo').html( data2 );//"REPRISE DES COMPOSITIONS D'EQUIPE:<br>"&
			});
			//, function(data2) {
			//	}, "json");
		}
	});
	$("#annulEquipe2").click(function(){
		$('#ShowCompo').hide();
		$('#plEquipe').val('');
		$('#tirEquipe').val('');
		$('#cltChEquipe').val('');
		$('#cltCpEquipe').val('');
	});
	

});

	