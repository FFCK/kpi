function recordMatch (id) {
	this.m_id = id

	this.m_tick_global = ''
	this.m_tick_score = ''
	this.m_tick_chrono = ''

	this.m_etat = '?'
	this.m_statut = '?'
	this.m_etat_prev = '?'

	this.m_periode = ''
	this.m_temps_ecoule = 0
	this.m_temps_reprise = 0
	this.m_temps_max = 0

	this.m_equipe1 = ''
	this.m_equipe2 = ''
	this.m_club1 = ''
	this.m_club2 = ''
	this.m_id_event = 0
	this.m_id_prev_event = -1

	this.m_score1 = 0
	this.m_score2 = 0
}

recordMatch.prototype.GetId = function () { return this.m_id }
recordMatch.prototype.GetTickGlobal = function () { return this.m_tick_global }
recordMatch.prototype.GetTickScore = function () { return this.m_tick_score }
recordMatch.prototype.GetTickChrono = function () { return this.m_tick_chrono }

recordMatch.prototype.GetEtat = function () { return this.m_etat }
recordMatch.prototype.GetEtatPrev = function () { return this.m_etat_prev }
recordMatch.prototype.GetStatut = function () { return this.m_statut }
recordMatch.prototype.GetScore1 = function () { return this.m_score1 }
recordMatch.prototype.GetScore2 = function () { return this.m_score2 }

recordMatch.prototype.GetPeriode = function () { return this.m_periode }
recordMatch.prototype.GetTempsEcoule = function () { return this.m_temps_ecoule }
recordMatch.prototype.GetTempsReprise = function () { return this.m_temps_reprise }
recordMatch.prototype.GetTempsMax = function () { return this.m_temps_max }

recordMatch.prototype.GetEquipe1 = function () { return this.m_equipe1 }
recordMatch.prototype.GetEquipe2 = function () { return this.m_equipe2 }
recordMatch.prototype.GetClub1 = function () { return this.m_club1 }
recordMatch.prototype.GetClub2 = function () { return this.m_club2 }
recordMatch.prototype.GetIdEvent = function () { return this.m_id_event }
recordMatch.prototype.GetIdPrevEvent = function () { return this.m_id_prev_event }

recordMatch.prototype.SetId = function (id) { this.m_id = id }
recordMatch.prototype.SetTickGlobal = function (tick) { this.m_tick_global = tick }
recordMatch.prototype.SetTickScore = function (tick) { this.m_tick_score = tick }
recordMatch.prototype.SetTickChrono = function (tick) { this.m_tick_chrono = tick }

recordMatch.prototype.SetEtat = function (etat) { this.m_etat_prev = this.m_etat; this.m_etat = etat }
recordMatch.prototype.SetStatut = function (statut) { this.m_statut = statut }

recordMatch.prototype.SetPeriode = function (periode) { this.m_periode = periode }
recordMatch.prototype.SetTempsEcoule = function (temps_ecoule) { this.m_temps_ecoule = temps_ecoule }
recordMatch.prototype.SetTempsReprise = function (temps_reprise) { this.m_temps_reprise = temps_reprise }
recordMatch.prototype.SetTempsMax = function (temps_max) { this.m_temps_max = temps_max }

recordMatch.prototype.SetEquipe1 = function (equipe1) { this.m_equipe1 = equipe1 }
recordMatch.prototype.SetEquipe2 = function (equipe2) { this.m_equipe2 = equipe2 }
recordMatch.prototype.SetClub1 = function (club1) { this.m_club1 = club1 }
recordMatch.prototype.SetClub2 = function (club2) { this.m_club2 = club2 }
recordMatch.prototype.SetIdEvent = function (id_event) { this.m_id_event = id_event }
recordMatch.prototype.SetIdPrevEvent = function (id_prev_event) { this.m_id_prev_event = id_prev_event }

recordMatch.prototype.SetScore1 = function (score1) { this.m_score1 = score1 }
recordMatch.prototype.SetScore2 = function (score2) { this.m_score2 = score2 }

function tableMatch () {
	this.m_array = new Array()
}

tableMatch.prototype.Add = function (id) {
	var rMatch = new recordMatch(id)
	this.m_array.push(rMatch)
}

tableMatch.prototype.GetCount = function () {
	return this.m_array.length
}

tableMatch.prototype.GetId = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetId()
}

tableMatch.prototype.GetTickGlobal = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTickGlobal()
}

tableMatch.prototype.GetTickScore = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTickScore()
}

tableMatch.prototype.GetScore1 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetScore1()
}

tableMatch.prototype.GetScore2 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetScore2()
}

tableMatch.prototype.GetTickChrono = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTickChrono()
}

tableMatch.prototype.GetEtat = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetEtat()
}

tableMatch.prototype.GetEtatPrev = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetEtatPrev()
}

tableMatch.prototype.GetStatut = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetStatut()
}

tableMatch.prototype.GetPeriode = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetPeriode()
}

tableMatch.prototype.GetTempsEcoule = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTempsEcoule()
}

tableMatch.prototype.GetTempsReprise = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTempsReprise()
}

tableMatch.prototype.GetTempsMax = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetTempsMax()
}

tableMatch.prototype.GetEquipe1 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetEquipe1()
}

tableMatch.prototype.GetEquipe2 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetEquipe2()
}

tableMatch.prototype.GetClub1 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetClub1()
}

tableMatch.prototype.GetClub2 = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetClub2()
}

tableMatch.prototype.GetIdEvent = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetIdEvent()
}

tableMatch.prototype.GetIdPrevEvent = function (row) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].GetIdPrevEvent()
}

tableMatch.prototype.SetId = function (row, id) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return ''

	return this.m_array[row].SetId(id)
}

tableMatch.prototype.SetTickGlobal = function (row, tick) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTickGlobal(tick)
}

tableMatch.prototype.SetTickScore = function (row, tick) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTickScore(tick)
}

tableMatch.prototype.SetScore1 = function (row, score1) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetScore1(score1)
}

tableMatch.prototype.SetScore2 = function (row, score2) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetScore2(score2)
}

tableMatch.prototype.SetTickChrono = function (row, tick) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTickChrono(tick)
}

tableMatch.prototype.SetEtat = function (row, etat) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetEtat(etat)
}

tableMatch.prototype.SetStatut = function (row, statut) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetStatut(statut)
}

tableMatch.prototype.SetPeriode = function (row, periode) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetPeriode(periode)
}

tableMatch.prototype.SetTempsEcoule = function (row, temps_ecoule) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTempsEcoule(temps_ecoule)
}

tableMatch.prototype.SetTempsReprise = function (row, temps_reprise) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTempsReprise(temps_reprise)
}

tableMatch.prototype.SetTempsMax = function (row, temps_max) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetTempsMax(temps_max)
}

tableMatch.prototype.SetEquipe1 = function (row, equipe1) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetEquipe1(equipe1)
}

tableMatch.prototype.SetEquipe2 = function (row, equipe2) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetEquipe2(equipe2)
}

tableMatch.prototype.SetClub1 = function (row, club1) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetClub1(club1)
}

tableMatch.prototype.SetClub2 = function (row, club2) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetClub2(club2)
}

tableMatch.prototype.SetIdEvent = function (row, id_event) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetIdEvent(id_event)
}

tableMatch.prototype.SetIdPrevEvent = function (row, id_prev_event) {
	var nbRows = this.m_array.length
	if ((row < 0) || (row >= nbRows)) return

	this.m_array[row].SetIdPrevEvent(id_prev_event)
}

tableMatch.prototype.GetRow = function (id) {
	var nbRows = this.m_array.length
	for (var row = 0; row < nbRows; row++) {
		if (this.m_array[row].GetId() == id)
			return row
	}
	return -1
}

function SecToHHMMSS (temps) {
	var h = parseInt(temps / 3600)
	var m = parseInt((temps - h * 3600) / 60)
	var s = temps - h * 3600 - m * 60

	var str = ''
	if (h <= 9) str += '0'
	str += h
	str += ':'
	if (m <= 9) str += '0'
	str += m
	str += ':'
	if (s <= 9) str += '0'
	str += s

	return str
}

function SecToMMSS (temps) {
	var m = parseInt(temps / 60)
	var s = temps - m * 60

	var str = ''
	if (m <= 9) str += '0'
	str += m
	str += ':'
	if (s <= 9) str += '0'
	str += s

	return str
}


function ImgClub48 (club) {
	var c = club.substr(0, 1)
	if ((c >= '0' && c <= '9') || club.substr(0, 2) == 'CR') {
		return "<img class='centre' src='../img/KIP/logo/" + club + "-logo.png' height='48' alt='' />"
	} else {
		nation = VerifNation(club)
		return "<img class='centre' src='../img/Nations/" + nation + ".png' height='48' alt='' />"
	}
}

function VerifNation (nation) {
	if (nation.length > 3) nation = nation.substr(0, 3)

	for (var i = 0; i < nation.length; i++) {
		var c = nation.substr(i, 1)
		if (c >= '0' && c <= '9') return 'FRA'
	}

	return nation
}

function ImgNation (nation) {
	nation = VerifNation(nation)
	if (nation.length > 0) {
		return "<img class='centre' src='../img/Nations/" + nation + ".png' height='32' alt='' />"
	} else {
		return ""
	}
}

function ImgNation48 (nation) {
	nation = VerifNation(nation)
	return "<img class='centre' src='../img/Nations/" + nation + ".png' height='48' alt='' />"
}

function ImgNation64 (nation) {
	nation = VerifNation(nation)
	return "<img class='centre' src='../img/Nations/" + nation + ".png' height='64' alt='' />"
}

function GetLabelPeriode (periode) {
	switch (periode) {
		case 'END':
			return "Finished"

		case 'ATT':
			return "Waiting"

		case 'ON':
			return ""

		case 'M1':
			return "1" // 1st Period

		case 'M2':
			return "2" // 2d Period";

		case 'P1':
			return "OVT" //"1st Prolong" Ovt = Overtime 

		case 'P2':
			return "OVT" // 2d Prolong";

		case 'TB':
			return "PEN" 	// Penalties ...

		default:
			break
	}

	return periode
}

function GetImgEvtMatch (evt_match) {
	switch (evt_match) {
		case 'B':
		case 'T':
			return "<img class='evt centre' src='img/ball.png' />"
			break

		case 'V':
			return "<img class='evt centre' src='img/greencard.png' />"
			break

		case 'J':
			return "<img class='evt centre' src='img/yellowcard.png' />"
			break

		case 'R':
			return "<img class='evt centre' src='img/redcard.png' />"
			break

		default:
			break
	}

	return ""
}

function GetLabelEvtMatch (evt_match) {
	switch (evt_match) {
		case 'B':
			return 'GOAL'

		case 'T':
			return 'SHOOT'

		case 'V':
			return 'GREEN CARD'

		case 'J':
			return 'YELLOW CARD'

		case 'R':
			return 'RED CARD'

		case 'A':
			return 'BLOCK'

		default:
			break
	}

	return evt_match
}

function RefreshCacheGlobal () {
	var nb = theContext.Match.GetCount()
	for (var i = 0; i < nb; i++) {
		if (theContext.Match.GetId(i) > 0) {
			axios({
				method: 'post',
				url: './cache/' + theContext.Match.GetId(i) + '_match_global.json',
				params: {},
				responseType: 'json'
			})
				.then(function (response) {
					ParseCacheGlobal(response.data)
				})
				.catch(function (error) {
					console.log(error)
				})
		}
	}
}

function RefreshCacheScore () {
	var nb = theContext.Match.GetCount()
	for (var i = 0; i < nb; i++) {
		if (theContext.Match.GetId(i) > 0) {
			axios({
				method: 'post',
				url: './cache/' + theContext.Match.GetId(i) + '_match_score.json',
				params: {},
				responseType: 'json'
			})
				.then(function (response) {
					ParseCacheScore(response.data)
				})
				.catch(function (error) {
					console.log(error)
				})
		}
	}
}

function RefreshCacheChrono () {
	var nb = theContext.Match.GetCount()
	for (var i = 0; i < nb; i++) {
		if (theContext.Match.GetId(i) > 0) {
			axios({
				method: 'post',
				url: './cache/' + theContext.Match.GetId(i) + '_match_chrono.json',
				params: {},
				responseType: 'json'
			})
				.then(function (response) {
					ParseCacheChrono(response.data)
				})
				.catch(function (error) {
					console.log(error)
				})
		}
	}
}

function RefreshCacheTerrain () {
	if (theContext.Event > 0) {
		axios({
			method: 'post',
			url: './cache/event' + theContext.Event + '_pitch' + theContext.Terrain + '.json',
			params: {},
			responseType: 'json'
		})
			.then(function (response) {
				ParseCacheTerrain(response.data)
			})
			.catch(function (error) {
				console.log(error)
			})
	}
}

var theContext = new Object()
theContext.Match = new tableMatch()
theContext.Terrain = 0
theContext.CountTimer = 0
theContext.Speaker = 0
theContext.Event = 0

function ParseCacheTerrain (jsonData) {
	//  if(theContext.Match.GetId(0) == -1)
	//      return; // Pas de match sélectionné

	if (typeof (jsonData.id_match) == 'undefined')
		return	// Data JSON non correcte ...

	if (theContext.Match.GetId(0) != jsonData.id_match) {

		theContext.Match.SetId(0, jsonData.id_match)

		RefreshCacheGlobal()
		RefreshCacheChrono()
		// On laisse le temps de charger les infos du match pour récupérer le drapeau pays du dernier événement
		setTimeout(RefreshCacheScore(), 800)

	}
}
