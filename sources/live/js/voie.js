var theCurrentVoie = 0;
var theCurrentVoieUrl = '';

// jq(document).ready(function() {

	async function SetVoie (voie, intervalle = 3000) {
		theCurrentVoie = voie
		theCurrentVoieUrl = window.location.href
		if (intervalle < 500) {
			intervalle = 500
		}
		if (voie > 0 && voie < 100) {
			setInterval(RefreshVoie, intervalle)
		} else if (voie >= 100) {
			const scene = voie % 100
			const voie_min = voie - scene + 1
			const voie_max = voie_min + 9
			if (voie === voie_max) {
				voie = voie_min
			} else {
				voie++
			}
	
			try {
				const resultat = await axios({
					method: 'post',
					url: './cache/voie_' + voie + '.json',
					responseType: 'json'
				})
				if (resultat.data.url === '') {
					const resultat2 = await axios({
						method: 'post',
						url: './cache/voie_' + voie_min + '.json',
						responseType: 'json'
					})
					RefreshScene(resultat2.data, intervalle)
				} else {
					RefreshScene(resultat.data, intervalle)
				}
			} catch (error) {
				console.error(error)
			}
		}
	}

	function RefreshScene (result, intervalle) {
		const responseUrl = decodeURIComponent(result.url)
		const responseVoie = decodeURIComponent(result.voie)
		const responseIntervalle = decodeURIComponent(result.intervalle)
		if (responseUrl.lastIndexOf("?") == -1) {
			newUrl = '/' + responseUrl + '?voie=' + responseVoie + '&intervalle=' + responseIntervalle
		} else {
			newUrl = '/' + responseUrl + '&voie=' + responseVoie + '&intervalle=' + responseIntervalle
		}
		setTimeout(function () { window.location.href = newUrl }, intervalle)
	}
	
	function RefreshVoie()
	{
		$.ajax({
			type: "POST",
			url: './cache/voie_' + theCurrentVoie + '.json', 
			dataType: "json",
			cache: false, 
			success: function(response) {
				if (response.length <= 0) return
				const responseUrl = decodeURIComponent(response.url)
				if (theCurrentVoieUrl.lastIndexOf(responseUrl) == -1) {
					theCurrentVoieUrl = responseUrl+'?voie='+theCurrentVoie; 
					if (responseUrl.lastIndexOf("?") == -1) {
						window.location.href = '/'+responseUrl+'?voie='+theCurrentVoie;
					} else {
						window.location.href = '/'+responseUrl+'&voie='+theCurrentVoie;
					}
				}
			},
			error: function(error) {
				console.log(error)
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
			const baseurl = window.location.origin + '/'
			document.querySelector('#showUrl' + showUrl).value = baseurl + url + "&voie=" + voie	
		} else {
			$.ajax({ 
				type: "GET", 
				url: "ajax_change_voie.php", 
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

// });