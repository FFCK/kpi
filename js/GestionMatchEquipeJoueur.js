jq = jQuery.noConflict();

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

jq(document).ready(function() { //Jquery + NoConflict='J'

	// Maskedinput
	jq(".champsHeure").mask("99:99");

	// Direct Input (numero joueur)
	//Ajout title
	jq('.directInput').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableMatchs').bind('keydown',function(e){
		if(e.which == 13)
		{
			validationDonnee();
			return false;
		}
	}); 
	// blur d'une input => validation de la donnée
	jq('#inputZone').live('blur', function(){
		var Classe = jq(this).attr('class');
		validationDonnee(Classe);
	});
	// focus sur une cellule du tableau => remplace le span par un input
	jq('#tableMatchs td.directInput').focus(function(event){
		event.preventDefault();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		if(jq(this).hasClass('text'))
		{
			jq(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
		}
		jq(this).children("span").hide();
		setTimeout( function() { jq('#inputZone').select() }, 0 );
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
		var numJoueur = identifiant2[1];
		var numMatch = identifiant2[2];
		if(valeur != nouvelleValeur){
			valeurTransmise = nouvelleValeur;
			var AjaxWhere = jq('#AjaxWhere').val();
			var AjaxTableName = jq('#AjaxTableName').val();
			var AjaxAnd = jq('#AjaxAnd').val();;
			var AjaxUser = jq('#AjaxUser').val();
			jq.get("UpdateCellJQ.php",
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
						jq('#'+identifiant).text(nouvelleValeur);
					}
				}
			);
		};
		jq('#inputZone').remove();
	}
	
	// directSelect (Capitaine)
	jq('#directSelecteur').hide();
	jq('.directSelect').click(function(e){
		posX = e.pageX - 10;
		posY = e.pageY - 15;
		jq('#directSelecteur').css('left', posX +'px');
		jq('#directSelecteur').css('top', posY +'px');
		jq('#directSelecteur').toggle();
		var valeur = jq(this).children('span').text();
		var variables = jq(this).children('span').attr('id');
		jq(this).attr('id', 'directSelected');
		jq('#variables').val(variables);
		jq('#directSelecteurSelect option').removeAttr('selected');
		jq('#directSelecteurSelect option').each(function(){
			if(jq(this).val() == valeur){
				jq(this).attr('selected','selected');
			};
		});
	});
	
	// Validation
	jq('#directSelecteurSelect').change(function(){
		var variables = jq('#variables').val();
		var variables = variables.split('-');
		var typeValeur = variables[0];
		var numJoueur = variables[1];
		var numEquipe = variables[2];
		var nouvelleValeur = jq('#directSelecteurSelect option:selected').val();
			valeurTransmise = nouvelleValeur;
			var AjaxWhere = jq('#AjaxWhere').val();
			var AjaxTableName = jq('#AjaxTableName').val();
			var AjaxAnd = jq('#AjaxAnd').val();;
			var AjaxUser = jq('#AjaxUser').val();
			jq.get("UpdateCellJQ.php",
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
						jq('#directSelected').removeAttr('id');
						jq('#directSelecteur').toggle();
					}else{
						jq('#directSelected').children('span').text(valeurTransmise);
						jq('#directSelected').removeAttr('id');
						jq('#directSelecteur').toggle();
					}
				}
			);
	});
	jq('#annulButton').click(function(){
		jq('#directSelected').removeAttr('id');
		jq('#directSelecteur').toggle();
	});
	
	jq("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	jq("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			jq("#matricJoueur2").val(data[1]);
			jq("#nomJoueur2").val(data[2]);
			jq("#prenomJoueur2").val(data[3]);
			jq("#naissanceJoueur2").val(data[4]);
			jq("#sexeJoueur2").val(data[5]);
		}
	});

	
});

		

