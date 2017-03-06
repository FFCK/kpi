jq = jQuery.noConflict();

function changeCompetition()
{
	jq("#ParamCmd").val('');
	jq("#formEquipe").submit();
}

function changeComiteReg()
{
	jq("#ParamCmd").val('changeComiteReg');
	jq("#formEquipe").submit();
}

function changeComiteDep()
{
	jq("#ParamCmd").val('changeComiteDep');
	jq("#formEquipe").submit();
}

function changeClub()
{
	jq("#ParamCmd").val('changeClub');
	jq("#formEquipe").submit();
}

function validEquipe()	
{
	var histoEquipe = jq("#histoEquipe").val();
	  
	if ( (histoEquipe.length > 0) && (histoEquipe[0] != '0') )
		return true; // Une Equipe de l'historique est sélectionnée ...

	var libelleEquipe = jq("#libelleEquipe").val();
	if (histoEquipe[0] == '0' && libelleEquipe.length == 0)
	{
		alert("Le Nom de l'Equipe est Vide ..., Ajout nouvelle équipe impossible !");
		return false;
	}

	var competition = jq("#competition").val();
	if (competition == '')
	{
		alert("Sélectionnez une compétition !");
		return false;
	}

	var codeClub = jq("#club").val();
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
	var EquipeNum = jq("#EquipeNum").val();
	  
	if ( (EquipeNum.length > 0) && (EquipeNum[0] != '0') )
		return true; // Une Equipe est sélectionnée ...

	var libelleEquipe = jq("#EquipeNom").val();
	if (libelleEquipe.length == 0)
	{
		alert("Recherchez une équipe !");
		return false;
	}

	var competition = jq("#competition").val();
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
	jq("#Cmd").val('Add');
	jq("#ParamCmd").val('');
	jq("#formEquipe").submit();
}

function Add2()
{
	if (!validEquipe2())
		return;
	jq("#Cmd").val('Add2');
	jq("#ParamCmd").val('');
	jq("#formEquipe").submit();
}

function Tirage()
{
	jq("#Cmd").val('Tirage');
	jq("#ParamCmd").val('');
	jq("#formEquipe").submit();
}

function dupliEquipe()
{
	if (confirm("Voulez-vous Dupliquer les Equipes ?")) 
	{
		jq("#Cmd").val('Duplicate');
		jq("#ParamCmd").val('');
		jq("#formEquipe").submit();
	}
}
		
function removeanddupliEquipe()
{
	if (confirm("Voulez-vous Supprimer puis Dupliquer les Equipes ?")) 
 	{
		jq("#Cmd").val('RemoveAndDuplicate');
		jq("#ParamCmd").val('');
		jq("#formEquipe").submit();
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
function initTitu(champs, valeur, valeur2)
{
    
}

jq(document).ready(function() {
	//Init Titulaires
	jq('#InitTitulaireCompet').click(function(){
		var champs = 'Compet';
		var valeur = jq('#competition').val();
		var valeur2 = jq('#competition option:selected').text();
		if(valeur == '*'){
			alert('Sélectionnez une compétition !');
            return;
		}
        if(jq('#verrouCompet').attr('data-verrou') == 'N') {
            alert('Verrouillez les feuilles de présence avant ! (cadenas rouge, à côté)');
            return;
        }
        if(!confirm('Confirmez-vous la mise à jour des feuilles de matchs\navec les compositions des feuilles de présence ?\n(toutes les équipes, matchs non verrouillés uniquement)\n'+champs+' : '+valeur2))
        {
            return;
        }
        //ajax
        jq.post("InitTitulaireJQ.php", {
            champs: champs,
            valeur: valeur,
            valeur3: -1
        }, function(data) {
            alert(data);
        });
	});

	//Init Titulaires
	jq('#verrouCompet').click(function(){
        if(!confirm('Confirmez-vous le verrouillage des feuilles de présence ?')) {
            return;
        }
        //ajax
        jq.post("VerrouCompetJQ.php", {
            verrou: jq('#verrouCompet').attr('data-verrou'),
            compet: jq('#competition').val(),
        }, function(data) {
            if(data == 'O' || data == 'N'){
                jq('#verrouCompet').attr('src', '../img/verrou2'+data+'.gif')
                                  .attr('data-verrou', data);
            }else{
                alert(data);
            }
        });
	});

    jq.extend(jq.expr[':'], {
	  'icontains': function(elem, i, match, array) {
		return (elem.textContent || elem.innerText || '').toLowerCase()
			.indexOf((match[3] || "").toLowerCase()) >= 0;
	  }
	});

	jq("#filtreText").keyup(function(){
		var str = jq(this).val();
		jq("#histoEquipe option")
			.hide()
			.filter(':icontains("' + str + '")')
			.show();
	});
	jq("#filtreTextButton").click(function(){
		var str = jq("#filtreText").val();
		jq("#histoEquipe option").show();
		if(str != '')
		{
			jq("#histoEquipe option").hide();
			jq("#histoEquipe option:icontains('"+str+"')").show();
		}
	});
	jq("#filtreAnnulButton").click(function(){
		jq("#filtreText").val('');
		jq("#histoEquipe option").show();
	});

	// Actualiser
	jq('#actuButton').click(function(){
		jq('#formEquipe').submit();
	});
	
	
	// Maskedinput
	jq.mask.definitions['h'] = "[A-O]";
	//jq("#inputZone").mask("9");
	
	
	// Direct Input (date, heure, intitule)
	//Ajout title
	jq('.directInput').attr('title','Cliquez pour modifier, puis tabulation pour passer à la valeur suivante. Lettre A à O pour les poules, nombre 0 à 99 pour le tirage');
	// contrôle touche entrée (valide les données en cours mais pas le formulaire)
	jq('#tableEquipes').bind('keydown',function(e){
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
	// focus sur un span du tableau => remplace le span par un input
	jq('#tableEquipes td > span.directInput').focus(function(event){
		event.preventDefault();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		if(jq(this).hasClass('textPoule'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			jq('#inputZone').mask("h",{placeholder:" "});
		}
		else if(jq(this).hasClass('textTirage'))
		{
			jq(this).before('<input type="text" id="inputZone" class="directInputSpan" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			jq('#inputZone').mask("9?9",{placeholder:" "});
		}
		jq(this).hide();
		setTimeout( function() { jq('#inputZone').select() }, 0 );
	});
	// focus sur une cellule du tableau => remplace le span par un input
	jq('#tableEquipes td.directInput').focus(function(event){
		event.preventDefault();
		var valeur = jq(this).text();
		var tabindexVal = jq(this).attr('tabindex');
		jq(this).attr('tabindex',tabindexVal+1000);
		if(jq(this).hasClass('textPoule'))
		{
			jq(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			jq('#inputZone').mask("h",{placeholder:" "});
		}
		else if(jq(this).hasClass('textTirage'))
		{
			jq(this).prepend('<input type="text" id="inputZone" class="directInputTd" tabindex="'+tabindexVal+'" size="2" value="'+valeur+'">');
			jq('#inputZone').mask("9?9",{placeholder:" "});
		}
		jq(this).children("span").hide();
		setTimeout( function() { jq('#inputZone').select() }, 0 );
		
	});
	// Validation des données 
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

	//Autocomplete recherche equipe
	jq('#plEquipe').mask("h",{placeholder:" "});
	jq('#tirEquipe').mask("9?9",{placeholder:" "});
	jq('#cltChEquipe').mask("9?9",{placeholder:" "});
	jq('#cltCpEquipe').mask("9?9",{placeholder:" "});
	jq('#ShowCompo').hide();	
	jq("#choixEquipe").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	jq("#choixEquipe").result(function(event, data, formatted) {
		if (data) {
			var lequipe = data[1];
			var lasaison = jq("#Saison").val();
			jq("#EquipeNom").val(data[0]);
			jq('#EquipeNum').val(lequipe);
			jq('#EquipeNumero').val(lequipe);
            jq('#ShowCompo').show();	
            jq.get("Autocompl_getCompo.php", { q: lequipe, s: lasaison }).done(function(data2) {
                jq('#GetCompo').html( data2 );//"REPRISE DES COMPOSITIONS D'EQUIPE:<br>"
            });
		}
	});
	jq("#annulEquipe2").click(function(){
		jq('#ShowCompo').hide();
		jq('#plEquipe').val('');
		jq('#tirEquipe').val('');
		jq('#cltChEquipe').val('');
		jq('#cltCpEquipe').val('');
	});
	

});

	