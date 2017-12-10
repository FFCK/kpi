jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Aucun_joueur'] = 'No player selected. unable to add !';
    langue['Certif'] = '(Med. Certificat)';
    langue['Cliquez_pour_modifier'] = 'Click to edit';
    langue['Joueur_vide'] = 'Player is empty. unable to add !';
    langue['MAJ_impossible'] = 'Unable to update';
    langue['Pagaie_couleur'] = '(Paddle level)';
    langue['Prenom_vide'] = 'Player first name is empty. unable to add !';
    langue['Saison_licence'] = '(Licence year)';
    langue['Surclassement'] = '(Mandatory upgrade)';
} else {
    langue['Aucun_joueur'] = 'Aucun joueur sélectionné, ajout impossible !';
    langue['Certif'] = '(Certificat CK)';
    langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier';
    langue['Joueur_vide'] = 'Joueur vide, ajout impossible !';
    langue['MAJ_impossible'] = 'Mise à jour impossible';
    langue['Pagaie_couleur'] = '(Pagaie couleur)';
    langue['Prenom_vide'] = 'Prénom joueur vide, ajout impossible!';
    langue['Saison_licence'] = '(Saison licence)';
    langue['Surclassement'] = '(Surclassement obligatoire)';
}


function validJoueur()
{
		var nomJoueur = jq('#nomJoueur').val();
		if (nomJoueur.length == 0)
		{
			alert(langue['Joueur_vide']);
			return false;
		}
		var prenomJoueur = jq('#prenomJoueur').val();
		if (prenomJoueur.length == 0)
		{
			alert(langue['Prenom_vide']);
			return false;
		}
		return true;
}

function validJoueur2()
{
		var nomJoueur2 = jq('#nomJoueur2').val();
		if (nomJoueur2.length == 0)
		{
			alert(langue['Prenom_vide']);
			return false;
		}
		return true;
}

function Add()
{
	if (!validJoueur())
		return;
	jq('#Cmd').val('Add');
	jq('#ParamCmd').val('');
	jq('#formEquipeJoueur').submit();
}

function Add2()
{
	if (!validJoueur2())
		return;
	jq('#Cmd').val('Add2');
	jq('#ParamCmd').val('');
	jq('#formEquipeJoueur').submit();
}

function AddCoureur(matric, categ)
{
	jq('#Cmd').val('AddCoureur');
	jq('#ParamCmd').val(matric + '|' + categ);
	jq('#formEquipeJoueur').submit();
}

function Find()
{
	jq('#Cmd').val('Find');
	jq('#ParamCmd').val('');
	jq('#formEquipeJoueur').submit();
}



jq(document).ready(function() { //Jquery + NoConflict='J'

	// Maskedinput
	jq(".champsHeure").mask("99:99");

	// Direct Input (numero joueur)
	// Ajout title
	jq('.directInput').attr('title', langue['Cliquez_pour_modifier']);
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
		var numEquipe = identifiant2[2];
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
					AjId2: numEquipe,
					AjUser: AjaxUser,
					AjOk: 'OK'
				},
				function(data){
					if(data != 'OK!'){
						alert(langue['MAJ_impossible'] + data);
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
	
	// Validation directSelect
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
						alert(langue['MAJ_impossible'] + data);
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
	
	jq('#irregularite').hide();
    jq('#addEquipeJoueurImpossible').hide();
    
	jq("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	jq("#choixJoueur").result(function(event, data, formatted) {
		var saisonCompet = jq('#saisonCompet').val();
		var typeCompet = jq('#typeCompet').val();
		if (data) {
			jq("#matricJoueur2").val(data[1]);
			jq("#nomJoueur2").val(data[2]);
			jq("#prenomJoueur2").val(data[3]);
			jq("#naissanceJoueur2").val(data[4]);
			jq("#sexeJoueur2").val(data[5]);
            catJoueurs2 = calculCategorie(data[4], saisonCompet);
			jq("#categJoueur2").val(catJoueurs2);
            jq("#categJoueur3").text('Cat: ' + catJoueurs2);
            surclassement = data[13];
            if(surclassement != ''){
                jq(".surclassement3").html('<b>Surcl: ' + surclassement + '</b>');
            }else if(catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'){
                jq(".surclassement3").html('Pas de surclassement');    
            }
            jq("#origineJoueur2").text(data[8]);
            jq("#pagaieJoueur2").text(data[9]);
            jq("#CKJoueur2").text(data[10]);
            jq("#APSJoueur2").text(data[11]);
            jq("#catJoueur2").text(catJoueurs2);
			if(typeCompet == 'CH' || typeCompet == 'CF' || typeCompet == 'MC'){
                var surcl_necess = jq('#surcl_necess').val();
				var motif = '';
				if(data[8] < saisonCompet){
					motif = langue['Saison_licence'];
				}else if(data[10] != 'OUI'){
					motif = langue['Certif'] ;
				}else if(data[9] == '' || data[9] == 'PAGB' || data[9] == 'PAGJ'){
					motif = langue['Pagaie_couleur'];
				}else if(surclassement == '' && surcl_necess == 1 && catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'){
                    motif = langue['Surclassement'];
                }
				if (motif != ''){
					jq('#motif').text(motif);
					jq('#irregularite').show();
					jq('#addEquipeJoueur2').hide();
					jq('#addEquipeJoueurImpossible').show();
				}else{
					jq('#motif').text(motif);
					jq('#irregularite').hide();
					jq('#addEquipeJoueur2').show();
					jq('#addEquipeJoueurImpossible').hide();
				}
				//Autoriser pagaie différente pour arbitres et entraineurs... ?
				
				
			}
		}
	});

	// Actualiser
	jq('#actuButton').click(function(){
		jq('#formEquipeJoueur').submit();
	});

    jq('#changeEquipe').change(function(){
    	jq(location).attr('href', "?idEquipe=" + jq(this).val());
    });

});

