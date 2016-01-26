function FindLicence()
{
	document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'FindLicence';
	document.forms['formMatchEquipeJoueur'].submit();
}

function AddJoueurTitulaire()
{
	document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'AddJoueurTitulaire';
	document.forms['formMatchEquipeJoueur'].submit();
}

function validJoueur2()
{
		var nomJoueur2 = document.forms['formMatchEquipeJoueur'].elements['nomJoueur2'].value;
		if (nomJoueur2.length == 0)
		{
			alert("Aucun joueur sélectionné, Ajout Impossible !");
			return false;
		}
				
		return true;
}

function Add2()
{
	if (!validJoueur2())
		return;

	document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'Add2';
	document.forms['formMatchEquipeJoueur'].elements['ParamCmd'].value = '';
	document.forms['formMatchEquipeJoueur'].submit();
}

function DelJoueurs()
{
	document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'DelJoueurs';
	document.forms['formMatchEquipeJoueur'].submit();
}


function CopieCompoEquipeJournee(journee)
{
	if(!confirm('Confirmez-vous la copie de cette composition pour la journée '+journee+' ?'))
	{
		return;
	} else {
		document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'copieCompoEquipeJournee';
		document.forms['formMatchEquipeJoueur'].elements['ParamCmd'].value = journee;
		document.forms['formMatchEquipeJoueur'].submit();
	}
}

function CopieCompoEquipeCompet(journee)
{
	if(!confirm('Confirmez-vous la copie de cette composition pour l\'ensemble de la compétition ?'))
	{
		return;
	} else {
		document.forms['formMatchEquipeJoueur'].elements['Cmd'].value = 'copieCompoEquipeCompet';
		document.forms['formMatchEquipeJoueur'].elements['ParamCmd'].value = journee;
		document.forms['formMatchEquipeJoueur'].submit();
	}
}


/*
function DoNumero(matric, numero)	// Prototype remplacé par Jquery
{
	var obj = document.getElementById("numero"+matric)
	
	var posx = findPosX(obj);
	var posy = findPosY(obj) + 150;

	window.open('GestionEquipeJoueurNumero.php?matric='+matric+'&numero='+numero, 'Numéro', 'left='+posx+', top='+posy+', width=250,height=100,menubar=no,scrollbars=yes,resizable=yes');
}
*/

$(document).ready(function() { //Jquery + NoConflict='J'

	// Maskedinput
	$(".champsHeure").mask("99:99");

	// Direct Input (numero joueur)
	//Ajout title
	$('.directInput').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	$('#tableMatchs').bind('keydown',function(e){
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
	// focus sur une cellule du tableau => remplace le span par un input
	$('#tableMatchs td.directInput').focus(function(event){
		event.preventDefault();
		var valeur = $(this).text();
		var tabindexVal = $(this).attr('tabindex');
		$(this).attr('tabindex',tabindexVal+1000);
		if($(this).hasClass('text'))
		{
			$(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		$(this).children("span").hide();
		setTimeout( function() { $('#inputZone').select() }, 0 );
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
		var numJoueur = identifiant2[1];
		var numMatch = identifiant2[2];
		if(valeur != nouvelleValeur){
			valeurTransmise = nouvelleValeur;
			var AjaxWhere = $('#AjaxWhere').val();
			var AjaxTableName = $('#AjaxTableName').val();
			var AjaxAnd = $('#AjaxAnd').val();;
			var AjaxUser = $('#AjaxUser').val();
			$.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numJoueur,
					AjId2: numMatch,
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
	
	// directSelect (Capitaine)
	$('#directSelecteur').hide();
	$('.directSelect').click(function(e){
		posX = e.pageX - 10;
		posY = e.pageY - 15;
		$('#directSelecteur').css('left', posX +'px');
		$('#directSelecteur').css('top', posY +'px');
		$('#directSelecteur').toggle();
		var valeur = $(this).children('span').text();
		var variables = $(this).children('span').attr('id');
		$(this).attr('id', 'directSelected');
		$('#variables').val(variables);
		$('#directSelecteurSelect option').removeAttr('selected');
		$('#directSelecteurSelect option').each(function(){
			if($(this).val() == valeur){
				$(this).attr('selected','selected');
			};
		});
	});
	
	// Validation
	$('#directSelecteurSelect').change(function(){
		var variables = $('#variables').val();
		var variables = variables.split('-');
		var typeValeur = variables[0];
		var numJoueur = variables[1];
		var numEquipe = variables[2];
		var nouvelleValeur = $('#directSelecteurSelect option:selected').val();
			valeurTransmise = nouvelleValeur;
			var AjaxWhere = $('#AjaxWhere').val();
			var AjaxTableName = $('#AjaxTableName').val();
			var AjaxAnd = $('#AjaxAnd').val();;
			var AjaxUser = $('#AjaxUser').val();
			$.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: valeurTransmise,
					AjAnd: AjaxAnd,
					AjId: numJoueur,
					AjId2: numEquipe,
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function(data){
					if(data != 'OK!'){
						alert('mise à jour impossible : '+data);
						$('#directSelected').removeAttr('id');
						$('#directSelecteur').toggle();
					}else{
						$('#directSelected').children('span').text(valeurTransmise);
						$('#directSelected').removeAttr('id');
						$('#directSelecteur').toggle();
					}
				}
			);
	});
	$('#annulButton').click(function(){
		$('#directSelected').removeAttr('id');
		$('#directSelecteur').toggle();
	});
	
	$("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	$("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			$("#matricJoueur2").val(data[1]);
			$("#nomJoueur2").val(data[2]);
			$("#prenomJoueur2").val(data[3]);
			$("#naissanceJoueur2").val(data[4]);
			$("#sexeJoueur2").val(data[5]);
		}
	});

	
});

		

