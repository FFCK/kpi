function ParseCacheScore (jsonData) {
}

function ParseCacheChrono (jsonData) {
}

function ParseCacheGlobal (jsonData) {
	if (typeof (jsonData.id_match) == 'undefined')
		return	// Data JSON non correcte ...

	if (typeof (jsonData.tick) == 'undefined')
		return	// Data JSON non correcte ...

	var rowMatch = theContext.Match.GetRow(jsonData.id_match)
	if (rowMatch < 0)
		return // Id Match pas dans la liste ... ???

	if (jsonData.tick == theContext.Match.GetTickGlobal(rowMatch))
		return	// Fichier de Cache déja pris en compte ...

	// Mise à jour des données ...
	theContext.Match.SetTickGlobal(rowMatch, jsonData.tick)
	theContext.Match.SetStatut(rowMatch, jsonData.statut)

	/*	
		if (jsonData.statut == 'END')
		{
			window.location.href = "./presentation.php?terrain="+theContext.Terrain+"&speaker="+theContext.Speaker;
			return;
		}
	*/

	var equipe1 = jsonData.equipe1.nom
	equipe1 = equipe1.replace(" Women", " W.")
	//	equipe1 = equipe1.replace(" Men", " M.");
	equipe1 = equipe1
	$('#equipe1').html(equipe1)

	var equipe2 = jsonData.equipe2.nom
	equipe2 = equipe2.replace(" Women", " W.")
	//	equipe2 = equipe2.replace(" Men", " M.");
	equipe2 = equipe2
	$('#equipe2').html(equipe2)

	theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.club)
	theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.club)

	$('#nation1').html(ImgClub48(jsonData.equipe1.club))
	$('#nation2').html(ImgClub48(jsonData.equipe2.club))

	$('#categorie').html(jsonData.categ + ' - ' + jsonData.phase)

	$('#lien_pdf').html('<a href="../PdfMatchMulti.php?listMatch='
		+ jsonData.id_match
		+ '" target="_blank" class="btn btn-primary">Report <span class="badge">' + jsonData.numero_ordre + '</span></a>')
	$('#terrain').html('Pitch ' + jsonData.terrain)

}

function Init (event, terrain, speaker, voie) {
	theContext.Event = event
	theContext.Terrain = terrain
	theContext.Speaker = speaker

	theContext.Match.Add(-1)
	RefreshCacheTerrain()

	RefreshCacheGlobal()

	// Refresh du cache Terrain toute les 10 secondes ...
	setInterval(RefreshCacheTerrain, 5000)
	// Refresh du cache Global toute les 30 secondes ...
	setInterval(RefreshCacheGlobal, 10000)

	// Refresh du cache Score toute les 5 secondes ...
	//	setInterval(RefreshCacheScore, 5500);

	// Refresh Chrono toutes les 2 secondes  ...
	//	setInterval(RefreshCacheChrono, 2500);

	// Refresh Horloge toutes les secondes  ...

	SetVoie(voie)
}

