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
	jq.ajax({ type: "GET", url: "./live/ajax_refresh_voie.php", dataType: "html", data: param, cache: false, 
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
	
function ChangeVoie(voie, url)
{
	url = url.replace("?", "|QU|");
	for (;;)
	{
		var url2 = url.replace("&", "|AM|");
		if (url2 == url) break;
		url = url2;
	}

	var param;
	param  = "voie="+voie;
	param += "&url="+url;

//    alert("ajax_change_voie.php?"+param);
    jq.ajax({ type: "GET", url: "./live/ajax_change_voie.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						jq("#tv_message").html(htmlData);
				}
	});
}