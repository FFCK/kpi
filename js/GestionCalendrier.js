$(document).ready(function() {
	$("#evenement").change(function(){
		$("#competition").val('*');
		$("#formCalendrier").submit();
	});	$(".typeJournee").click(function(){
		//if(confirm('Confirmez-vous le changement de statut ?')){
			laJournee = $(this);
			laJournee.attr('src', 'v2/images/indicator.gif');
			if(laJournee.attr('data-valeur') == 'C'){
				changeType = 'E';
				textType = 'Elimination';
			}else{
				changeType = 'C';
				textType = 'Classement';
			}			$.post(
				'v2/StatutJournee.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Journee : laJournee.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Type'
				},
				function(data){ // callback
					if(data == 'OK'){
						laJournee.attr('src', '../img/type' + changeType + '.png');
						laJournee.attr('data-valeur', changeType);
						laJournee.attr('title', textType);
					}
					else{
						custom_alert('Changement impossible', 'Attention');
						laJournee.attr('src', '../img/type' + laJournee.attr('data-valeur') + '.png');
						laJournee.attr('data-valeur', laJournee.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
		//}
	});	$(".publiJournee").click(function(){
		//if(confirm('Confirmez-vous le changement de publication ?')){
			laJournee = $(this);
			laJournee.attr('src', 'v2/images/indicator.gif');
			if(laJournee.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non public';
			}else{
				changeType = 'O';
				textType = 'Public';
			}
			$.post(
				'v2/StatutJournee.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Journee : laJournee.attr('data-id'),
					Valeur : changeType,
					TypeUpdate : 'Publication'
				},
				function(data){ // callback
					if(data == 'OK'){
						laJournee.attr('src', '../img/oeil2' + changeType + '.gif');
						laJournee.attr('data-valeur', changeType);
						laJournee.attr('title', textType);
					}
					else{
						custom_alert('Changement impossible', 'Attention');
						laJournee.attr('src', '../img/oeil2' + laJournee.attr('data-valeur') + '.gif');
						laJournee.attr('data-valeur', laJournee.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
		//}
	});
	
	$(".checkassoc2").click(function(event){
			event.preventDefault();
			var laJournee = $(this);
			var idJournee = laJournee.attr('data-id');
			var idEvenement = $('#evenement').val();
			var statut = laJournee.attr('checked');
			laJournee.after('<img src="v2/images/indicator.gif" />').hide();
			
				
			//alert(statut);
	/*		
			laSaison = $('#saisonTravail').val();
			if(statut == '0' || statut == 'ATT'){
				changeType = 'ON';
			}else if(statut == 'ON'){
				changeType = 'END';
			}else{
				changeType = 'ATT';
			}
	*/
			$.post(
				'v2/setEvenementJournee.php', // Le fichier cible côté serveur.
				{ // variables
					Id_Evenement : idEvenement,
					Id_Journee : idJournee,
					Valeur : statut,
				},
				function(data){ // callback
					if(data == 'OK'){
						laJournee.show().attr('checked',statut).next().remove();
					}
					else{
						laJournee.show().next().remove();
						alert('Changement impossible <br />'+data);
					}
				},
				'text' // Format des données reçues.
			);		
	});
	
});

function changeCompetition()
{
	document.forms['formCalendrier'].submit();
}

function changeCompetitionOrder()
{
	document.forms['formCalendrier'].submit();
}

function changeEvenement()
{
}

function changeModeEvenement()
{
	document.forms['formCalendrier'].submit();
}

function ParamJournee(idJournee)
{
	if (idJournee == 0)
	{
		var compet = document.forms['formCalendrier'].elements['competition'].value;
		if (compet == '*')
		{
			alert('Sélectionnez une compétition');
			return;
		}
	}
	
	document.forms['formCalendrier'].elements['Cmd'].value = 'ParamJournee';
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee;
	document.forms['formCalendrier'].submit();
}

function ClickEvenementJournee(idJournee)
{
	var obj = document.getElementById('checkEvenementJournee'+idJournee);
	if (obj == null)
		return;
		
	if (obj.checked)
		document.forms['formCalendrier'].elements['Cmd'].value = 'AddEvenementJournee';
	else
		document.forms['formCalendrier'].elements['Cmd'].value = 'RemoveEvenementJournee';
		
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee;
	document.forms['formCalendrier'].submit();
}

/*
function publiJournee(idJournee, pub)
{
	if(!confirm('Confirmez-vous le changement ?'))
		return false;
		
	document.forms['formCalendrier'].elements['Cmd'].value = 'PubliJournee';
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee;
	document.forms['formCalendrier'].elements['Pub'].value = pub;
	document.forms['formCalendrier'].submit();
}
*/

function publiMultiJournees()
{
	if(!confirm('Publier/Dépublier les journées/phases sélectionnées. Confirmez-vous le changement ?'))
	{
		return false;
	}
	document.forms['formCalendrier'].elements['Cmd'].value = 'PubliMultiJournees';
	document.forms['formCalendrier'].submit();
}
 		
function duplicate(idJournee)
{
	if(!confirm('Confirmez-vous la copie ?'))
		return false;
		
	document.forms['formCalendrier'].elements['Cmd'].value = 'Duplicate';
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee;
	document.forms['formCalendrier'].submit();
}


	
	