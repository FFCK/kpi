var theCount = 0;

function InitCache()
{
	var delay = $('#delay_event').val();
	$('#info').html("<b>Le traitement va se lancer toute les "+delay+" secondes ... </b><br>");
	setInterval(RefreshCache, delay*1000);
}

function RefreshCache()
{
	var param = $('#event_form').serialize();
//    alert("ajax_cache_event.php?"+param);
	$.ajax({ type: "GET", url: "ajax_cache_event.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						++theCount;
						$('#info').html("<b>Refresh Count = "+theCount+", <br></b>"+htmlData+"<br>");
				}
	});
}

function Init()
{
	$('#btn_go').click(function () {
		InitCache();
		return false;
	});
}
  