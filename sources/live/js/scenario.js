var intervalId = null;
var theCount = 0;
var theContext = new Object();
theContext.scenario_row = 0;
theContext.scenario_url = "";
theContext.scenario_duree_max = 0;
theContext.scenario_duree = 0;

function Go_scenario() {
	Next_scenario();
	intervalId = setInterval(RefreshScenario, 1000);
}

function Next_scenario() {
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

function RefreshScenario() {
	++theContext.scenario_duree;
	$("#tv_message").html("<b>Scenario en cours : "+theContext.scenario_url+" => "+theContext.scenario_duree+"/"+theContext.scenario_duree_max+"sec</b>");
	if (theContext.scenario_duree > theContext.scenario_duree_max)
		Next_scenario();
}


function Init() {
    $('#raz_btn').hide();
    
    $('#scenario_btn').click( function () { 
        $(this).hide();
        $('#raz_btn').show();
        Go_scenario();
        return false;
    });
    
    $('#raz_btn').click( function () { 
        clearInterval(intervalId);
        $(this).hide();
        $('#scenario_btn').show();
    });
}
  