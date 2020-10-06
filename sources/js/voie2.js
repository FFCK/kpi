var theCurrentVoie = 0;
var theCurrentVoieUrl = '';

function SetVoie(voie)
{
	theCurrentVoie = voie;
	theCurrentVoieUrl = window.location.href;
	
	if (voie > 0)
	{
		// Refresh toutes les 4 secondes ...
		setInterval(RefreshVoie, 4000);
	}
}

function RefreshVoie()
{
	var param;
	param = "voie="+theCurrentVoie;
//	alert("./live/ajax_refresh_voie.php?"+param+" -- "+theCurrentVoieUrl);
	$.ajax({ type: "POST", url: "./live/ajax_refresh_voie.php", dataType: "html", data: param, cache: false, 
                success: function(urlCurrent) {
					if (urlCurrent.length <= 0) return;
					if (theCurrentVoieUrl.lastIndexOf(urlCurrent) == -1)
					{
						theCurrentVoieUrl = urlCurrent+'?voie='+theCurrentVoie; 
//						alert("RefreshVoie !!!!! "+urlCurrent+" current="+theCurrentVoieUrl);

						if (urlCurrent.lastIndexOf("?") == -1)
							window.location.href = '/'+urlCurrent+'?voie='+theCurrentVoie;
						else
							window.location.href = '/'+urlCurrent+'&voie='+theCurrentVoie;
					}
				}
	});
}
	
function ChangeVoie(voie, url, showUrl=0)
{
	url2 = url.replace("?", "|QU|");
	for (;;)
	{
		var url3 = url2.replace("&", "|AM|");
		if (url3 == url2) break;
		url2 = url3;
	}

	var param;
	param  = "voie="+voie;
	param += "&url="+url2;

    if(showUrl > 0){
        $('#showUrl' + showUrl).val(url + "&voie="+voie);
    } else {
		$.ajax({ 
			type: "GET", 
			url: "./live/ajax_change_voie.php", 
			dataType: "html", 
			data: param, 
			cache: false, 
			success: function(htmlData) {
				alerte(htmlData);
			}
        });
    }
}

function alerte(data) {
    $('#msg p').text(data);
    $('#msg').fadeIn(500).delay(2000).fadeOut(900);
}

$('#msg').fadeOut(900);