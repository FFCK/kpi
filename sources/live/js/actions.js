var theContext = new Object();
theContext.Match = new tableMatch();
theContext.CountTimer = 0;
theContext.ScrollCount = 5;
theContext.ScrollStart = 0;
theContext.Event = 44;

function RefreshCacheGlobal()
{
	var nb = theContext.Match.GetCount();
	for (var i=0;i<nb;i++)
	{
		$.ajax({
			url : './cache/'+theContext.Match.GetId(i)+'_match_global.json',
			type: 'GET',
			dataType: 'text',
			cache: false,
			async: false,
			success: ParseCacheGlobal
		});
	}
}

function RefreshCacheScore()
{
	var nb = theContext.Match.GetCount();
	for (var i=0;i<nb;i++)
	{
		$.ajax({
			url : './cache/'+theContext.Match.GetId(i)+'_match_score.json',
			type: 'GET',
			dataType: 'text',
			cache: false,
			async: false,
			success: ParseCacheScore
		});
	}
}

function RefreshCacheChrono()
{
	var nb = theContext.Match.GetCount();
	for (var i=0;i<nb;i++)
	{
		$.ajax({
			url : './cache/'+theContext.Match.GetId(i)+'_match_chrono.json',
			type: 'GET',
			dataType: 'text',
			cache: false,
			async: false,
			success: ParseCacheChrono
		});
	}
}

function ParseCacheScore(jsonTxt)
{
	var iFind = jsonTxt.lastIndexOf("@@END@@");
	if (iFind == -1) return; // Aucune précence de @@END@@ => le fichier cache n'est pas complet ... => On sort

	jsonTxt = jsonTxt.substring(0,iFind);
	jsonData = JSON && JSON.parse(jsonTxt) || $.parseJSON(jsonTxt);
 
 	if (typeof(jsonData.id_match) == 'undefined')
		return;	// Data JSON non correcte ...

	if (typeof(jsonData.tick) == 'undefined')
		return;	// Data JSON non correcte ...

	var rowMatch = theContext.Match.GetRow(jsonData.id_match);
	if (rowMatch < 0)
		return; // Id Match pas dans la liste ... ???

	theContext.Match.SetTickScore(rowMatch, jsonData.tick);
	theContext.Match.SetPeriode(rowMatch, jsonData.periode);

	$('#score1').html(jsonData.score1);
	$('#score2').html(jsonData.score2);
	
	var nbEvents = jsonData.event.length;
	var htmlText = '<table class="table">';
	if (theContext.ScrollStart >= nbEvents)
		theContext.ScrollStart = 0;

	for (var r=0;r<theContext.ScrollCount;r++)
	{
		var i = theContext.ScrollStart + r;
		if (i >= nbEvents)
			break;
	
		 htmlText += '<tr>';
		 htmlText += '<td>'+jsonData.event[i].Temps.substr(3,5)+'</td>';
		 htmlText += '<td>'+jsonData.event[i].Periode+'</td>';
		 htmlText += '<td>'+GetLabelEvtMatch(jsonData.event[i].Id_evt_match)+'</td>';
		 htmlText += '<td>'+jsonData.event[i].Nom+' '+jsonData.event[i].Prenom+'</td>';
		 htmlText += '</tr>';
	}
	htmlText += '</table>';
	$('#match_event').html(htmlText);

	if (theContext.ScrollStart+theContext.ScrollCount < nbEvents)
		++theContext.ScrollStart;
	else
		theContext.ScrollStart = 0;
}

function ParseCacheChrono(jsonTxt)
{
	var iFind = jsonTxt.lastIndexOf("@@END@@");
	if (iFind == -1) return; // Aucune précence de @@END@@ => le fichier cache n'est pas complet ... => On sort

	jsonTxt = jsonTxt.substring(0,iFind);

	var jsonData = JSON && JSON.parse(jsonTxt) || $.parseJSON(jsonTxt);

	if (typeof(jsonData.IdMatch) == 'undefined')
		return;	// Data JSON non correcte ...

	if (typeof(jsonData.tick) == 'undefined')
		return;	// Data JSON non correcte ...

	var rowMatch = theContext.Match.GetRow(jsonData.IdMatch);
	if (rowMatch < 0)
		return; // Id Match pas dans la liste ... ???

	if (jsonData.tick == theContext.Match.GetTickChrono(rowMatch))
		return;	// Fichier de Cache déja pris en compte ...

	theContext.Match.SetTickChrono(rowMatch, jsonData.tick);
	theContext.Match.SetEtat(rowMatch, jsonData.action);
	
	var temps_ecoule = parseInt(parseInt(jsonData.run_time)/1000);
	var temps_reprise = parseInt(jsonData.start_time_server);
	theContext.Match.SetTempsEcoule(rowMatch, temps_ecoule);
	theContext.Match.SetTempsReprise(rowMatch, temps_reprise);
	
	var temps_max = jsonData.max_time;
	if (jsonData.max_time.length == 5)
	{
		temps_max = parseInt(temps_max.substr(0,2))*60 + parseInt(temps_max.substr(3,2));
		theContext.Match.SetTempsMax(rowMatch, temps_max);
	}
	
//	alert("Etat Chrono "+jsonData.action);
}

function ParseCacheGlobal(jsonTxt)
{
	var iFind = jsonTxt.lastIndexOf("@@END@@");
	if (iFind == -1) return; // Aucune précence de @@END@@ => le fichier cache n'est pas complet ... => On sort
	
	jsonTxt = jsonTxt.substring(0,iFind);
	jsonData = JSON && JSON.parse(jsonTxt) || $.parseJSON(jsonTxt);
  
	if (typeof(jsonData.id_match) == 'undefined')
		return;	// Data JSON non correcte ...

	if (typeof(jsonData.tick) == 'undefined')
		return;	// Data JSON non correcte ...
		
	var rowMatch = theContext.Match.GetRow(jsonData.id_match);
	if (rowMatch < 0)
		return; // Id Match pas dans la liste ... ???

	if (jsonData.tick == theContext.Match.GetTickGlobal(rowMatch))
		return;	// Fichier de Cache déja pris en compte ...

	// Mise à jour des données ...
	theContext.Match.SetTickGlobal(rowMatch, jsonData.tick);
	theContext.Match.SetStatut(rowMatch, jsonData.statut);
	
	var nom = jsonData.competition;
	nom += '-';
	nom += GetLabelPeriode(theContext.Match.GetPeriode(rowMatch));
	nom += '-';
	nom += jsonData.phase;
	nom += '-';
	nom += jsonData.terrain;
	$('#match_nom').html(nom);

	$('#equipe1').html(jsonData.equipe1.nom);
	$('#equipe2').html(jsonData.equipe2.nom);
	theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.nom);
	theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.nom);

	$('#nation1').html(ImgNation(jsonData.equipe1.club));
	$('#nation2').html(ImgNation(jsonData.equipe2.club));
}

function Init(idMatch)
{
	theContext.Match.Add(idMatch); 

	RefreshCacheGlobal();
	RefreshCacheChrono();
	setTimeout(RefreshCacheScore(), 800);
	
	setInterval(RefreshCacheScore, 1000);
}
