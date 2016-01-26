var theContext = new Object();
theContext.Voie = 0;
theContext.Url = '';

function Go_list_medals()
{
	var param;
	param  = "show=list_medals";
	param += "&voie="+$('#list_medals_channel').val();
	param += "&competition="+$('#list_medals_competition').val();
	Go(param);
}

function Go_referee()
{
	var param;
	param  = "show=referee";
	param += "&voie="+$('#referee_channel').val();
	param += "&match="+$('#referee_match').val();
	Go(param);
}

function Go_player()
{
	var param;
	param  = "show=player";
	param += "&voie="+$('#player_channel').val();
	param += "&match="+$('#player_match').val();
	param += "&team="+$('#player_team').val();
	param += "&number="+$('#player_number').val();
	Go(param);
}

function Go_player_medal()
{
	var param;
	param  = "show=player_medal";
	param += "&voie="+$('#player_medal_channel').val();
	param += "&match="+$('#player_medal_match').val();
	param += "&team="+$('#player_medal_team').val();
	param += "&number="+$('#player_medal_number').val();
	param += "&medal="+$('#player_medal_medal').val();
	Go(param);
}

function Go_team()
{
	var param;
	param  = "show=team";
	param += "&voie="+$('#team_channel').val();
	param += "&match="+$('#team_match').val();
	param += "&team="+$('#team_team').val();
	Go(param);
}

function Go_team_medal()
{
	var param;
	param  = "show=team_medal";
	param += "&voie="+$('#team_medal_channel').val();
	param += "&match="+$('#team_medal_match').val();
	param += "&team="+$('#team_medal_team').val();
	param += "&medal="+$('#team_medal_medal').val();
	Go(param);
}

function Go_match()
{
	var param;
	param  = "show=match";
	param += "&voie="+$('#match_channel').val();
	param += "&match="+$('#match_match').val();
	Go(param);
}

function Go_match_score()
{
	var param;
	param  = "show=match_score";
	param += "&voie="+$('#match_score_channel').val();
	param += "&match="+$('#match_score_match').val();
	Go(param);
}

function Go_list_team()
{
	var param;
	param  = "show=list_team";
	param += "&voie="+$('#list_team_channel').val();
	param += "&match="+$('#list_team_match').val();
	param += "&team="+$('#list_team_team').val();
	Go(param);
}

function Go_raz()
{
	var param;
	param  = "show=reset";
	param += "&voie="+$('#list_team_channel').val();
	Go(param);
}

function Go(param)
{
//    alert("ajax_change_tv.php?"+param);
    $.ajax({ type: "GET", url: "ajax_change_tv.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						alert(htmlData);
				}
	});
}

function RefreshTV()
{
	var param;
	param = "voie="+theContext.Voie;
	$.ajax({ type: "GET", url: "ajax_refresh_tv.php", dataType: "html", data: param, cache: false, 
                success: function(urlCurrent) {
					if (urlCurrent.length <= 0) return;
					if (theContext.Url == urlCurrent) return;

					window.location.href = './tv.php?'+urlCurrent+'&voie='+theContext.Voie;
				}
	});
}

function Init(voie)
{
	theContext.Voie = voie;
	
	if (theContext.Voie > 0)
	{
		// Refresh toutes les 2 secondes ...
		setInterval(RefreshTV, 2000);
		return;
	}
	
	// Mode "show=command"
	$('#list_medals_btn').click( function () { Go_list_medals(); return false;});
	$('#referee_btn').click( function () { Go_referee(); return false;});
	$('#player_btn').click( function () { Go_player(); return false;});
	$('#player_medal_btn').click( function () { Go_player_medal(); return false;});
	$('#team_btn').click( function () { Go_team(); return false;});
	$('#team_medal_btn').click( function () { Go_team_medal(); return false;});
	$('#match_btn').click( function () { Go_match(); return false;});
	$('#match_score_btn').click( function () { Go_match_score(); return false;});
	$('#list_team_btn').click( function () { Go_list_team(); return false;});
	$('#raz_btn').click( function () { Go_raz(); return false;});
}	
