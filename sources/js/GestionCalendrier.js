jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Cliquez_pour_modifier'] = 'Click to edit';
    langue['Confirm_delete'] = 'Delete selected phases ?';
    langue['Confirm_update'] = 'Confirm update ?';
    langue['Confirm_dupplicate'] = 'Confirm dupplicate ?';
    langue['MAJ_impossible'] = 'Unable to update';
    langue['Selection_competition'] = 'Select a competition !';
} else {
    langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier';
    langue['Confirm_delete'] = 'Supprimer les journées/phases sélectionnées ?';
    langue['Confirm_update'] = 'Confirmer le changement ?';
    langue['Confirm_dupplicate'] = 'Confirmez-vous la copie ?';
    langue['MAJ_impossible'] = 'Mise à jour impossible';
    langue['Selection_competition'] = 'Sélectionner une compétition !';
}


jq(document).ready(function() {
	jq("#evenement").change(function(){
		jq("#competition").val('*');
		jq("#formCalendrier").submit();
	});
    
	jq(".typeJournee").click(function(){
			laJournee = jq(this);
			laJournee.attr('src', 'v2/images/indicator.gif');
			if(laJournee.attr('data-valeur') == 'C'){
				changeType = 'E';
				textType = 'Elimination';
			}else{
				changeType = 'C';
				textType = 'Classement';
			}
			jq.post(
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
					} else {
						alert(langue['MAJ_impossible']);
						laJournee.attr('src', '../img/type' + laJournee.attr('data-valeur') + '.png');
						laJournee.attr('data-valeur', laJournee.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
	});
    
	jq(".publiJournee").click(function(){
			laJournee = jq(this);
			laJournee.attr('src', 'v2/images/indicator.gif');
			if(laJournee.attr('data-valeur') == 'O'){
				changeType = 'N';
				textType = 'Non public';
			}else{
				changeType = 'O';
				textType = 'Public';
			}
			jq.post(
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
						custom_alert(langue['MAJ_impossible'], 'Attention');
						laJournee.attr('src', '../img/oeil2' + laJournee.attr('data-valeur') + '.gif');
						laJournee.attr('data-valeur', laJournee.attr('data-valeur'));
					}
				},
				'text' // Format des données reçues.
			);
	});
	
	jq(".checkassoc2").click(function(event){
			event.preventDefault();
			var laJournee = jq(this);
			var idJournee = laJournee.attr('data-id');
			var idEvenement = jq('#evenement').val();
			var statut = laJournee.attr('checked');
			laJournee.after('<img src="v2/images/indicator.gif"  height="23">').hide();
			
			jq.post(
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
						alert(langue['MAJ_impossible'] + ' <br />'+data);
					}
				},
				'text' // Format des données reçues.
			);		
	});
    
    jq('.directInput').attr('title',langue['Cliquez_pour_modifier']);
    
    jq("body").delegate("span.directInput", "click", function(event){
		event.preventDefault();
        var valeur = jq(this).text();
        var typeChamps = jq(this).attr('data-type');
        switch(typeChamps) {
            case 'text':
                jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="7" data-anciennevaleur="'+valeur+'" value="'+valeur+'">');
                break;
            case 'smalltext':
                jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="3" data-anciennevaleur="'+valeur+'" value="'+valeur+'">');
                break;
            case 'tel':
                jq(this).before('<input type="tel" id="inputZone" class="directInputSpan" size="1" data-anciennevaleur="'+valeur+'" value="'+valeur+'">');
                break;
            case 'date':
                jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="8" value="'+valeur+'" >');
                jq('#inputZone').mask("9999-99-99");
                break;
        }
        jq(this).hide();
		setTimeout( function() { 
			jq('#inputZone').select();
		}, 0 );
    });
    
    jq('#inputZone').live('keydown',function(e){
		if(e.which == 13) {
			jq(this).blur();
			return false;
		}
	}); 
    
    jq('#inputZone').live('blur', function(){
        var thisSpan = jq('#inputZone + span');
        var nouvelleValeur = jq(this).val();
        var typeChamps = jq(this).attr('type');
        var valeurEntier = nouvelleValeur | 0;
        if(typeChamps == 'tel' && valeurEntier < 1){
            jq(this).focus().css('border', '1px solid red');
        } else {
            if(nouvelleValeur != jq(this).attr('data-anciennevaleur')){
                var AjaxWhere = jq('#AjaxWhere').val();
                var AjaxTableName = jq('#AjaxTableName').val();
                var AjaxAnd = '';
                var AjaxUser = jq('#AjaxUser').val();
                var numJournee = thisSpan.attr('data-id');
                var typeValeur = thisSpan.attr('data-target');
                jq.get("UpdateCellJQ.php",
                    {
                        AjTableName: AjaxTableName,
                        AjWhere: AjaxWhere,
                        AjTypeValeur: typeValeur,
                        AjValeur: nouvelleValeur,
                        AjAnd: AjaxAnd,
                        AjId: numJournee,
                        AjId2: '',
                        AjUser: AjaxUser,
                        AjOk: 'OK'
                    },
                    function(data){
                        if(data != 'OK!'){
                            alert(langue['MAJ_impossible'] + ' : ' + data);
                        }else{
                            thisSpan.text(nouvelleValeur);
                        }
                    }
                );
            }
            thisSpan.show();
            jq(this).remove();
        }
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
			alert(langue['Selection_competition']);
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

function publiMultiJournees()
{
	if(!confirm(langue['Confirm_update']))
	{
		return false;
	}
	document.forms['formCalendrier'].elements['Cmd'].value = 'PubliMultiJournees';
	document.forms['formCalendrier'].submit();
}
 		
function duplicate(idJournee)
{
	if(!confirm(langue['Confirm_dupplicate']))
		return false;
		
	document.forms['formCalendrier'].elements['Cmd'].value = 'Duplicate';
	document.forms['formCalendrier'].elements['ParamCmd'].value = idJournee;
	document.forms['formCalendrier'].submit();
}


	
	