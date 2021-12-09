function RefreshHorloge () {
	if (typeof (theContext.temps_offset) == 'undefined') {
		// Prise de l'Offset entre le temps du serveur et le temps de la machine cliente ...
		axios({
			method: 'post',
			url: './get_sec.php',
			params: {},
			responseType: 'text'
		})
			.then(function (response) {
				var now = new Date()
				var temps_actuel = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()
				theContext.temps_offset = temps_actuel - parseInt(response.data)
			})
			.catch(function (error) {
				console.log(error)
				return
			})
		return
	}

	var nb = theContext.Match.GetCount()
	for (var i = 0; i < nb; i++) {
		if ((theContext.Match.GetEtat(i) == 'run') || (theContext.Match.GetEtat(i) == 'start')) {
			var now = new Date()
			var temps_actuel = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()

			// var temps_match = theContext.Match.GetTempsEcoule(i) + temps_actuel - theContext.temps_offset;
			var temps_running = temps_actuel - theContext.Match.GetTempsReprise(i) - theContext.temps_offset
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i) - temps_running
			if (temps_restant < 0) temps_restant = 0

		} else {
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i)
			if (temps_restant < 0) temps_restant = 0

			// Evolution Chrono ...
			if (theContext.Match.GetStatut(i) == 'END')
				temps_restant = 0
		}

		document.querySelector('#match_horloge').innerHTML = SecToMMSS(temps_restant)
		document.querySelector('#match_periode').innerHTML = GetLabelPeriode(theContext.Match.GetPeriode(i).replace('M1', '1').replace('M2', '2'))
	}

	++theContext.CountTimer
	//	if (theContext.CountTimer % 2 == 0)
	RefreshCacheChrono()

	//	if (theContext.CountTimer % 2 == 0)
	// RefreshCacheScore()
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
	theContext.Match.SetPeriode(rowMatch, jsonData.periode)

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
			document.querySelector('#match_event_line1').innerHTML = line

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
			document.querySelector('#match_event_line2').innerHTML = line

			document.querySelector('#goal_card').innerHTML = GetImgEvtMatch(jsonData.event[0].Id_evt_match)

			// $('#bandeau_goal').fadeIn(600).delay(6000).fadeOut(900)
			const bandeau_goal = document.querySelector('#bandeau_goal')
			bandeau_goal.style.display = 'block'
			bandeau_goal.classList.remove('animate__fadeOutLeft')
			bandeau_goal.classList.add('animate__fadeInLeft')
			setTimeout(function () {
				bandeau_goal.classList.remove('animate__fadeInLeft')
				bandeau_goal.classList.add('animate__fadeOutLeft')
			}, 5000)

		}

		theContext.Match.SetIdEvent(rowMatch, lastId)
	}

	var score1 = jsonData.score1
	if (((score1 == '') || (score1 == null)) && (jsonData.periode != 'ATT'))
		score1 = '0'

	var score2 = jsonData.score2
	if (((score2 == '') || (score2 == null)) && (jsonData.periode != 'ATT'))
		score2 = '0'

	document.querySelector('#score1').innerHTML = score1
	document.querySelector('#score2').innerHTML = score2
}

function ParseCacheChrono (jsonData) {

	if (typeof (jsonData.IdMatch) == 'undefined')
		return	// Data JSON non correcte ...

	if (typeof (jsonData.tick) == 'undefined')
		return	// Data JSON non correcte ...

	var rowMatch = theContext.Match.GetRow(jsonData.IdMatch)
	if (rowMatch < 0)
		return // Id Match pas dans la liste ... ???

	if (jsonData.tick == theContext.Match.GetTickChrono(rowMatch))
		return	// Fichier de Cache déja pris en compte ...

	theContext.Match.SetTickChrono(rowMatch, jsonData.tick)
	theContext.Match.SetEtat(rowMatch, jsonData.action)

	var temps_max = jsonData.max_time
	if (jsonData.max_time.length == 5) {
		temps_max = parseInt(temps_max.substr(0, 2)) * 60 + parseInt(temps_max.substr(3, 2))
		theContext.Match.SetTempsMax(rowMatch, temps_max)
	}

	var temps_ecoule = temps_max - parseInt(parseInt(jsonData.run_time) / 1000)
	var temps_reprise = parseInt(jsonData.start_time_server)

	theContext.Match.SetTempsEcoule(rowMatch, temps_ecoule)
	theContext.Match.SetTempsReprise(rowMatch, temps_reprise)
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

	var equipe1 = jsonData.equipe1.nom
	// equipe1 = equipe1.replace(" Women", " W.")
	//	equipe1 = equipe1.replace(" Men", " M.");
	equipe1 = equipe1.substr(0, 3).toUpperCase()
	document.querySelector('#equipe1').innerHTML = equipe1

	var equipe2 = jsonData.equipe2.nom
	equipe2 = equipe2.replace(" Women", " W.")
	//	equipe2 = equipe2.replace(" Men", " M.");
	equipe2 = equipe2.substr(0, 3).toUpperCase()
	document.querySelector('#equipe2').innerHTML = equipe2

	theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.club)
	theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.club)

	document.querySelector('#nation1').innerHTML = ImgNation48(jsonData.equipe1.club)
	document.querySelector('#nation2').innerHTML = ImgNation48(jsonData.equipe2.club)

	document.querySelector('#categorie').innerHTML = jsonData.categ + ' - ' + jsonData.phase

	if (document.querySelector('#lien_pdf'))
		document.querySelector('#lien_pdf').innerHTML = '<a href="../PdfMatchMulti.php?listMatch=' + jsonData.id_match + '" target="_blank" class="btn btn-primary">Report <span class="badge">' + jsonData.numero_ordre + '</span></a>'
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
	setTimeout(RefreshCacheScore(), 800)

	// Refresh du cache Terrain toute les 10 secondes ...
	setInterval(RefreshCacheTerrain, 10000)
	// Refresh du cache Global toute les 30 secondes ...
	setInterval(RefreshCacheGlobal, 30000)

	// Refresh du cache Score toute les 3 secondes ...
	setInterval(RefreshCacheScore, 3000)

	// Refresh Chrono toutes les 2 secondes  ...
	// setInterval(RefreshCacheChrono, 2500);

	// Refresh Horloge toutes les secondes  ...
	setInterval(RefreshHorloge, 1000)

	SetVoie(voie)
}

