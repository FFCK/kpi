var theCurrentVoie = 0;
var theCurrentVoieUrl = '';

// jq(document).ready(function() {

	function SetVoie(voie, intervalle=3000)
	{
		theCurrentVoie = voie;
		theCurrentVoieUrl = window.location.href;
		if (voie > 0 && voie < 100)
		{
			setInterval(RefreshVoie, intervalle);
		}
		else if (voie >= 100)
		{
			RefreshScene(voie, intervalle);
		}
	}

	function RefreshVoie()
	{
		var param;
		param = "voie="+theCurrentVoie;
		$.ajax({ type: "POST", url: "ajax_refresh_voie.php", dataType: "html", data: param, cache: false, 
			success: function(urlCurrent) {
				if (urlCurrent.length <= 0) return;
				if (theCurrentVoieUrl.lastIndexOf(urlCurrent) == -1)
				{
					theCurrentVoieUrl = urlCurrent+'?voie='+theCurrentVoie; 

					if (urlCurrent.lastIndexOf("?") == -1) {
						window.location.href = '/'+urlCurrent+'?voie='+theCurrentVoie;
					} else {
						window.location.href = '/'+urlCurrent+'&voie='+theCurrentVoie;
					}
				}
			}
		});
	}
		
	function RefreshScene(voie, intervalle)
	{
		$.post(
			"ajax_refresh_scene.php",
			{ voie: voie },
			function(data) {
				if (data.Url.length <= 0) return;
				if (data.Url.lastIndexOf("?") == -1) {
					newUrl = '/'+data.Url+'?voie='+data.Voie+'&intervalle='+data.intervalle;
				} else {
					newUrl = '/'+data.Url+'&voie='+data.Voie+'&intervalle='+data.intervalle;
				}
				setTimeout(function(){window.location.href = newUrl}, intervalle);
			},
			'json'
		);
		
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
			$.ajax({ type: "POST", url: "ajax_change_voie.php", dataType: "html", data: param, cache: false, 
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

// });