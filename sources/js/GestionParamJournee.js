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
		document.forms['formParamJournee'].elements['duppliThis'].value = 'Duppli';
		
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

	// Maskedinput removed - obsolete (dates use Flatpickr, departments use HTML5 pattern)
	//jq.mask.definitions['h'] = "[A-O]";
	//jq('#Departement').mask("999");
	// jq('.dpt').mask("?***");
	// if (lang == 'en') {
	// 	jq('.date').mask("9999-99-99");
	// } else {
	// 	jq('.date').mask("99/99/9999");
	// }

	vanillaAutocomplete('#Lieu', 'Autocompl_ville.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#Lieu").val(item.nom);
				jq("#Departement").val(item.departement);
			}
		}
	});
	vanillaAutocomplete('#Nom', 'Autocompl_refJournee.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#Nom").val(item.nom);
			}
		}
	});
	vanillaAutocomplete('#Organisateur', 'Autocompl_club.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#Organisateur").val(item.libelle);
			}
		}
	});
	vanillaAutocomplete('#Responsable_R1', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Responsable_R1").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Responsable_insc', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Responsable_insc").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Delegue', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Delegue").val(nom);
			}
		}
	});
	vanillaAutocomplete('#ChefArbitre', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#ChefArbitre").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Rep_athletes', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Rep_athletes").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Arb_nj1', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Arb_nj1").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Arb_nj2', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Arb_nj2").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Arb_nj3', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Arb_nj3").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Arb_nj4', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Arb_nj4").val(nom);
			}
		}
	});
	vanillaAutocomplete('#Arb_nj5', 'Autocompl_joueur3.php', {
		width: 420,
		maxResults: 80,
		minChars: 2,
		dataType: 'json',
		extraParams: {
			format: 'json'
		},
		scrollHeight: 320,
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				var nom = item.prenom + ' ' + item.nom + ' (' + item.matric + ')';
				jq("#Arb_nj5").val(nom);
			}
		}
	});

	jq('.rcpick').click(function(){
		var rc = jq(this).attr('title');
		jq("#Responsable_insc").val(rc);
	});
	
});