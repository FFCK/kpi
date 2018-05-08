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
//	alert("ajax_refresh_voie.php?"+param+" -- "+theCurrentVoieUrl);
	$.ajax({ type: "GET", url: "ajax_refresh_voie.php", dataType: "html", data: param, cache: false, 
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

    if(showUrl > 0){
        param = param.replace('&voie=', '&v=');
        jq('#showUrl' + showUrl).val('live/tv2.php?' + param);
    } else {
        $.ajax({ type: "GET", url: "ajax_change_voie.php", dataType: "html", data: param, cache: false, 
                    success: function(htmlData) {
                            $("#tv_message").html(htmlData);
                    }
        });
    }
}