function ParseCacheScore (jsonData) {
  if (typeof (jsonData.id_match) == 'undefined')
    return	// Data JSON non correcte ...

  if (typeof (jsonData.tick) == 'undefined')
    return	// Data JSON non correcte ...

  var rowMatch = theContext.Match.GetRow(jsonData.id_match)
  if (rowMatch < 0)
    return // Id Match pas dans la liste ... ???

  if (jsonData.tick == theContext.Match.GetTickScore(rowMatch))
    return	// Fichier de Cache déja pris en compte ...

  theContext.Match.SetTickScore(rowMatch, jsonData.tick)
  theContext.Match.SetPeriode(rowMatch, jsonData.periode)

  // SCORE
  var score1 = jsonData.score1
  if (((score1 == '') || (score1 == null)) && (jsonData.periode != 'ATT'))
    score1 = '0'

  var score2 = jsonData.score2
  if (((score2 == '') || (score2 == null)) && (jsonData.periode != 'ATT'))
    score2 = '0'

  document.querySelector('#score1').innerHTML = score1
  document.querySelector('#score2').innerHTML = score2
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

  document.querySelector('#banner_line1').innerHTML = jsonData.categ
  document.querySelector('#banner_line2').innerHTML = jsonData.phase

  var equipe1 = jsonData.equipe1.nom
  equipe1 = equipe1.replace(" Women", " W.")
  //	equipe1 = equipe1.replace(" Men", " M.");
  equipe1 = equipe1.substr(0, 3).toUpperCase()
  document.querySelector('#equipe1').innerHTML = equipe1

  var equipe2 = jsonData.equipe2.nom
  equipe2 = equipe2.replace(" Women", " W.")
  //	equipe2 = equipe2.replace(" Men", " M.");
  equipe2 = equipe2.substr(0, 3).toUpperCase()
  document.querySelector('#equipe2').innerHTML = equipe2

  theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.nom)
  theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.nom)
  theContext.Match.SetClub1(rowMatch, jsonData.equipe1.club)
  theContext.Match.SetClub2(rowMatch, jsonData.equipe2.club)

  document.querySelector('#nation1').innerHTML = ImgClub80(jsonData.equipe1.club)
  document.querySelector('#nation2').innerHTML = ImgClub80(jsonData.equipe2.club)
}

function RefreshCombo () {
  RefreshCacheTerrain(false)
  setTimeout(RefreshCacheGlobal, 800)
}
function Init (event, terrain, speaker, voie) {
  theContext.Event = event
  theContext.Terrain = terrain
  theContext.Speaker = speaker

  theContext.Match.Add(-1)
  RefreshCacheTerrain(false)

  RefreshCacheGlobal()

  setTimeout(RefreshCacheScore, 1200)

  // Refresh du cache Terrain et Global toute les 30 secondes ...
  setInterval(RefreshCombo, 30000)

  // Refresh du cache Score toute les 15 secondes ...
  setInterval(RefreshCacheScore, 15000)

  SetVoie(voie)
}
