var theContext = new Object();
theContext.scenario_row = 0;
theContext.scenario_url = "";
theContext.scenario_duree_max = 0;
theContext.scenario_duree = 0;

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

function Go_presentation()
{
	ChangeVoie(+$('#list_presentation_channel').val(), $('#list_presentation_url').val());
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

function Go_scenario()
{
	Next_scenario();
	setInterval(RefreshScenario, 1000);
}

function Next_scenario()
{
	++theContext.scenario_row;
	var url = $("#scenario_url"+theContext.scenario_row).val();
	if ((url == '') || (url == "undefined"))
		theContext.scenario_row = 1;
	
	theContext.scenario_url = $("#scenario_url"+theContext.scenario_row).val();
	theContext.scenario_duree_max = parseInt($("#scenario_duree"+theContext.scenario_row).val(), 10);
	theContext.scenario_duree = 0;
	
	var voie = $("#scenario_channel").val();
	ChangeVoie(voie, theContext.scenario_url);
}

function RefreshScenario()
{
	++theContext.scenario_duree;
	$("#tv_message").html("<b>Scenario en cours : "+theContext.scenario_url+" => "+theContext.scenario_duree+"/"+theContext.scenario_duree_max+"sec</b>");
	if (theContext.scenario_duree > theContext.scenario_duree_max)
		Next_scenario();
}

function Init(voie)
{
	SetVoie(voie);
	
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
	$('#list_presentation_btn').click( function () { Go_presentation(); return false;});
	$('#scenario_btn').click( function () { Go_scenario(); return false;});
	
	$('#raz_btn').click( function () { Go_raz(); return false;});
}	
