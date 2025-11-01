var theCurrentVoie = 0
var theCurrentVoieUrl = ''

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
      const resultat = await axiosLikeFetch({
        method: 'post',
        url: '/live/cache/voie_' + voie + '.json',
        responseType: 'json'
      })
      if (resultat.data.url === '') {
        const resultat2 = await axiosLikeFetch({
          method: 'post',
          url: '/live/cache/voie_' + voie_min + '.json',
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

function RefreshVoie () {
  axiosLikeFetch({
    method: 'post',
    url: '/live/cache/voie_' + theCurrentVoie + '.json',
    responseType: 'json'
  })
    .then(function (response) {
      if (response.data.length <= 0) return
      const responseUrl = decodeURIComponent(response.data.url)
      if (theCurrentVoieUrl.lastIndexOf(responseUrl) == -1) {
        theCurrentVoieUrl = responseUrl + '?voie=' + theCurrentVoie

        if (responseUrl.lastIndexOf("?") == -1) {
          window.location.href = '/' + responseUrl + '?voie=' + theCurrentVoie
        } else {
          window.location.href = '/' + responseUrl + '&voie=' + theCurrentVoie
        }
      }
    })
    .catch(function (error) {
      console.log(error)
    })
}

function ChangeVoie (voie, url, showUrl = 0) {
  url2 = url.replace("?", "|QU|")
  for (; ;) {
    var url3 = url2.replace("&", "|AM|")
    url3 = url3.replace('#', '|HA|')
    if (url3 == url2) break
    url2 = url3
  }

  if (showUrl > 0) {
    const baseurl = window.location.origin + '/'
    document.querySelector('#showUrl' + showUrl).value = baseurl + url + "&voie=" + voie
  } else {
    axiosLikeFetch({
      method: 'post',
      url: 'ajax_change_voie.php',
      params: {
        voie: voie,
        url: url2
      },
      responseType: 'text'
    })
      .then(function (response) {
        alerte(response.data)
      })
      .catch(function (error) {
        console.log(error)
      })
  }
}

function alerte (data) {
  if (document.querySelector('#msg p')) {
    document.querySelector('#msg p').innerHTML = data
    document.querySelector('#msg').style.display = 'block'
    setTimeout(function () {
      document.querySelector('#msg').style.display = 'none'
    }, 3000)
  }
}

if (document.querySelector('#msg')) {
  document.querySelector('#msg').style.display = 'none'
}
