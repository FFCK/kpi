function ParseCacheChrono (jsonData) {
  return
}

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

  var nbEvents = jsonData.event.length
  if (nbEvents > 0) {
    var lastId = jsonData.event[0].Id
    // Evenement déjà pris en compte
    if ((theContext.Match.GetIdEvent(rowMatch) != lastId) && (theContext.Match.GetIdEvent(rowMatch) >= 0)) {
      // Evenement déjà affiché précédemment (suite à suppression)
      if ((theContext.Match.GetIdPrevEvent(rowMatch) == lastId) && (theContext.Match.GetIdPrevEvent(rowMatch) >= 0)) {
        if (nbEvents > 1) {
          // Mémorisation de l'événement précédent
          theContext.Match.SetIdPrevEvent(rowMatch, jsonData.event[1].Id)
        }
        return
      }
      if (nbEvents > 1) {
        // Mémorisation de l'événement précédent
        theContext.Match.SetIdPrevEvent(rowMatch, jsonData.event[1].Id)
      }

      var line
      if (jsonData.event[0].Equipe_A_B == 'A') {
        line = ImgNation48(theContext.Match.GetEquipe1(rowMatch))
        line += '&nbsp;' + theContext.Match.GetEquipe1(rowMatch)
      } else {
        line = ImgNation48(theContext.Match.GetEquipe2(rowMatch))
        line += '&nbsp;' + theContext.Match.GetEquipe2(rowMatch).substring(0, 3)
      }
      line += "&nbsp;<span>"
      //			line  = GetImgEvtMatch(jsonData.event[0].Id_evt_match);
      //			line += "&nbsp;";
      //			line += GetLabelEvtMatch(jsonData.event[0].Id_evt_match);
      $('#match_event_line1').html(line)

      if (jsonData.event[0].Numero == 'undefi') {
        if (jsonData.event[0].Equipe_A_B == 'A')
          line = "Team " + theContext.Match.GetEquipe1(rowMatch).substring(0, 3)
        else
          line = "Team " + theContext.Match.GetEquipe2(rowMatch).substring(0, 3)
      } else {
        if (jsonData.event[0].Capitaine != 'E') {
          line = '<span class="clair">' + jsonData.event[0].Numero + '</span>&nbsp;'
        }
        line += ' '
        line += jsonData.event[0].Nom
        line += ' '
        line += jsonData.event[0].Prenom

        if (jsonData.event[0].Capitaine == 'C') {
          line += ' <span class="label label-warning capitaine">C</span>'
        } else if (jsonData.event[0].Capitaine == 'E') {
          line += ' (Coach)'
        }
      }
      line += "</span>"
      $('#match_event_line2').html(line)

      $('#goal_card').html(GetImgEvtMatch(jsonData.event[0].Id_evt_match))

      $('#bandeau_goal').fadeIn(600).delay(10000).fadeOut(900)
    }

    theContext.Match.SetIdEvent(rowMatch, lastId)
  }

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

  $('#match_nom').html(jsonData.competition)

  var equipe1 = jsonData.equipe1.nom
  equipe1 = equipe1.replace(" Women", " W.")
  //	equipe1 = equipe1.replace(" Men", " M.");
  equipe1 = equipe1.substr(0, 3)
  $('#equipe1').html(equipe1)

  var equipe2 = jsonData.equipe2.nom
  equipe2 = equipe2.replace(" Women", " W.")
  //	equipe2 = equipe2.replace(" Men", " M.");
  equipe2 = equipe2.substr(0, 3)
  $('#equipe2').html(equipe2)

  theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.club)
  theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.club)

  $('#nation1').html(ImgNation48(jsonData.equipe1.club))
  $('#nation2').html(ImgNation48(jsonData.equipe2.club))

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
  setInterval(RefreshCacheScore, 1500)

  // Refresh du cache Terrain toute les 10 secondes ...
  setInterval(RefreshCacheTerrain, 10000)
  // Refresh du cache Global toute les 30 secondes ...
  setInterval(RefreshCacheGlobal, 30000)

  SetVoie(voie)
}

