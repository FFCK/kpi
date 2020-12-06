var theCount = 0;
var time_event = new Date();
var time_event_str;
var delay;
var interval;

function InitCache()
{
	delay = $('#delay_event').val();
	$('#info_titre').html("Don't close this page/tab !").after('<i>(let it run in background)</i><br><br>');
	$('#info').html("<b>Cache will refresh every "+delay+" seconds ... </b><br>");
	interval = setInterval(RefreshCache, delay*1000);
}

function RefreshCache()
{
	time_event.setSeconds(time_event.getSeconds() + parseInt(delay, 10));
	$('#hour_event').val(time_event.toISOString().split('T')[1].substring(0,5));

	var param = $('#event_form').serialize();
	$.ajax({ type: "GET", url: "ajax_cache_event.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						++theCount;
						$('#info').html("<b>Refresh Count = "+theCount+"<br></b>"+htmlData+"<br>");
				}
	});
}

function Init()
{
	$('#btn_go').click(function () {
		$(this).hide().after('<hr>');
		clearInterval(interval);
		time_event = new Date($('#date_event').val()+'T'+$('#hour_event').val()+':00Z');
		$('#hour_event').after(' <i>(Started at ' + time_event.toISOString().split('T')[1].substring(0,5) + ')</i>');
		InitCache();
		return false;
	});
}
  