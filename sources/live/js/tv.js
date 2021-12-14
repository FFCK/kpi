var theContext = new Object()
theContext.scenario_row = 0
theContext.scenario_url = ""
theContext.scenario_duree_max = 0
theContext.scenario_duree = 0

function Go_list_medals () {
	var param
	param = "show=list_medals"
	param += "&voie=" + document.querySelector('#list_medals_channel').value
	param += "&competition=" + document.querySelector('#list_medals_competition').value
	Go(param)
}

function Go_referee () {
	var param
	param = "show=referee"
	param += "&voie=" + document.querySelector('#referee_channel').value
	param += "&match=" + document.querySelector('#referee_match').value
	Go(param)
}

function Go_player () {
	var param
	param = "show=player"
	param += "&voie=" + document.querySelector('#player_channel').value
	param += "&match=" + document.querySelector('#player_match').value
	param += "&team=" + document.querySelector('#player_team').value
	param += "&number=" + document.querySelector('#player_number').value
	Go(param)
}

function Go_player_medal () {
	var param
	param = "show=player_medal"
	param += "&voie=" + document.querySelector('#player_medal_channel').value
	param += "&match=" + document.querySelector('#player_medal_match').value
	param += "&team=" + document.querySelector('#player_medal_team').value
	param += "&number=" + document.querySelector('#player_medal_number').value
	param += "&medal=" + document.querySelector('#player_medal_medal').value
	Go(param)
}

function Go_team () {
	var param
	param = "show=team"
	param += "&voie=" + document.querySelector('#team_channel').value
	param += "&match=" + document.querySelector('#team_match').value
	param += "&team=" + document.querySelector('#team_team').value
	Go(param)
}

function Go_team_medal () {
	var param
	param = "show=team_medal"
	param += "&voie=" + document.querySelector('#team_medal_channel').value
	param += "&match=" + document.querySelector('#team_medal_match').value
	param += "&team=" + document.querySelector('#team_medal_team').value
	param += "&medal=" + document.querySelector('#team_medal_medal').value
	Go(param)
}

function Go_match () {
	var param
	param = "show=match"
	param += "&voie=" + document.querySelector('#match_channel').value
	param += "&match=" + document.querySelector('#match_match').value
	Go(param)
}

function Go_match_score () {
	var param
	param = "show=match_score"
	param += "&voie=" + document.querySelector('#match_score_channel').value
	param += "&match=" + document.querySelector('#match_score_match').value
	Go(param)
}

function Go_list_team () {
	var param
	param = "show=list_team"
	param += "&voie=" + document.querySelector('#list_team_channel').value
	param += "&match=" + document.querySelector('#list_team_match').value
	param += "&team=" + document.querySelector('#list_team_team').value
	Go(param)
}

function Go_presentation () {
	ChangeVoie(+document.querySelector('#list_presentation_channel').value, document.querySelector('#list_presentation_url').value)
}

function Go_raz () {
	var param
	param = "show=reset"
	param += "&voie=" + document.querySelector('#list_team_channel').value
	Go(param)
}

function Go (param) {
	// $.ajax({
	// 	type: "POST", url: "ajax_change_tv.php", dataType: "html", data: param, cache: false,
	// 	success: function (htmlData) {
	// 		alert(htmlData)
	// 	}
	// })

	axios({
		method: 'post',
		url: './ajax_change_tv.php',
		params: param,
		responseType: 'text'
	})
		.then(function (response) {
			alert(response.data)
		})
		.catch(function (error) {
			console.log(error)
			return
		})

}

function Go_scenario () {
	Next_scenario()
	setInterval(RefreshScenario, 1000)
}

function Next_scenario () {
	++theContext.scenario_row
	var url = document.querySelector("#scenario_url" + theContext.scenario_row).value
	if ((url == '') || (url == "undefined"))
		theContext.scenario_row = 1

	theContext.scenario_url = document.querySelector("#scenario_url" + theContext.scenario_row).value
	theContext.scenario_duree_max = parseInt(document.querySelector("#scenario_duree" + theContext.scenario_row).value, 10)
	theContext.scenario_duree = 0

	var voie = document.querySelector("#scenario_channel").value
	ChangeVoie(voie, theContext.scenario_url)
}

function RefreshScenario () {
	++theContext.scenario_duree
	document.querySelector("#tv_message").innerHTML = "<b>Scenario en cours : " + theContext.scenario_url + " => " + theContext.scenario_duree + "/" + theContext.scenario_duree_max + "sec</b>"
	if (theContext.scenario_duree > theContext.scenario_duree_max)
		Next_scenario()
}

function Go_url_splitter () {
	var url = '/live/splitter.php'
	for (var i = 1; i < 10; i++) {
		var urlRow = document.querySelector("#scenario_url" + i).value
		if ((urlRow == '') || (urlRow == "undefined"))
			break

		urlRow = urlRow.replace("?", "|Q|")
		for (; ;) {
			var urlRow2 = urlRow.replace("&", "|A|")
			if (urlRow2 == urlRow) break
			urlRow = urlRow2
		}

		if (i == 1)
			url += "?"
		else
			url += "&"

		url += "frame" + i + "=" + urlRow
	}

	document.querySelector("#tv_message").innerHTML = "<b>URL progression : " + url
}


function Init (voie, intervalle = 3000) {
	SetVoie(voie, intervalle)

	// Mode "show=command"
	if (document.querySelector('#list_medals_btn'))
		document.querySelector('#list_medals_btn').addEventListener('click', () => { Go_list_medals(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#referee_btn').addEventListener('click', () => { Go_referee(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#player_btn').addEventListener('click', () => { Go_player(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#player_medal_btn').addEventListener('click', () => { Go_player_medal(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#team_btn').addEventListener('click', () => { Go_team(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#team_medal_btn').addEventListener('click', () => { Go_team_medal(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#match_btn').addEventListener('click', () => { Go_match(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#match_score_btn').addEventListener('click', () => { Go_match_score(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#list_team_btn').addEventListener('click', () => { Go_list_team(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#list_presentation_btn').addEventListener('click', () => { Go_presentation(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#scenario_btn').addEventListener('click', () => { Go_scenario(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#url_splitter').addEventListener('click', () => { Go_url_splitter(); return false })
	if (document.querySelector('#referee_btn'))
		document.querySelector('#raz_btn').addEventListener('click', () => { Go_raz(); return false })
}	
