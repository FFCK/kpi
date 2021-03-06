function RefreshHorloge()
{
	if (typeof(theContext.temps_offset) == 'undefined')
	{
		// Prise de l'Offset entre le temps du serveur et le temps de la machine cliente ...
		$.ajax({
			url : './get_sec.php',
			type: 'GET',
			dataType: 'text',
			cache: false,
			async: false,
			success: function(flux) { 	
				var now = new Date();
				var temps_actuel = now.getHours()*3600+now.getMinutes()*60+now.getSeconds();
				theContext.temps_offset = temps_actuel - parseInt(flux); 
			}
		});
		return;
	}
	
	var nb = theContext.Match.GetCount();
	for (var i=0;i<nb;i++)
	{
		if ( (theContext.Match.GetEtat(i) == 'run') || (theContext.Match.GetEtat(i) == 'start') )
		{
			var now = new Date();
			var temps_actuel = now.getHours()*3600+now.getMinutes()*60+now.getSeconds();
			
			// var temps_match = theContext.Match.GetTempsEcoule(i) + temps_actuel - theContext.temps_offset;
			var temps_running = temps_actuel - theContext.Match.GetTempsReprise(i) - theContext.temps_offset;
/*
			alert('temps_REPRISE2='+ theContext.Match.GetTempsReprise(i));
			alert('temps_ACTUEL2='+ temps_actuel);
			alert('temps_OFFSET2='+theContext.temps_offset);
			alert('temps_RUNNING='+(temps_running).toString());
*/			
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i) - temps_running;
			if (temps_restant < 0) temps_restant = 0;
	
			$('#match_horloge').html(SecToMMSS(temps_restant));
			$('#match_periode').html(GetLabelPeriode(theContext.Match.GetPeriode(i).replace('M1', '1st').replace('M2', '2nd')));
/*			
			if (theContext.Match.GetEtat(i) != theContext.Match.GetEtatPrev(i))
			{
				var periode = theContext.Match.GetPeriode(i);
				var htmlNext = "<img src='./img/flag-green.png' height='32' width='32' />&nbsp;<span class='etat_start'>"+periode+"</span>";
				$('#match_horloge_etat').html(htmlNext);
				theContext.Match.SetEtat(i, theContext.Match.GetEtat(i));
			}
*/
		}
		else
		{
			var temps_restant = theContext.Match.GetTempsMax(i) - theContext.Match.GetTempsEcoule(i);
			if (temps_restant < 0) temps_restant = 0;
			
			// Evolution Chrono ...
			if (theContext.Match.GetStatut(i) == 'END')
				temps_restant = 0;

			$('#match_horloge').html(SecToMMSS(temps_restant));
			$('#match_periode').html(GetLabelPeriode(theContext.Match.GetPeriode(i).replace('M1', '1st').replace('M2', '2nd')));

/*			
			if (theContext.Match.GetEtat(i) != theContext.Match.GetEtatPrev(i))
			{
				var periodeEtat = theContext.Match.GetPeriode(i)+" : Arrêt ";
				var htmlNext = "<img src='./img/stop.png' height='32' width='32' />&nbsp;<span class='etat_stop'>"+periodeEtat+"</span>";
				$('#match_horloge_etat').html(htmlNext);
				theContext.Match.SetEtat(i, theContext.Match.GetEtat(i));
			}
*/
		}
	}
	
	++theContext.CountTimer;
//	if (theContext.CountTimer % 2 == 0)
		RefreshCacheChrono();

//	if (theContext.CountTimer % 2 == 0)
		RefreshCacheScore();
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

	if (jsonData.tick == theContext.Match.GetTickScore(rowMatch))
		return;	// Fichier de Cache déja pris en compte ...

	theContext.Match.SetTickScore(rowMatch, jsonData.tick);
	theContext.Match.SetPeriode(rowMatch, jsonData.periode);
	
	var nbEvents = jsonData.event.length;
	if (nbEvents > 0) {
		var lastId = jsonData.event[0].Id;
		if ((theContext.Match.GetIdEvent(rowMatch) != lastId) && (theContext.Match.GetIdEvent(rowMatch) >= 0)) {
			var line;
			if (jsonData.event[0].Equipe_A_B == 'A') {
				line = ImgNation48(theContext.Match.GetEquipe1(rowMatch));
                line += '&nbsp;' + theContext.Match.GetEquipe1(rowMatch);
            } else {
				line = ImgNation48(theContext.Match.GetEquipe2(rowMatch));
                line += '&nbsp;' + theContext.Match.GetEquipe2(rowMatch).substring(0, 3);
            }
			line += "&nbsp;<span>";
//			line  = GetImgEvtMatch(jsonData.event[0].Id_evt_match);
//			line += "&nbsp;";
//			line += GetLabelEvtMatch(jsonData.event[0].Id_evt_match);
			$('#match_event_line1').html(line);

            // console.log(jsonData.event[0].Numero);
			if (jsonData.event[0].Numero == 'undefi') {
				if (jsonData.event[0].Equipe_A_B == 'A')
					line = "Team "+theContext.Match.GetEquipe1(rowMatch).substring(0, 3);
				else	
					line = "Team "+theContext.Match.GetEquipe2(rowMatch).substring(0, 3);
			} else {
                if(jsonData.event[0].Capitaine != 'E') {
                    line = '<span class="clair">' + jsonData.event[0].Numero + '</span>&nbsp;';
                }
				line += ' ';
				line += jsonData.event[0].Nom;
				line += ' ';
				line += jsonData.event[0].Prenom;
                
                if(jsonData.event[0].Capitaine == 'C') {
                    line += ' <span class="label label-warning capitaine">C</span>';
                } else if(jsonData.event[0].Capitaine == 'E') {
                    line += ' (Coach)';
                }
			}
			line += "</span>";
			$('#match_event_line2').html(line);
            
            $('#goal_card').html(GetImgEvtMatch(jsonData.event[0].Id_evt_match));
			
			$('#bandeau_goal').fadeIn(600).delay(6000).fadeOut(900);
		}

		theContext.Match.SetIdEvent(rowMatch, lastId);
	}
	
	var score1 = jsonData.score1;
	if ( ((score1 == '') || (score1 == null)) && (jsonData.periode != 'ATT'))
		score1 = '0';

	var score2 = jsonData.score2;
	if ( ((score2 == '') || (score2 == null)) && (jsonData.periode != 'ATT'))
		score2 = '0';
	
	$('#score1').html(score1);
	$('#score2').html(score2);
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
	
	var temps_max = jsonData.max_time;
	if (jsonData.max_time.length == 5)
	{
		temps_max = parseInt(temps_max.substr(0,2))*60 + parseInt(temps_max.substr(3,2));
		theContext.Match.SetTempsMax(rowMatch, temps_max);
	}

	var temps_ecoule = temps_max - parseInt(parseInt(jsonData.run_time)/1000);
	var temps_reprise = parseInt(jsonData.start_time_server);

/*
	alert("Etat = "+jsonData.action);
	alert("Temps_max = "+temps_max);
	alert("Run_time = "+jsonData.run_time);
	alert("Temps_ecoule="+temps_ecoule);
	alert("Temps_reprise="+temps_reprise);
*/
	
	theContext.Match.SetTempsEcoule(rowMatch, temps_ecoule);
	theContext.Match.SetTempsReprise(rowMatch, temps_reprise);
}

function ParseCacheGlobal(jsonTxt)
{
	var iFind = jsonTxt.lastIndexOf("@@END@@");
	if (iFind == -1) return; // Aucune précence de @@END@@ => le fichier cache n'est pas complet ... => On sort
	
	jsonTxt = jsonTxt.substring(0,iFind);
	jsonData = JSON && JSON.parse(jsonTxt) || $.parseJSON(jsonTxt);
    // console.log(jsonData);
  
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

/*	
	if (jsonData.statut == 'END')
	{
		window.location.href = "./presentation.php?terrain="+theContext.Terrain+"&speaker="+theContext.Speaker;
		return;
	}
*/

	$('#match_nom').html(jsonData.competition);
	
	var equipe1 = jsonData.equipe1.nom;
	equipe1 = equipe1.replace(" Women", " W.");
//	equipe1 = equipe1.replace(" Men", " M.");
    equipe1 = equipe1;
	$('#equipe1').html(equipe1);
	
	var equipe2 = jsonData.equipe2.nom;
	equipe2 = equipe2.replace(" Women", " W.");
//	equipe2 = equipe2.replace(" Men", " M.");
    equipe2 = equipe2;
	$('#equipe2').html(equipe2);
	
	theContext.Match.SetEquipe1(rowMatch, jsonData.equipe1.club);
	theContext.Match.SetEquipe2(rowMatch, jsonData.equipe2.club);

	$('#nation1').html(ImgClub48(jsonData.equipe1.club));
	$('#nation2').html(ImgClub48(jsonData.equipe2.club));
    
    $('#categorie').html(jsonData.categ + ' - ' + jsonData.phase);
    
    $('#lien_pdf').html('<a href="../PdfMatchMulti.php?listMatch=' 
            + jsonData.id_match 
            + '" target="_blank" class="btn btn-primary">Report <span class="badge">' + jsonData.numero_ordre + '</span></a>');
    $('#terrain').html('Pitch ' + jsonData.terrain);
	
/* Joueurs 
	var htmlData = '';
	for (i=0;i<jsonData.equipe1.joueurs.length;i++)
	{
		htmlData += '<p class="text-left">'+
					jsonData.equipe1.joueurs[i].Numero+' : '+
					jsonData.equipe1.joueurs[i].Nom+' '+
					jsonData.equipe1.joueurs[i].Prenom+' ('+
					jsonData.equipe1.joueurs[i].Capitaine+')'+
					'</p>';
	}
	$('#competiteurs1').html(htmlData);

	htmlData = '';
	for (i=0;i<jsonData.equipe2.joueurs.length;i++)
	{
		htmlData += '<p class="text-left">'+
					jsonData.equipe2.joueurs[i].Numero+' : '+
					jsonData.equipe2.joueurs[i].Nom+' '+
					jsonData.equipe2.joueurs[i].Prenom+' ('+
					jsonData.equipe2.joueurs[i].Capitaine+')'+
					'</p>';
	}
	$('#competiteurs2').html(htmlData);
*/
}

function Init(event, terrain, speaker, voie)
{
	theContext.Event = event;
	theContext.Terrain = terrain;
	theContext.Speaker = speaker;

	theContext.Match.Add(-1); 
	RefreshCacheTerrain();

	RefreshCacheGlobal();

	// Refresh du cache Terrain toute les 10 secondes ...
	setInterval(RefreshCacheTerrain, 5000);
	// Refresh du cache Global toute les 30 secondes ...
	setInterval(RefreshCacheGlobal, 10000);

	// Refresh du cache Score toute les 5 secondes ...
//	setInterval(RefreshCacheScore, 5500);
	
	// Refresh Chrono toutes les 2 secondes  ...
//	setInterval(RefreshCacheChrono, 2500);
	
	// Refresh Horloge toutes les secondes  ...
	
	SetVoie(voie);
}	

