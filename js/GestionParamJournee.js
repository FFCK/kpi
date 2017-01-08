jq = jQuery.noConflict();

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
jq(document).ready(function() {

	// Maskedinput
	//jq.mask.definitions['h'] = "[A-O]";
	//jq('#Departement').mask("999");
	jq('.dpt').mask("?***");
	jq('.date').mask("99/99/9999");

	jq("#Lieu").autocomplete('Autocompl_ville.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Lieu").result(function(event, data, formatted) {
		if (data) {
			jq("#Lieu").val(data[1]);
			jq("#Departement").val(data[2]);
		}
	});
	jq("#Nom").autocomplete('Autocompl_refJournee.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Nom").result(function(event, data, formatted) {
		if (data) {
			var nom = data[1];
			jq("#Nom").val(nom);
		}
	});
	jq("#Organisateur").autocomplete('Autocompl_club.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Organisateur").result(function(event, data, formatted) {
		if (data) {
			var nom = data[1];
			jq("#Organisateur").val(nom);
		}
	});
	jq("#Responsable_R1").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Responsable_R1").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			jq("#Responsable_R1").val(nom);
		}
	});
	jq("#Responsable_insc").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Responsable_insc").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			jq("#Responsable_insc").val(nom);
		}
	});
	jq("#Delegue").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#Delegue").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			jq("#Delegue").val(nom);
		}
	});
	jq("#ChefArbitre").autocomplete('Autocompl_joueur.php', {
		width: 420,
		max: 80,
		mustMatch: false,
		minChars: 2,
		cacheLength: 1,
		scrollHeight: 320,
	});
	jq("#ChefArbitre").result(function(event, data, formatted) {
		if (data) {
			var nom = data[7]+' '+data[6];
			jq("#ChefArbitre").val(nom);
		}
	});
	
});