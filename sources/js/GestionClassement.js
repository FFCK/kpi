jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Confirmer'] = 'Confirm ?';
    langue['MAJ_impossible'] = 'Unable to update';
    langue['Match_de_classement'] = 'Classifying game';
    langue['Match_eliminatoire'] = 'Playoffs';
    langue['Non_valide'] = 'Unvalidated (private score)';
    langue['Rien_a_transferer'] = 'Nothing to transfer !';
    langue['Selection_competition_cible'] = 'Select a target competition !';
    langue['Selection_saison_cible'] = 'Select a target season !';
    langue['Suppression_classement'] = 'Remove of public ranking ?';
    langue['Valider'] = 'Valid';
    langue['Valide'] = 'Validated, locked (public score)';
} else {
    langue['Confirmer'] = 'Confirmer ?';
    langue['MAJ_impossible'] = 'Mise à jour impossible';
    langue['Match_de_classement'] = 'Match de classement';
    langue['Match_eliminatoire'] = 'Match éliminatoire';
    langue['Non_valide'] = 'Non validé (score non public)';
    langue['Rien_a_transferer'] = 'Rien à transférer !';
    langue['Selection_competition_cible'] = 'Sélectionner une compétition cible !';
    langue['Selection_saison_cible'] = 'Sélectionner une saison cible !';
    langue['Suppression_classement'] = 'Suppression du classement public ?';
    langue['Valider'] = 'Valider';
    langue['Valide'] = 'Validé / verrouillé (score public)';
}


function changeCompetition()
{
	document.forms['formClassement'].elements['Cmd'].value = '';
	document.forms['formClassement'].elements['ParamCmd'].value = 'changeCompetition';
	document.forms['formClassement'].submit();
}

function changeSaisonTransfert()
{
	document.forms['formClassement'].elements['Cmd'].value = '';
	document.forms['formClassement'].elements['ParamCmd'].value = 'changeSaisonTransfert';
	document.forms['formClassement'].submit();
}

function changeOrderCompetition()
{
	document.forms['formClassement'].elements['Cmd'].value = '';
	document.forms['formClassement'].elements['ParamCmd'].value = 'changeOrderCompetition';
	document.forms['formClassement'].submit();
}

function computeClt()
{
	document.forms['formClassement'].elements['Cmd'].value = 'DoClassement';
	document.forms['formClassement'].elements['ParamCmd'].value = '';
	document.forms['formClassement'].submit();
}

function initClt()
{
	document.forms['formClassement'].elements['Cmd'].value = 'InitClassement';
	document.forms['formClassement'].elements['ParamCmd'].value = '';
	document.forms['formClassement'].submit();
}

function publicationClt()
{
	if (!confirm(langue['Confirmer']))
		return false;

	document.forms['formClassement'].elements['Cmd'].value = 'PublicationClassement';
	document.forms['formClassement'].elements['ParamCmd'].value = '';
	document.forms['formClassement'].submit();
}

function depublicationClt()
{
	if (!confirm(langue['Suppression_classement']))
		return false;

	if (!confirm(langue['Confirmer']))
		return false;

	document.forms['formClassement'].elements['Cmd'].value = 'DePublicationClassement';
	document.forms['formClassement'].elements['ParamCmd'].value = '';
	document.forms['formClassement'].submit();
}

// Transfert des Equipes ...
function transfert()
{
	// Verification qu'il y a bien des Equipes &agrave;  Transf&eacute;rer ...
	var elts = document.forms['formClassement'].elements['checkClassement'];
	var elts_count = (typeof(elts.length) != 'undefined') ? elts.length : 0;

	var str = '';
	if (elts_count) {
		for (var i = 0; i < elts_count; i++) 
		{
			if (elts[i].checked)
			{
				if (str.length > 0)
					str += ',';
			
				str += elts[i].value;
			}
		} 
	} else {
		str = elts.value;
	}
	  
	if (str.length == 0) {
		alert(langue['Rien_a_transferer']);
		return false;
	}
	
	// Verification qu'une comp&eacute;tition est choisie ainsi qu'une saison ...
	var codeCompetTransfert = jq('#codeCompetTransfert option:selected').val();
	if (codeCompetTransfert.length == 0) {
		alert(langue['Selection_competition_cible']);
		return false;
	}
	var codeSaisonTransfert = jq('#codeSaisonTransfert option:selected').val();
	if (codeSaisonTransfert.length == 0) {
		alert(langue['Selection_saison_cible']);
		return false;
	}

	if (!confirm(langue['Confirmer']))
		return false;

	document.forms['formClassement'].elements['Cmd'].value = 'Transfert';
	document.forms['formClassement'].elements['ParamCmd'].value = str;
	document.forms['formClassement'].submit();

	return true;
}
		
function sessionSaison()
{
	if(!confirm(langue['Confirmer'])) {
		document.forms['formClassement'].reset;
		return;
	} else {
		document.forms['formClassement'].elements['Cmd'].value = 'SessionSaison';
		document.forms['formClassement'].elements['ParamCmd'].value = document.forms['formClassement'].elements['saisonTravail'].value;
		document.forms['formClassement'].submit();
	}
}

jq(document).ready(function() { //Jquery

	document.querySelector('table.tableau').addEventListener('input', function(event) {
		if (event.target.matches('input[type="tel"]')) {
			// Supprime tous les caractères non numériques et non traits d'union
			event.target.value = event.target.value.replace(/[^\d-]/g, '');
		}
	});

	// Actualiser
	jq('#actuButton').click(function(){
		jq('#formClassement').submit();
	});
	// contr&ocirc;le touche entr&eacute;e (valide les donn&eacute;es en cours mais pas le formulaire)
	jq('.tableauJQ').bind('keydown',function(e){
		if(e.which == 13) {
			validationDonnee();
			return false;
		}
	}); 
	// blur d'une input => validation de la donn&eacute;e
	jq('#inputZone').live('blur', function(){
		validationDonnee();
	});
	// focus sur un lien du tableau => remplace le lien par un input
	jq('.directInput').focus(function(event){
		event.preventDefault();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		jq(this).before('<input type="tel" id="inputZone" class="champsPoints" tabindex="'+tabindexVal+'" size="1" maxlength="3" value="'+valeur+'">');
		jq(this).hide();
		setTimeout( function() { jq('#inputZone').select() }, 0 );
	});
	
	function validationDonnee(){
		var nouvelleValeur = jq('#inputZone').val();
		var tabindexVal = jq('#inputZone').attr('tabindex');
		jq('#inputZone + span').attr('tabindex',tabindexVal);
		jq('#inputZone + span').show();
		var valeur = jq('#inputZone + span').text();
		var identifiant = jq('#inputZone + span').attr('id');
		var identifiant2 = identifiant.split('-');
		var typeValeur = identifiant2[0];
		var numEquipe = identifiant2[1];
		if(typeof identifiant2[2] != 'undefined') {
			var numJournee = identifiant2[2];
		} else {
			var numJournee = '';
		}

		var diviseur = 1;
		if(typeValeur == 'Pts') {
			nouvelleValeur = nouvelleValeur * 100;
			valeur = valeur * 100;
			diviseur = 100;
		}
		if (valeur != nouvelleValeur && nouvelleValeur != '') {
			var AjaxWhere = jq('#AjaxWhere').val();
			var AjaxUser = jq('#AjaxUser').val();
			if(numJournee != '') {
				var AjaxTableName = jq('#AjaxTableName2').val();
				var AjaxAnd = jq('#AjaxAnd').val();
			} else {
				var AjaxTableName = jq('#AjaxTableName').val();
				var AjaxAnd = '';
			}
			console.log(numJournee);
			jq.get("UpdateCellJQ.php",
				{
					AjTableName: AjaxTableName,
					AjWhere: AjaxWhere,
					AjTypeValeur: typeValeur,
					AjValeur: nouvelleValeur,
					AjAnd: AjaxAnd,
					AjId: numEquipe,
					AjId2: numJournee,
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function(data) {
					if(data != 'OK!') {
						alert(langue['MAJ_impossible'] + ' : ' + data);
					} else {
						jq('#'+identifiant).text(nouvelleValeur/diviseur);
					}
				}
			);
		};
		jq('#inputZone').remove();
	}

	jq(".statutCompet").click(function() {
			laCompet = jq(this);
			statut = laCompet.text();
			laCompet.html('<img src="v2/images/indicator.gif" height="23">');
			laSaison = jq('#saisonTravail').val();
			if(statut == '0' || statut == 'ATT') {
				changeType = 'ON';
			} else if(statut == 'ON') {
				changeType = 'END';
			} else {
				changeType = 'ATT';
			}
			jq.post(
				'v2/StatutCompet.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Compet : laCompet.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Statut',
					idSaison : laSaison
				},
				function(data) { // callback
					if(data == 'OK'){
						laCompet.html(changeType);
						laCompet.removeClass('statutCompetATT statutCompetON statutCompetEND').addClass('statutCompet' + changeType);
					} else {
						laCompet.html(statut);
						alert(langue['MAJ_impossible'] + ' : ' + data);
					}
				},
				'text' // Format des données reçues.
			);
			
	});
    
    jq(".dropTeam").click(function(e) {
        e.preventDefault();
        if(confirm(langue['Confirmer'])) {
            jq('#Cmd').val('DropTeam');
            jq('#ParamCmd').val(jq(this).data('Id_journee') + ';' + jq(this).data('Id'));
            jq('#formClassement').submit();
        }
    });
	
});

