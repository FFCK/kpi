var theCount = 0
var time_event = new Date()
var time_event_str
var delay
var interval

function InitCache () {
	delay = $('#delay_event').val()
	$('#info_titre').html("Don't close this page/tab !").after('<i>(let it run in background)</i><br><br>')
	$('#info').html("<b>Cache will refresh every " + delay + " seconds ... </b><br>")
	RefreshCache()
	interval = setInterval(RefreshCache, delay * 1000)
}

function RefreshCache () {
	time_event.setSeconds(time_event.getSeconds() + parseInt(delay, 10))
	$('#hour_event').val(time_event.toISOString().split('T')[1].substring(0, 5))

	var param = $('#event_form').serialize()
	$.ajax({
		type: "GET",
		url: "ajax_cache_event.php",
		dataType: "json",
		data: param,
		cache: false,
		success: function (data) {
			++theCount
			texte = '<b>Refresh Count = ' + theCount + '</b>'
			texte += '<br>Current Time : ' + data.time.currentTime + ' - Working time : ' + data.time.workingTime + '<br></br>'
			texte += '<table class="table table-bordered table-condensed"><thead>'
			texte += '<tr><th></th><th colspan="3">Current game</th><th colspan="3">Next game</th></tr>'
			texte += '<tr><th>Pitch</th><th>Time</th><th>Num</th><th>Id</th><th>Time</th><th>Num</th><th>Id</th></tr>'
			texte += '</thead><tbody>'
			data.pitches.forEach((item) => {
				texte += '<tr><td>' + item.pitch + '</td>'
				if (item.game) {
					texte += '<td>' + item.time + '</td><td>#' + item.num + '</td><td>' + item.game + '</td>'
				} else {
					texte += '<td colspan="3">Waiting...</td>'
				}
				if (item.next.id != null) {
					texte += '<td>' + item.next.time + '</td><td>#' + item.next.num + '</td><td>' + item.next.id + '</td>'
				} else {
					texte += '<td colspan="3">Waiting...</td>'
				}
				texte += '</tr></tbody></table>'
			})
			$('#info').html(texte)
		}
	})
}

function Init () {
	$('#idevent').change(function () {
		window.location.href = "?evt=" + $(this).val()
	})

	$('.btn_date_evt').click(function (e) {
		e.preventDefault()
		$('#date_event').val($(this).attr('data-date'))
		$('#hour_event').val($(this).attr('data-heure'))
		time_event = new Date($('#date_event').val() + 'T' + $('#hour_event').val() + ':00Z')
		time_event.setMinutes(time_event.getMinutes() - parseInt($('#offset_event').val(), 10))
		$('#hour_event').val(time_event.toISOString().split('T')[1].substring(0, 5))

	})

	$('#btn_go').click(function () {
		$(this).hide().after('<hr>')
		$('input').prop('readonly', true)
		$('select, button').prop('disabled', true)
		clearInterval(interval)
		time_event = new Date($('#date_event').val() + 'T' + $('#hour_event').val() + ':00Z')
		$('#hour_event').after(' <i>(Started at ' + time_event.toISOString().split('T')[1].substring(0, 5) + ')</i>')
		InitCache()
		return false
	})
}
