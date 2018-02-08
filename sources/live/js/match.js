function recordMatch(id)
{
	this.m_id = id;

	this.m_tick_global = '';
	this.m_tick_score = '';
	this.m_tick_chrono = '';
	
	this.m_etat = '?';
	this.m_statut = '?';
	this.m_etat_prev = '?';
	
	this.m_periode = '';
	this.m_temps_ecoule = 0;
	this.m_temps_reprise = 0;
	this.m_temps_max = 0;
	
	this.m_equipe1 = '';
	this.m_equipe2 = '';
	this.m_id_event = 0;
	
	this.m_score1 = 0;
	this.m_score2 = 0;
}

recordMatch.prototype.GetId = function() {	return this.m_id; }
recordMatch.prototype.GetTickGlobal = function() { return this.m_tick_global; }
recordMatch.prototype.GetTickScore = function() { return this.m_tick_score; }
recordMatch.prototype.GetTickChrono = function() { return this.m_tick_chrono; }

recordMatch.prototype.GetEtat = function() { return this.m_etat; }
recordMatch.prototype.GetEtatPrev = function() { return this.m_etat_prev; }
recordMatch.prototype.GetStatut = function() { return this.m_statut; }
recordMatch.prototype.GetScore1 = function() { return this.m_score1; }
recordMatch.prototype.GetScore2 = function() { return this.m_score2; }

recordMatch.prototype.GetPeriode = function() { return this.m_periode; }
recordMatch.prototype.GetTempsEcoule = function() { return this.m_temps_ecoule; }
recordMatch.prototype.GetTempsReprise = function() { return this.m_temps_reprise; }
recordMatch.prototype.GetTempsMax = function() { return this.m_temps_max; }

recordMatch.prototype.GetEquipe1 = function() { return this.m_equipe1; }
recordMatch.prototype.GetEquipe2 = function() { return this.m_equipe2; }
recordMatch.prototype.GetIdEvent = function() { return this.m_id_event; }

recordMatch.prototype.SetId = function(id) { this.m_id = id; }
recordMatch.prototype.SetTickGlobal = function(tick) { this.m_tick_global = tick; }
recordMatch.prototype.SetTickScore = function(tick) { this.m_tick_score = tick; }
recordMatch.prototype.SetTickChrono = function(tick) { this.m_tick_chrono = tick; }

recordMatch.prototype.SetEtat = function(etat) { this.m_etat_prev = this.m_etat; this.m_etat = etat; }
recordMatch.prototype.SetStatut = function(statut) { this.m_statut = statut; }

recordMatch.prototype.SetPeriode = function(periode) { this.m_periode = periode; }
recordMatch.prototype.SetTempsEcoule = function(temps_ecoule) { this.m_temps_ecoule = temps_ecoule; }
recordMatch.prototype.SetTempsReprise = function(temps_reprise) { this.m_temps_reprise = temps_reprise; }
recordMatch.prototype.SetTempsMax = function(temps_max) { this.m_temps_max = temps_max; }

recordMatch.prototype.SetEquipe1 = function(equipe1) { this.m_equipe1 = equipe1; }
recordMatch.prototype.SetEquipe2 = function(equipe2) { this.m_equipe2 = equipe2; }
recordMatch.prototype.SetIdEvent = function(id_event) { this.m_id_event = id_event; }

recordMatch.prototype.SetScore1 = function(score1) { this.m_score1 = score1; }
recordMatch.prototype.SetScore2 = function(score2) { this.m_score2 = score2; }

function tableMatch()
{
	this.m_array = new Array();
}

tableMatch.prototype.Add = function(id) 
{
	var rMatch = new recordMatch(id);
	this.m_array.push(rMatch);
}

tableMatch.prototype.GetCount = function()
{
	return this.m_array.length;
}

tableMatch.prototype.GetId = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetId();
}

tableMatch.prototype.GetTickGlobal = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTickGlobal();
}

tableMatch.prototype.GetTickScore = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTickScore();
}

tableMatch.prototype.GetScore1 = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetScore1();
}

tableMatch.prototype.GetScore2 = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetScore2();
}

tableMatch.prototype.GetTickChrono = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTickChrono();
}

tableMatch.prototype.GetEtat = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetEtat();
}

tableMatch.prototype.GetEtatPrev = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetEtatPrev();
}

tableMatch.prototype.GetStatut = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetStatut();
}

tableMatch.prototype.GetPeriode = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetPeriode();
}

tableMatch.prototype.GetTempsEcoule = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTempsEcoule();
}

tableMatch.prototype.GetTempsReprise = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTempsReprise();
}

tableMatch.prototype.GetTempsMax = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetTempsMax();
}

tableMatch.prototype.GetEquipe1 = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetEquipe1();
}

tableMatch.prototype.GetEquipe2 = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetEquipe2();
}

tableMatch.prototype.GetIdEvent = function(row)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].GetIdEvent();
}

tableMatch.prototype.SetId = function(row, id)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return '';
	
	return this.m_array[row].SetId(id);
}

tableMatch.prototype.SetTickGlobal = function(row, tick)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTickGlobal(tick);
}

tableMatch.prototype.SetTickScore = function(row, tick)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTickScore(tick);
}

tableMatch.prototype.SetScore1 = function(row, score1)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetScore1(score1);
}

tableMatch.prototype.SetScore2 = function(row, score2)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetScore2(score2);
}

tableMatch.prototype.SetTickChrono = function(row, tick)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTickChrono(tick);
}

tableMatch.prototype.SetEtat = function(row, etat)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetEtat(etat);
}

tableMatch.prototype.SetStatut = function(row, statut)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetStatut(statut);
}

tableMatch.prototype.SetPeriode = function(row, periode)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetPeriode(periode);
}

tableMatch.prototype.SetTempsEcoule = function(row, temps_ecoule)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTempsEcoule(temps_ecoule);
}

tableMatch.prototype.SetTempsReprise = function(row, temps_reprise)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTempsReprise(temps_reprise);
}

tableMatch.prototype.SetTempsMax = function(row, temps_max)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetTempsMax(temps_max);
}

tableMatch.prototype.SetEquipe1 = function(row, equipe1)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetEquipe1(equipe1);
}

tableMatch.prototype.SetEquipe2 = function(row, equipe2)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetEquipe2(equipe2);
}

tableMatch.prototype.SetIdEvent = function(row, id_event)
{
	var nbRows = this.m_array.length;
	if ( (row < 0) || (row >= nbRows) ) return;
	
	this.m_array[row].SetIdEvent(id_event);
}

tableMatch.prototype.GetRow = function(id)
{
	var nbRows = this.m_array.length;
	for (var row=0;row<nbRows;row++)
	{
		if (this.m_array[row].GetId() == id)
			return row;
	}
	return -1;
} 

function SecToHHMMSS(temps)
{
	var h = parseInt(temps/3600);
	var m = parseInt((temps-h*3600)/60);
	var s = temps-h*3600-m*60;
	
	var str = '';
	if (h <= 9) str += '0';
	str += h;
	str += ':';
	if (m <= 9) str += '0';
	str += m;
	str += ':';
	if (s <= 9) str += '0';
	str += s;
	
	return str;
}

function SecToMMSS(temps)
{
	var m = parseInt(temps/60);
	var s = temps-m*60;
	
	var str = '';
	if (m <= 9) str += '0';
	str += m;
	str += ':';
	if (s <= 9) str += '0';
	str += s;
	
	return str;
}

function VerifNation(nation)
{
	if (nation.length > 3) nation = nation.substr(0,3);

	for (var i=0;i<nation.length;i++)
	{
		var c = nation.substr(i,1);
		if (c >= '0' && c <= '9') return 'FRA';
	}
	
	return nation;
}

function ImgNation(nation)
{
	nation = VerifNation(nation);
	return "<img class='centre' src='./img/nation/"+nation+".png' height='32' width='32' />";
}

function ImgNation48(nation)
{
	nation = VerifNation(nation);
	return "<img class='centre' src='./img/nation/"+nation+".png' height='48' width='48' />";
}

function ImgNation64(nation)
{
	nation = VerifNation(nation);
	return "<img class='centre' src='./img/nation/"+nation+".png' height='64' width='64' />";
}

function GetLabelPeriode(periode)
{
	switch(periode)
	{
		case 'END':
		return "Finished";

		case 'ATT':
		return "Waiting";

		case 'ON':
		return "";

		case 'M1':
		return "1"; // 1st Period
		
		case 'M2':
		return "2"; // 2d Period";

		case 'P1':
		return "OVT"; //"1st Prolong" Ovt = Overtime 
		
		case 'P2':
		return "OVT"; // 2d Prolong";
			
		case 'TB':
		return "PEN"; 	// Penalties ...
		
		default:
		break;
	}
	
	return periode;
}

function GetImgEvtMatch(evt_match)
{
	switch(evt_match)
	{
		case 'B':
		case 'T':
		return "<img class='centre' src='./img/carton-goal.png' height='32' width='32' />";

		case 'V':
		return "<img class='centre' src='./img/carton-vert.png' height='32' width='32' />";

		case 'J':
		return "<img class='centre' src='./img/carton-jaune.png' height='32' width='32' />";

		case 'R':
		return "<img class='centre' src='./img/carton-rouge.png' height='32' width='32' />";
		
		default:
		break;
	}
	
	return "";
}

function GetLabelEvtMatch(evt_match)
{
	switch(evt_match)
	{
		case 'B':
		return 'GOAL';

		case 'T':
		return 'SHOOT';

		case 'V':
		return 'GREEN CARD';

		case 'J':
		return 'YELLOW CARD';

		case 'R':
		return 'RED CARD';

		case 'A':
		return 'BLOCK';
		
		default:
		break;
	}
	
	return evt_match;
}

function RefreshCacheGlobal()
{
	var nb = theContext.Match.GetCount();
//	alert("nb Match = "+nb);
	
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

function RefreshCacheTerrain()
{
	$.ajax({
		url : './cache/'+theContext.Terrain+'_terrain.json',
		type: 'GET',
		dataType: 'text',
		cache: false,
		async: false,
		success: ParseCacheTerrain
	});
}

var theContext = new Object();
theContext.Match = new tableMatch();
theContext.Terrain = 0;
theContext.CountTimer = 0;
theContext.Speaker = 0;
theContext.Event = 0;

function ParseCacheTerrain(jsonTxt)
{
	var iFind = jsonTxt.lastIndexOf("@@END@@");
	if (iFind == -1) return; // Aucune précence de @@END@@ => le fichier cache n'est pas complet ... => On sort

	jsonTxt = jsonTxt.substring(0,iFind);
	var jsonData = JSON && JSON.parse(jsonTxt) || $.parseJSON(jsonTxt);
	
	if (typeof(jsonData.id_match) == 'undefined')
		return;	// Data JSON non correcte ...

//	alert("ET1="+jsonTxt);
	if (theContext.Match.GetId(0) != jsonData.id_match)
	{
//		alert("ET2 "+jsonData.id_match);

		theContext.Match.SetId(0,jsonData.id_match);

		RefreshCacheScore();
		RefreshCacheChrono();
		RefreshCacheGlobal();
		
		if (theContext.Speaker == 1)
			$('#lien_pdf').html("<a target='_blank' href='https://www.kayak-polo.info/PdfMatchMulti.php?listMatch="+jsonData.id_match+"'>Lien vers la Feuille de Match</a>");
	}
}
