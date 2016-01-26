function DuppliListJournees()
{
	document.forms['formParamJournee'].elements['Cmd'].value = 'DuppliListJournees';
	var valeur = document.forms['formParamJournee'].elements['checkListJournees'];
	var tmp="";
	for (var i=0; i < valeur.length; i++)
	{   
		if( valeur[i].checked )
		{
			if( tmp )
				tmp += ",";
		    tmp += valeur[i].value;
		}
	}
	document.forms['formParamJournee'].elements['ParamCmd'].value = tmp;
	document.forms['formParamJournee'].submit();

}
		
function AjustDates()
{
	document.forms['formParamJournee'].elements['Cmd'].value = 'AjustDates';
	document.forms['formParamJournee'].elements['ParamCmd'].value = '';
	document.forms['formParamJournee'].submit();
}
		
function Ok()
{
	document.forms['formParamJournee'].elements['Cmd'].value = 'Ok';
	document.forms['formParamJournee'].elements['ParamCmd'].value = '';
	document.forms['formParamJournee'].submit();
}
		
function Duppli()
{
	if(confirm("Créer une nouvelle journée ?"))
	{
		document.forms['formParamJournee'].elements['Cmd'].value = 'Ok';
		document.forms['formParamJournee'].elements['ParamCmd'].value = '';
		document.forms['formParamJournee'].elements['dupliThis'].value = 'Duppli';
		
		var meme = 0
		if(document.forms['formParamJournee'].elements['PrevSaison'].value == document.forms['formParamJournee'].elements['J_saison'].value)
		{
			meme = 1
		}
		if(document.forms['formParamJournee'].elements['PrevCompetition'].value == document.forms['formParamJournee'].elements['J_competition'].value)
		{
			meme = 1
		}
		if(document.forms['formParamJournee'].elements['PrevDate'].value == document.forms['formParamJournee'].elements['Date_debut'].value)
		{
			meme = 1
		}
		if(meme == 1)
		{
			if(confirm("Vous copiez cette journée sur une saison, une compétition ou une date identique. Continuer ?"))
			{
				document.forms['formParamJournee'].submit();
			}
		}
		else
		{
			document.forms['formParamJournee'].submit();
		}
	}
}

function Cancel()
{
	document.forms['formParamJournee'].elements['Cmd'].value = 'Cancel';
	document.forms['formParamJournee'].elements['ParamCmd'].value = '';
	document.forms['formParamJournee'].submit();
}

function Duplicate()
{
	document.forms['formParamJournee'].elements['Cmd'].value = 'Duplicate';
	document.forms['formParamJournee'].elements['ParamCmd'].value = '';
	document.forms['formParamJournee'].submit();
}
$(document).ready(function() {

	// Maskedinput
	//$.mask.definitions['h'] = "[A-O]";
	//$('#Departement').mask("999");
	$('.dpt').mask("?***");
	$('.date').mask("99/99/9999");

	$("#Lieu").autocomplete('Autocompl_ville.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Lieu").result(function(event, data, formatted) {
		if (data) {
			$("#Lieu").val(data[1]);
			$("#Departement").val(data[2]);
		}
	});
	$("#Nom").autocomplete('Autocompl_refJournee.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Nom").result(function(event, data, formatted) {
		if (data) {
			var nom = data[1];
			$("#Nom").val(nom);
		}
	});
	$("#Organisateur").autocomplete('Autocompl_club.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Organisateur").result(function(event, data, formatted) {
		if (data) {
			var nom = data[1];
			$("#Organisateur").val(nom);
		}
	});
	$("#Responsable_R1").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Responsable_R1").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			$("#Responsable_R1").val(nom);
		}
	});
	$("#Responsable_insc").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Responsable_insc").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			$("#Responsable_insc").val(nom);
		}
	});
	$("#Delegue").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#Delegue").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			$("#Delegue").val(nom);
		}
	});
	$("#ChefArbitre").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	$("#ChefArbitre").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			$("#ChefArbitre").val(nom);
		}
	});
	
});