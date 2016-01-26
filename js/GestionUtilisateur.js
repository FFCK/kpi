function validUser()
{
	var user = document.forms['formUser'].elements['guser'].value;
	if (user.length == 0)
	{
		alert("Le code Utilisateur est Vide ..., Ajout Impossible !");
		return false;
	}
	var niveau = document.forms['formUser'].elements['gniveau'].value;
	var limitclub = document.forms['formUser'].elements['limitclub'].value;
	var filtre_competition = document.forms['formUser'].elements['filtre_competition'].value;
	if ((niveau == '7' || niveau == '8') && limitclub == '')
	{
		alert("Pour les profils 7 et 8, la limite club ne peut être vide !");
		return false;
	}
	if (niveau >= 3)
	{
		if(!confirm("Pour les profils superieurs à 2, filtrez les compétitions et les saisons ! Confirmer tout de même ?"))
			return false;
	}
	return true;
}

function Add()
{
	if (!validUser())
		return;

	document.forms['formUser'].elements['Cmd'].value = 'Add';
	document.forms['formUser'].elements['ParamCmd'].value = '';
	document.forms['formUser'].submit();
}

function Update()
{
	if (!validUser())
		return;

	document.forms['formUser'].elements['Cmd'].value = 'Update';
	document.forms['formUser'].elements['ParamCmd'].value = '';
	document.forms['formUser'].submit();
}

function Raz()
{
	document.forms['formUser'].elements['Cmd'].value = '';
	document.forms['formUser'].elements['Action'].value = '';
	document.forms['formUser'].elements['ParamCmd'].value = '';
	document.forms['formUser'].submit();
}

function changeTypeFiltreCompetition()
{
	document.forms['formUser'].submit();
}

function changeSaison()
{
	var comboSaison = document.forms['formUser'].elements['comboSaison[]'];

	var txt = "";
	var nbSel = 0;
	for (i=0;i<comboSaison.length; i++)
	{
		if (comboSaison[i].selected)
		{
			++nbSel;
			txt += "|";
			txt += comboSaison.options[i].value;
		}              
	}      
	document.forms['formUser'].submit();
}

function updateUser(code)
{
	document.forms['formUser'].elements['Cmd'].value = 'Edit';
	document.forms['formUser'].elements['ParamCmd'].value = code;
	document.forms['formUser'].submit();
}
		
		
/*		function rechercheLicenceUtilisateur()	// Prototype remplacé par Jquery
		{
			if ($('iframeRechercheLicenceIndi').getStyle('visibility') == 'visible')
			{
				alert("Recherche d'utilisateur déja en cours ...");
				return;
			}
	 		$('iframeRechercheLicenceIndi').src = 'RechercheLicenceIndi.php?zoneMatric=guser&zoneIdentite=gidentite';
			var v = $('gidentite');
 			var p = v.viewportOffset();
			var posy = p[1];
 	 		$('iframeRechercheLicenceIndi').setStyle({ left:'250px', top:posy+'px' });
 	 		$('iframeRechercheLicenceIndi').setStyle({ visibility:'visible' });
		}
*/

$(document).ready(function() { //Jquery + NoConflict='J'

	$('#iframeRechercheLicenceIndi2').hide();
	// Recherche utilisateur
	$('#rechercheUtilisateur').click(function(e){
		$('#iframeRechercheLicenceIndi2').toggle();
	});

	$("#choixJoueur").autocomplete('Autocompl_joueur.php', {
		width: 550,
		max: 50,
		mustMatch: true,
	});
	$("#choixJoueur").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			$("#guser").val(data[1]);
			$("#gidentite").val(nom);
		}
	});
	
	//Affiche, masque formulaire
	$('#tabledown').hide();
	var act = $('#Action').val();
	if(act != 'Update')
	{
		$('#tabledown').toggle();
		$('#tableup').toggle();
	}
	$('#clickup').click(function() {
		$('#tabledown').toggle();
		$('#tableup').toggle();
	});
	$('#clickdown').click(function() {
		$('#tabledown').toggle();
		$('#tableup').toggle();
	});

	// Highlight
    $('#reach').bind('keyup change', function(ev) {
        // pull in the new value
        var searchTerm = $(this).val();
        // remove any old highlighted terms
        $('.tableau').removeHighlight();
        // disable highlighting if empty
        if ( searchTerm ) {
            // highlight the new term
            $('.tableau').highlight( searchTerm );
			$('.tableau thead').removeHighlight();
        }
    });
	
	$('#msgStandard').click(function(){
		var texte = "\n************************************************************************\n";
		texte += "Vous devez obligatoirement faire figurer dans les feuilles de présence de votre équipe sur KPI tous les compétiteurs qui seront amenés ";
		texte += "à participer à au moins un match de la prochaine journée de Championnat de France, ou de la Coupe de France.\n";
		texte += "\n";
		texte += "Pour que KPI accepte l\’ajout d\’un joueur sur la feuille de présence, le joueur doit obligatoirement être en possession :\n";
		texte += "- de la licence 2015 valide\n";
		texte += "- de la pagaie couleur eau calme verte minimum\n";
		texte += "- du certificat Compétition (CK) valide\n";
		texte += "- des autorisations éventuelles de surclassement\n";
		texte += "\n";
		texte += "Les feuilles de présence sur KPI doivent impérativement être saisies ET mises à jour au plus tard 7 jours avant chaque journée de Championnat de France ";
		texte += "et de Coupe de France.\n";
		texte += "\n";
		texte += "AUCUN JOUEUR NON INSCRIT PRÉALABLEMENT NE POURRA PARTICIPER AUX MATCHS !\n";
		texte += "\n";
		texte += "Les feuilles de présence doivent également indiquer les entraîneurs et arbitres non joueurs accompagnant l\'équipe sur les compétitions.\n";
		texte += "\n";
		texte += "Contactez votre responsable de Championnat ou de Coupe pour toute difficulté.\n";
		texte += "\n";
		texte += "Tutoriel : http://www.kayak-polo.info/?page_id=987 \n";
		texte += "\n************************************************************************";
		$('#message_complementaire').text(texte);
	});
	
});

