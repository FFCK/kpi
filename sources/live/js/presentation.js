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

	if (jsonData.tick == theContext.Match.GetTickScore(rowMatch))
		return;	// Fichier de Cache déja pris en compte ...

	theContext.Match.SetTickScore(rowMatch, jsonData.tick);
	theContext.Match.SetPeriode(rowMatch, jsonData.periode);
	theContext.Match.SetScore1(rowMatch, jsonData.score1);
	theContext.Match.SetScore2(rowMatch, jsonData.score2);
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

	if (jsonData.statut == 'ON')
	{
		window.location.href = "./score.php?terrain="+theContext.Terrain+"&speaker="+theContext.Speaker;
		return;
	}

	if (jsonData.statut != 'END')
	{
		// Ligne 1
		var line;
		line  = jsonData.categ;
	 	line += " - Pitch ";
		line += jsonData.terrain;
	
		$('#presentation_line1').html(line);
		
		// Ligne 2
		line  = ImgNation(jsonData.equipe1.club);
		line += "&nbsp;<span>";
		line += jsonData.equipe1.nom;
		line += " vs ";
		line += jsonData.equipe2.nom;
		line += "</span>&nbsp;";
		line += ImgNation(jsonData.equipe2.club);
		$('#presentation_line2').html(line);
	}
	else
	{
		// Ligne 1
		var line;

//		line = 'U21 MEN - Pitch 2 ';
		line  = jsonData.categ;
	 	line += " - Pitch ";
		line += jsonData.terrain;

		//	line = jsonData.competition;
		$('#presentation_line1').html(line);
		
		// Ligne 2
		line  = ImgNation(jsonData.equipe1.club);
		line += "&nbsp;<span>";
		line += jsonData.equipe1.nom;
		line += "&nbsp;&nbsp;";
		line += theContext.Match.GetScore1(rowMatch);
		line += " - ";
		line += theContext.Match.GetScore2(rowMatch);
		line += "&nbsp;&nbsp;";
		line += jsonData.equipe2.nom;
		line += "</span>&nbsp;";
		line += ImgNation(jsonData.equipe2.club);
		$('#presentation_line2').html(line);
	}
}

function Init(terrain, speaker)
{
	theContext.Terrain = terrain;
	theContext.Speaker = speaker;
	
	theContext.Match.Add(-1); 
	RefreshCacheTerrain();
	
	// Refresh du cache Global toute les 20 secondes ...
	setInterval(RefreshCacheGlobal, 15000);
	
	setInterval(RefreshCacheTerrain, 10000);
	
	
}	
