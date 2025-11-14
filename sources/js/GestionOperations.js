jq = jQuery.noConflict()

var langue = []
var theLstEvt = '-1'
var theLocalUrl = 'http://localhost/KPI2'
var theDistantUrl = "https://www.kayak-polo.info"

if (lang == 'en') {
	langue['Cliquez_pour_modifier'] = 'Click to edit'
} else {
	langue['Cliquez_pour_modifier'] = 'Cliquez pour modifier'
}

function ExportEvt () {
	jq("#ParamCmd").val(jq('#evenementExport').val())
	jq("#Cmd").val('ExportEvt')
	jq("#formOperations").submit()
}

function ImportEvt () {
	jq("#ParamCmd").val(jq('#evenementImport').val())
	jq("#Cmd").val('ImportEvt')
	jq("#formOperations").submit()
}

function changeAuthSaison () {
	document.forms['formOperations'].elements['Cmd'].value = 'ChangeAuthSaison'
	document.forms['formOperations'].elements['ParamCmd'].value = ''
	document.forms['formOperations'].submit()
}

function AddSaison () {
	if (!confirm(langue['Confirmer'])) {
		return
	}
	else {
		document.forms['formOperations'].elements['Cmd'].value = 'AddSaison'
		document.forms['formOperations'].elements['ParamCmd'].value = ''
		document.forms['formOperations'].submit()
	}
}

function activeSaison () {
	if (!confirm(langue['Confirmer'])) {
		document.forms['formOperations'].reset
		return
	} else if (!confirm(langue['Confirmer'])) {
		document.forms['formOperations'].reset
		return
	} else {
		document.forms['formOperations'].elements['Cmd'].value = 'ActiveSaison'
		document.forms['formOperations'].elements['ParamCmd'].value = document.forms['formOperations'].elements['saisonActive'].value
		document.forms['formOperations'].submit()
	}
}

function showDataConnector(data)
{
	jq('#json_msg').html("<h1>Importation</h1>");
	jq('#json_msg').append("<h2>Les donnees suivantes sont enregistr&eacute;es dans la base locale ...</h2>");

	jq('#json_msg').append("<div>"+data+"</div>");
}

function showExportReturn(data)
{
	alert('showExportReturn = '+data);

	jq('#json_msg').html('<h1>Exportation</h1>');
	jq('#json_msg').append('<div>'+data+'</div>');
}

function submitJsonData(json)
{
	var txtJSON = JSON.stringify(json);

   	var pos = txtJSON.indexOf('ERREUR');
    if ((pos >= 0) && (pos <= 2))
 	{
    	alert(txtJSON);
    	return;
    }

	jq('#json_data').attr('value', txtJSON);
	document.forms['formOperations'].submit();
}

function getRemoteData(url)
{
    var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = url + "&callback=submitJsonData"; //ajout de la fonction de retour
	jq("head")[0].appendChild(script);
}

function OnImport()
{
	theLstEvt = jq('#lstEvent').attr('value');
	var user = jq('#user').attr('value');
	var pwd = jq('#pwd').attr('value');

	if (theLstEvt.length == 0)
	{
	    alert("Erreur : Aucun Evenement ...");
	    return;
	}

	if (user.length == 0)
	{
	    alert("Erreur : Utilisateur Vide ...");
	    return;
	}

	if (pwd.length == 0)
	{
	    alert("Erreur : Mot de Passe Vide ...");
	    return;
	}

	jq.ajax({
		url : theLocalUrl+'/connector/ajax_md5.php?user='+user+'&pwd='+pwd,
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		crossDomain:true,
		success: OnImportMD5
	});
}

function OnImportMD5(session)
{
	getRemoteData(theDistantUrl+'/connector/get_evenement.php?lst='+theLstEvt+'&session='+session);
}

function OnImportServer()
{
    theLstEvt = jq('#lstEvent').attr('value');

	if (theLstEvt.length == 0)
	{
	    alert("Erreur : Aucun Evenement !");
	    return;
	}

    jq.ajax({
		url : theDistantUrl+'/connector/ajax_okevent.php?lst='+theLstEvt,
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		crossDomain:false,
		success: OnImportServerOk
	});
}

function OnImportServerOk(msg)
{
    var pos = msg.indexOf('OK');
    if (pos == 0)
    {
        getRemoteData(theLocalUrl+'/connector/get_evenement.php?lst='+theLstEvt);
        return;
    }

    alert("ERREUR Evènement ou Login ... : "+msg);
}

jq(document).ready(function () {

	// Add language strings
	if (!langue) {
		langue = []
	}
	if (lang == 'en') {
		langue['Confirmer'] = 'Confirm ?'
	} else {
		langue['Confirmer'] = 'Confirmez-vous ?'
	}

	//Fusion joueurs
	vanillaAutocomplete('#FusionSource', 'Autocompl_joueur.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionSource").val(item.matric);
				jq("#FusionSource").val(item.label);
			}
		}
	});
	vanillaAutocomplete('#FusionCible', 'Autocompl_joueur.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionCible").val(item.matric);
				jq("#FusionCible").val(item.label);
			}
		}
	});
	jq("#FusionJoueurs").click(function () {
		var fusSource = jq("#FusionSource").val()
		var fusCible = jq("#FusionCible").val()
		if (!confirm('Confirmez-vous la fusion : ' + fusSource + ' => ' + fusCible + ' ?')) {
			return false
		}
		document.forms['formOperations'].elements['Cmd'].value = 'FusionJoueurs'
		document.forms['formOperations'].submit()
	})

	//Renomme Equipe
	vanillaAutocomplete('#RenomSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numRenomSource").val(item.numero);
				jq("#RenomSource").val(item.libelle);
				jq("#RenomCible").val(item.libelle);
			}
		}
	});
	jq("#RenomEquipe").click(function () {
		var renSource = jq("#RenomSource").val()
		var renCible = jq("#RenomCible").val()
		if (!confirm('Confirmez-vous la modification :\n' + renSource + ' => ' + renCible + ' ?')) {
			return false
		}
		document.forms['formOperations'].elements['Cmd'].value = 'RenomEquipe'
		document.forms['formOperations'].submit()
	})

	//Fusion équipes
	vanillaAutocomplete('#FusionEquipeSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionEquipeSource").val(item.numero);
				jq("#FusionEquipeSource").val(item.libelle);
			}
		}
	});
	vanillaAutocomplete('#FusionEquipeCible', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.value,
		onSelect: function(item) {
			if (item) {
				jq("#numFusionEquipeCible").val(item.numero);
				jq("#FusionEquipeCible").val(item.libelle);
			}
		}
	});
	jq("#FusionEquipes").click(function () {
		var fusSource = jq("#FusionEquipeSource").val()
		var fusCible = jq("#FusionEquipeCible").val()
		if (!confirm('Confirmez-vous la fusion : ' + fusSource + ' => ' + fusCible + ' ?')) {
			return false
		}
		jq('#Cmd').val('FusionEquipes')
		jq('#formOperations').submit()
	})

	//Déplacement équipe
	vanillaAutocomplete('#DeplaceEquipeSource', 'Autocompl_equipe.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#numDeplaceEquipeSource").val(item.numero);
				jq("#DeplaceEquipeSource").val(item.label);
			}
		}
	});
	vanillaAutocomplete('#DeplaceEquipeCible', 'Autocompl_club2.php', {
		width: 550,
		maxResults: 50,
		cacheLength: 0,
		dataType: 'json',
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#numDeplaceEquipeCible").val(item.code);
				jq("#DeplaceEquipeCible").val(item.label);
			}
		}
	});
	jq("#DeplaceEquipe").click(function () {
		var depSource = jq("#DeplaceEquipeSource").val()
		var depCible = jq("#DeplaceEquipeCible").val()
		if (!confirm('Confirmez-vous le déplacement : ' + depSource + ' => ' + depCible + ' ?')) {
			return false
		}
		jq('#Cmd').val('DeplaceEquipe')
		jq('#formOperations').submit()
	})

	//Changement code competition
	vanillaAutocomplete('#ChangeCodeRecherche', 'Autocompl_compet2.php', {
		width: 550,
		maxResults: 30,
		minChars: 2,
		cacheLength: 0,
		dataType: 'json',
		extraParams: {
			saison: jq('#saisonTravail').val()
		},
		formatItem: (item) => item.label,
		formatResult: (item) => item.label,
		onSelect: function(item) {
			if (item) {
				jq("#changeCodeSource").val(item.code);
			}
		}
	});
	jq("#ChangeCodeBtn").click(function () {
		var changeCodeSource = jq("#changeCodeSource").val()
		var changeCodeCible = jq("#changeCodeCible").val()
		var seasonText = document.getElementById("changeCodeAllSeason").checked ? 'TOUTES LES SAISONS' : 'LA SAISON EN COURS'
		if (!confirm(`Confirmez-vous le changement de code pour ${seasonText} : ${changeCodeSource} => ${changeCodeCible}  ?`)) {
			return false
		}
		jq('#Cmd').val('ChangeCode')
		jq('#formOperations').submit()
	})

	// Import PCE handlers
	jq('#importPCE2').click(function(){
		jq('#json_msg').prepend( "Traitement en cours (patientez 15 à 20 secondes)..." );
		jq('#Control').val('importPCE2');
		jq('#formOperations').submit();
	});

	jq('#btnImportServer').click(function() {
		OnImportServer();
	});

	jq('#btnImport').click(function() {
		OnImport();
	});

})

