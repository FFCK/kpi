
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

  // EVENT
  var nbEvents = jsonData.event.length
  if (nbEvents > 0) {
    var lastId = jsonData.event[0].Id
    // Evenement pas encore pris en compte et pas le tout premier existant au chargement de la page
    if ((theContext.Match.GetIdEvent(rowMatch) != lastId) && (theContext.Match.GetIdEvent(rowMatch) != '-1')) {
      // Evenement déjà affiché précédemment (suite à suppression)
      if ((theContext.Match.GetIdPrevEvent(rowMatch) == lastId)) {
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
        line = ImgClub48(theContext.Match.GetClub1(rowMatch))
        line += '&nbsp;' + theContext.Match.GetEquipe1(rowMatch)
      } else {
        line = ImgClub48(theContext.Match.GetClub2(rowMatch))
        line += '&nbsp;' + theContext.Match.GetEquipe2(rowMatch)
      }
      line += "&nbsp;<span>"
      document.querySelector('#match_event_line1').innerHTML = line

      if (jsonData.event[0].Numero == 'undefi') {
        if (jsonData.event[0].Equipe_A_B == 'A')
          line = "Team " + theContext.Match.GetEquipe1(rowMatch)
        else
          line = "Team " + theContext.Match.GetEquipe2(rowMatch)
      } else {
        if (jsonData.event[0].Capitaine != 'E') {
          line = '<span class="clair numero">' + jsonData.event[0].Numero + '</span>&nbsp;'
        }
        line += '<span class="nom">'
        line += truncateStr(jsonData.event[0].Nom, 16)
        line += '</span> <span class="prenom">'
        line += truncateStr(jsonData.event[0].Prenom, 16)
        line += '</span>'

        if (jsonData.event[0].Capitaine == 'C') {
          line += ' <span class="badge bg-warning capitaine">C</span>'
        } else if (jsonData.event[0].Capitaine == 'E') {
          line += ' (Coach)'
        }
      }
      line += "</span>"
      document.querySelector('#match_event_line2').innerHTML = line

      document.querySelector('#goal_card').innerHTML = GetImgEvtMatch(jsonData.event[0].Id_evt_match)

      // const b = jsonData.event[0].Id_evt_match === 'B' ? '_b' : ''
      const b = ''
      document.querySelector('#match_player img').src = '/img/KIP/players/' + jsonData.event[0].Competiteur + b + '.png'

      const bandeau_goal = document.querySelector('#bandeau_goal')
      bandeau_goal.style.display = 'block'
      bandeau_goal.classList.remove('animate__fadeOutLeft')
      bandeau_goal.classList.add('animate__fadeInLeft')
      setTimeout(function () {
        bandeau_goal.classList.remove('animate__fadeInLeft')
        bandeau_goal.classList.add('animate__fadeOutLeft')
      }, 10000)
    }
    theContext.Match.SetIdEvent(rowMatch, lastId)
  }
  // Réinitialisation du tout premier existant au chargement de la page
  if (theContext.Match.GetIdEvent(rowMatch) === '-1') {
    theContext.Match.SetIdEvent(rowMatch, '')
  }
}


function ParseCacheChrono (jsonData) {
  return
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

  if (document.querySelector('#match_nom'))
    document.querySelector('#match_nom').innerHTML = jsonData.competition

  theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.nom)
  theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.nom)
  theContext.Match.SetClub1(rowMatch, jsonData.equipe1.club)
  theContext.Match.SetClub2(rowMatch, jsonData.equipe2.club)

  if (document.querySelector('#lien_pdf'))
    document.querySelector('#lien_pdf').innerHTML = '<a href="../PdfMatchMulti.php?listMatch=' + jsonData.id_match + '" target="_blank" class="btn btn-primary">Report <span class="badge bg-dark">' + jsonData.numero_ordre + '</span></a>'
  if (document.querySelector('#terrain'))
    document.querySelector('#terrain').innerHTML = 'Pitch ' + jsonData.terrain

}

function Init (event, terrain, speaker, voie) {
  theContext.Event = event
  theContext.Terrain = terrain
  theContext.Speaker = speaker

  theContext.Match.Add(-1)
  RefreshCacheTerrain()

  RefreshCacheGlobal()
  RefreshCacheChrono()
  setInterval(RefreshCacheScore, 1000)

  // Refresh du cache Terrain toute les 10 secondes ...
  setInterval(RefreshCacheTerrain, 10000)
  // Refresh du cache Global toute les 30 secondes ...
  setInterval(RefreshCacheGlobal, 30000)

  SetVoie(voie)
}

