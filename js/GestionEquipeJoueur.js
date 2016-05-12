function validJoueur()
{
		var nomJoueur = $('#nomJoueur').val();
		if (nomJoueur.length == 0)
		{
			alert("Le Nom du Joueur est Vide... Ajout Impossible !");
			return false;
		}
		var prenomJoueur = $('#prenomJoueur').val();
		if (prenomJoueur.length == 0)
		{
			alert("Le prénom du Joueur est Vide... Ajout Impossible !");
			return false;
		}
		return true;
}

function validJoueur2()
{
		var nomJoueur2 = $('#nomJoueur2').val();
		if (nomJoueur2.length == 0)
		{
			alert("Aucun joueur sélectionné, Ajout Impossible !");
			return false;
		}
		return true;
}

function Add()
{
	if (!validJoueur())
		return;
	$('#Cmd').val('Add');
	$('#ParamCmd').val('');
	$('#formEquipeJoueur').submit();
}

function Add2()
{
	if (!validJoueur2())
		return;
	$('#Cmd').val('Add2');
	$('#ParamCmd').val('');
	$('#formEquipeJoueur').submit();
}

function AddCoureur(matric, categ)
{
	$('#Cmd').val('AddCoureur');
	$('#ParamCmd').val(matric + '|' + categ);
	$('#formEquipeJoueur').submit();
}

function Find()
{
	$('#Cmd').val('Find');
	$('#ParamCmd').val('');
	$('#formEquipeJoueur').submit();
}

$(document).ready(function() { //Jquery + NoConflict='J'

	// Maskedinput
	$(".champsHeure").mask("99:99");

	// Direct Input (numero joueur)
	// Ajout title
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
		var numEquipe = identifiant2[2];
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
					AjId2: numEquipe,
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
	
	// Validation directSelect
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
	
	$('#irregularite').hide();
    $('#addEquipeJoueurImpossible').hide();
    
	$("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	$("#choixJoueur").result(function(event, data, formatted) {
		var saisonCompet = $('#saisonCompet').val();
		var typeCompet = $('#typeCompet').val();
		if (data) {
			$("#matricJoueur2").val(data[1]);
			$("#nomJoueur2").val(data[2]);
			$("#prenomJoueur2").val(data[3]);
			$("#naissanceJoueur2").val(data[4]);
			$("#sexeJoueur2").val(data[5]);
            catJoueurs2 = calculCategorie(data[4], saisonCompet);
			$("#categJoueur2").val(catJoueurs2);
            $("#categJoueur3").text('Cat: ' + catJoueurs2);
            surclassement = data[13];
            if(surclassement != ''){
                $(".surclassement3").html('<b>Surcl: ' + surclassement + '</b>');
            }else if(catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'){
                $(".surclassement3").html('Pas de surclassement');    
            }
            $("#origineJoueur2").text(data[8]);
            $("#pagaieJoueur2").text(data[9]);
            $("#CKJoueur2").text(data[10]);
            $("#APSJoueur2").text(data[11]);
            $("#catJoueur2").text(catJoueurs2);
			if(typeCompet == 'CH' || typeCompet == 'CF' || typeCompet == 'MC'){
                var surcl_necess = $('#surcl_necess').val();
				var motif = '';
				if(data[8] < saisonCompet){
					motif = '(Saison licence)';
				}else if(data[10] != 'OUI'){
					motif = '(Certificat CK)';
				}else if(data[9] == '' || data[9] == 'PAGB' || data[9] == 'PAGJ'){
					motif = '(Pagaie couleur)';
				}else if(surclassement == '' && surcl_necess == 1 && catJoueurs2 != 'JUN' && catJoueurs2 != 'SEN'){
                    motif = '(Surclassement obligatoire)';
                }
				if (motif != ''){
					$('#motif').text(motif);
					$('#irregularite').show();
					$('#addEquipeJoueur2').hide();
					$('#addEquipeJoueurImpossible').show();
				}else{
					$('#motif').text(motif);
					$('#irregularite').hide();
					$('#addEquipeJoueur2').show();
					$('#addEquipeJoueurImpossible').hide();
				}
				//Autoriser pagaie différente pour arbitres et entraineurs... ?
				
				
			}
		}
	});

	// Actualiser
	$('#actuButton').click(function(){
		$('#formEquipeJoueur').submit();
	});

});

function changeEquipe(){
	$(location).attr('href', "?idEquipe=" + $('#idEquipe').val());
}
