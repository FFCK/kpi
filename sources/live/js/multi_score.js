function RefreshHorloge () {
	if (typeof (theContext.temps_offset) == 'undefined') {
		// Prise de l'Offset entre le temps du serveur et le temps de la machine cliente ...
		$.ajax({
			url: './get_sec.php',
			type: 'GET',
			dataType: 'text',
			cache: false,
			async: false,
			success: function (flux) {
				var now = new Date()
				var temps_actuel = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()
				theContext.temps_offset = temps_actuel - parseInt(flux)
			}
		})
		return
	}

	var nb = theContext.Match.GetCount()
	for (var i = 0; i < nb; i++) {
		if ((theContext.Match.GetEtat(i) == 'run') || (theContext.Match.GetEtat(i) == 'start')) {
			var now = new Date()
			var temps_actuel = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()

			var temps_match = theContext.Match.GetTempsEcoule(i) + temps_actuel - theContext.temps_offset
			var temps_running = temps_actuel - theContext.Match.GetTempsReprise(i) - theContext.temps_offset
			/*
						alert('temps_REPRISE2='+ theContext.Match.GetTempsReprise(i));
						alert('temps_ACTUEL2='+ temps_actuel);
						alert('temps_OFFSET2='+theContext.temps_offset);
						alert('temps_RUNNING='+(temps_running).toString());
			*/
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i) - temps_running
			if (temps_restant < 0) temps_restant = 0

			$('#match_horloge_' + (i + 1).toString()).html(SecToMMSS(temps_restant))
			$('#match_periode_' + (i + 1).toString()).html(GetLabelPeriode(theContext.Match.GetPeriode(i).replace('M1', '1st').replace('M2', '2nd')))
		}
		else {
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i)
			if (temps_restant < 0) temps_restant = 0

			// Evolution Chrono ...
			if (theContext.Match.GetStatut(i) == 'END')
				temps_restant = 0

			$('#match_horloge_' + (i + 1).toString()).html(SecToMMSS(temps_restant))
			$('#match_periode_' + (i + 1).toString()).html(GetLabelPeriode(theContext.Match.GetPeriode(i).replace('M1', '1st').replace('M2', '2nd')))
		}
	}

	++theContext.CountTimer
	// if (theContext.CountTimer % 2 == 0)
	RefreshCacheChrono()

	// if (theContext.CountTimer % 4 == 0)
	RefreshCacheScore()
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

	var idMulti = rowMatch + 1

	var nbEvents = jsonData.event.length
	if (nbEvents > 0) {
		var lastId = jsonData.event[0].Id
		if ((theContext.Match.GetIdEvent(rowMatch) != lastId) && (theContext.Match.GetIdEvent(rowMatch) >= 0)) {
			var line
			if (jsonData.event[0].Equipe_A_B == 'A') {
				line = ImgNation(theContext.Match.GetEquipe1(rowMatch))
				line += '&nbsp;' + theContext.Match.GetEquipe1(rowMatch).substring(0, 3)
			} else {
				line = ImgNation(theContext.Match.GetEquipe2(rowMatch))
				line += '&nbsp;' + theContext.Match.GetEquipe2(rowMatch).substring(0, 3)
			}
			line += "&nbsp;<span>"
			//			line  = GetImgEvtMatch(jsonData.event[0].Id_evt_match);
			//			line += "&nbsp;";
			//			line += GetLabelEvtMatch(jsonData.event[0].Id_evt_match);
			$('#match_event_line1_' + idMulti).html(line)

			if (jsonData.event[0].Numero == "undefi") {
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

			$('#match_event_line2_' + idMulti).html(line)

			$('#goal_card_' + idMulti).html(GetImgEvtMatch(jsonData.event[0].Id_evt_match))

			$('#bandeau_goal_' + idMulti).fadeIn(400).delay(6000).fadeOut(900)
		}

		theContext.Match.SetIdEvent(rowMatch, lastId)
	}

	var score1 = jsonData.score1
	if (((score1 == '') || (score1 == null)) && (jsonData.periode != 'ATT'))
		score1 = '0'

	var score2 = jsonData.score2
	if (((score2 == '') || (score2 == null)) && (jsonData.periode != 'ATT'))
		score2 = '0'

	$('#score1_' + idMulti).html(score1)
	$('#score2_' + idMulti).html(score2)
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

	/*
		alert("Etat = "+jsonData.action);
		alert("Temps_max = "+temps_max);
		alert("Run_time = "+jsonData.run_time);
		alert("Temps_ecoule="+temps_ecoule);
		alert("Temps_reprise="+temps_reprise);
	*/

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
	theContext.Match.SetHeure(rowMatch, jsonData.heure)

	var idMulti = rowMatch + 1

	$('#match_nom_' + idMulti).html(jsonData.competition)

	$('#equipe1_' + idMulti).html(jsonData.equipe1.nom.substr(0, 3))
	$('#equipe2_' + idMulti).html(jsonData.equipe2.nom.substr(0, 3))

	theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.club)
	theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.club)

	$('#nation1_' + idMulti).html(ImgNation(jsonData.equipe1.club))
	$('#nation2_' + idMulti).html(ImgNation(jsonData.equipe2.club))

	$('#lien_pdf_' + idMulti).html('<a href="../PdfMatchMulti.php?listMatch='
		+ jsonData.id_match
		+ '" target="_blank" class="btn btn-primary">Game report <span class="badge">#' + jsonData.numero_ordre + '</span></a>')
	$('#terrain_' + idMulti).text('Pitch ' + jsonData.terrain)
	$('#catgroup_' + idMulti).text(jsonData.categ + ' - ' + jsonData.phase).show()
	$('#heure_' + idMulti).text(jsonData.heure).show()
	
}


function RefreshCacheNext (idMulti, idNext) {
	if (idNext > 0) {
		axios({
			method: 'post',
			url: './cache/' + idNext + '_match_global.json',
			params: {},
			responseType: 'json'
		})
			.then(function (response) {
				ParseCacheNext(idMulti, response.data)
			})
			.catch(function (error) {
				console.log(error)
			})
	} else {
		$('#nextgame_' + idMulti).hide()
	}
}

function ParseCacheNext (idMulti, jsonData) {
	if (typeof (jsonData.id_match) == 'undefined')
		return	// Data JSON non correcte ...

	if (typeof (jsonData.tick) == 'undefined')
		return	// Data JSON non correcte ...

	if (jsonData.tick == nextTick[idMulti]) {
		return	// Data JSON déjà traitée ...
	}
	nextTick[idMulti] = jsonData.tick
	const nextGame = '#' + jsonData.numero_ordre + ' : '+ jsonData.heure + ' (' + jsonData.categ + ' - ' + jsonData.phase + ')<br>' + jsonData.equipe1.nom + ' - ' + jsonData.equipe2.nom
	
	$('#nextgame_' + idMulti).html('Next game ' + nextGame).show()
}

function RefreshCacheMultiPitch () {
	var nb = theContext.Match.GetCount()
	for (var i = 1; i <= nb; i++) {
		$.ajax({
			url: './cache/event' + theContext.Event + '_pitch' + i + '.json',
			type: 'GET',
			dataType: 'json',
			cache: false,
			async: false,
			success: ParseCacheMultiPitch
		})
	}
}

function ParseCacheMultiPitch (jsonData) {

	if (typeof (jsonData.id_match) == 'undefined')
		return	// Data JSON non correcte ...

	if (typeof (jsonData.pitch) == 'undefined')
		return	// Data JSON non correcte ...

	var nb = theContext.Match.GetCount()
	if (jsonData.pitch <= nb) {
		var rowMatch = jsonData.pitch - 1
		theContext.Match.SetId(rowMatch, jsonData.id_match)
		if (speaker > 0) {
			RefreshCacheNext(jsonData.pitch, jsonData.id_next.id)
		}
	}
}

function RefreshCountDown (refresh) {
	countDown -= 1
	if (countDown <= 0) {
		countDown = refresh
		RefreshHorloge()
		showCurrentTime()
	}
	$('#refresh_frequency').text('refresh: ' + countDown + 's')
	
}

function showCurrentTime() {
	var date = new Date()
	var h = date.getHours();
	var m = date.getMinutes();
	// var s = date.getSeconds();
	if( h < 10 ){ h = '0' + h; }
	if( m < 10 ){ m = '0' + m; }
	// if( s < 10 ){ s = '0' + s; }
	var time = h + ':' + m // + ':' + s
	document.getElementById('horloge').innerHTML = time;
}

function Init (event, count, voie, refresh) {
	
	countDown = parseInt(refresh, 10)
	

	theContext.Event = event
	//	alert("Event = "+theContext.Event);
	for (var i = 1; i <= count; i++) {
		nextTick[i] = 0
		theContext.Match.Add(-1)
	}
	RefreshCacheMultiPitch()

	RefreshCacheGlobal()
	RefreshCacheScore()
	RefreshCacheChrono()

	showCurrentTime()

	// Refresh du cache Pitch toute les 10 secondes ...
	setInterval(RefreshCacheMultiPitch, 10000)

	// Refresh du cache Global toute les 30 secondes ...
	setInterval(RefreshCacheGlobal, 30000)

	// Refresh du cache Score toute les 5 secondes ...
	//	setInterval(RefreshCacheScore, 5500);

	// Refresh Chrono toutes les 2 secondes  ...
	//	setInterval(RefreshCacheChrono, 2500);

	// Refresh Horloge toutes les X secondes  ...
	setInterval(RefreshCountDown, 1000, refresh)

	SetVoie(voie)
}

