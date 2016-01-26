var theCount = 0;

function InitCache()
{
	var delay = $('#delay_event').val();
	$('#info').html("<BIG>Traitement va se lancer toute les "+delay+" secondes ... </BIG>");
	setInterval(RefreshCache, delay*1000);
}

function RefreshCache()
{
	var param = $('#event_form').serialize();
//    alert("ajax_cache_event.php?"+param);
	$.ajax({ type: "GET", url: "ajax_cache_event.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						++theCount;
						$('#info').html("<BIG>Refresh Count = "+theCount+", "+htmlData+"</BIG>");
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
  