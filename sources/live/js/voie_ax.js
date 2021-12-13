var theCurrentVoie = 0
var theCurrentVoieUrl = ''

function SetVoie (voie, intervalle = 3000) {
  theCurrentVoie = voie
  theCurrentVoieUrl = window.location.href
  if (intervalle < 500) { intervalle = 500 }
  if (voie > 0 && voie < 100) {
    setInterval(RefreshVoie, intervalle)
  }
  else if (voie >= 100) {
    RefreshScene(voie, intervalle)
  }
}

function RefreshVoie () {
  axios({
    method: 'post',
    url: 'ajax_refresh_voie.php',
    params: {
      voie: theCurrentVoie
    },
    responseType: 'text'
  })
    .then(function (response) {
      if (response.data.length <= 0) return
      if (theCurrentVoieUrl.lastIndexOf(response.data) == -1) {
        theCurrentVoieUrl = response.data + '?voie=' + theCurrentVoie

        if (response.data.lastIndexOf("?") == -1) {
          window.location.href = '/' + response.data + '?voie=' + theCurrentVoie
        } else {
          window.location.href = '/' + response.data + '&voie=' + theCurrentVoie
        }
      }
    })
    .catch(function (error) {
      console.log(error)
    })
}

function RefreshScene (voie, intervalle) {
  axios({
    method: 'post',
    url: 'ajax_refresh_scene.php',
    params: {
      voie: voie
    },
    responseType: 'json'
  })
    .then(function (response) {
      if (response.data.Url.length <= 0)
        return
      if (response.data.Url.lastIndexOf("?") == -1) {
        newUrl = '/' + response.data.Url + '?voie=' + response.data.Voie + '&intervalle=' + response.data.intervalle
      } else {
        newUrl = '/' + response.data.Url + '&voie=' + response.data.Voie + '&intervalle=' + response.data.intervalle
      }
      setTimeout(function () { window.location.href = newUrl }, intervalle)
    })
    .catch(function (error) {
      console.log(error)
    })
}

function ChangeVoie (voie, url, showUrl = 0) {
  url2 = url.replace("?", "|QU|")
  for (; ;) {
    var url3 = url2.replace("&", "|AM|")
    if (url3 == url2) break
    url2 = url3
  }

  if (showUrl > 0) {
    const baseurl = window.location.origin + '/'
    document.querySelector('#showUrl' + showUrl).value = baseurl + url + "&voie=" + voie
  } else {
    axios({
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
