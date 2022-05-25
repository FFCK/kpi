/* 
 * Feuille de marque en ligne
 * Javascript partie A
 */

function Raz () {
  //$('#heure').val('00:00');
  $('#heure').val(minut_max + ':' + second_max)
}
function Horloge () {
  var temp_time = new Date()
  // chrono
  // run_time.setTime(temp_time.getTime() - start_time.getTime());
  // compte à rebours
  var max_time1 = (minut_max * 60000) + (second_max * 1000)
  run_time.setTime(start_time.getTime() + max_time1 - temp_time.getTime())

  $('#run_time_display').text(run_time.toLocaleString()) //debug
  var minut_ = run_time.getMinutes()
  if (minut_ < 10) { minut_ = '0' + minut_ }
  var second_ = run_time.getSeconds()
  if (second_ < 10) { second_ = '0' + second_ }
  $('#heure').val(minut_ + ':' + second_)
  /* Contrôle maxi */
  //if(minut_ >= minut_max && second_ >= second_max)
  if (minut_ <= 0 && second_ <= 0) {
    // Temps écoulé
    clearInterval(timer)
    //$('#periode_end').text(minut_max + ':' + second_max);
    $('#periode_end').text('00:00')
    $('#stop_button').click()
    $("#dialog_end").dialog("open")
  }
  if (socket.readyState === 1) {
    socket.send(JSON.stringify({
      'game': idMatch,
      'timer': $('#heure').val(),
      'status': timer_status
    }))
  } else {
    console.log('webSocket HS')
  }
}
function clearTimer () {
  if (timer) {
    clearInterval(timer)
  }
  if (socket.readyState === 1) {
    socket.send(JSON.stringify({
      'game': idMatch,
      'timer': $('#heure').val(),
      'status': timer_status
    }))
  } else {
    console.log('webSocket HS')
  }
}

//Messages 
function avertissement (texte) {
  $('#avert').append('<div class="avertText">' + texte + '</div>')
  $('.avertText:last').show('blind', {}, 500).text(texte).delay(2000).fadeOut(800)
}
//Alert
function custom_alert (output_msg, title_msg) {
  if (output_msg == '')
    output_msg = lang.Aucun_message
  if (title_msg == '')
    title_msg = lang.Attention
  $('div.simple_alert').remove()
  $("<div></div>").html(output_msg).dialog({
    dialogClass: 'simple_alert',
    title: title_msg,
    resizable: false,
    modal: true,
    buttons: {
      "Ok": function () {
        $(this).dialog("close")
      }
    }
  })
}

function statutActive (leStatut, leClick) {
  if (leStatut == 'ATT') {
    $('#zoneTemps, .periode, #zoneChrono').hide()
    $('.endmatch').hide()
  } else if (leStatut == 'ON') {
    $('.joueurs, #zoneTemps, #M1, #M2, #zoneChrono').show()
    $('.endmatch').hide()
    if (typeMatch == 'E') {
      $('#P1, #P2, #TB').show()
    }
  } else if (leStatut == 'END') {
    if (leClick == 'O') {
      avertissement(lang.Fin_match)
      avertissement(lang.Saisissez_heure_fin)
      var end_time = new Date()
      var end_hours = end_time.getHours()
      if (end_hours < 10) {
        end_hours = '0' + end_hours
      }
      var end_minuts = end_time.getMinutes()
      if (end_minuts < 10) {
        end_minuts = '0' + end_minuts
      }
      if ($('#end_match_time').val() == '00:00' || $('#end_match_time').val() == '00h00') {
        $('#time_end_match').val(end_hours + 'h' + end_minuts)
      } else {
        $('#time_end_match').val($('#end_match_time').val())
      }
      $('#commentaires').val($('#comments').text().replace(lang.Cliquez_pour_modifier + '...', ''))
      $('#dialog_end_match').dialog('open')
      $('#reset_evt').click()
    } else {
      $('#zoneTemps, .periode, #zoneChrono').hide()
      $('#end_match_time').removeClass('inactif').addClass('actif')
    }
  }
}

$(function () {


  $('#updateChrono img').hide()
  $.editable.addInputType('autocomplete', { //Plugin Autocomplete pour jEditable
    element: $.editable.types.text.element,
    plugin: function (settings, original) {
      $('input', this).autocomplete(settings.autocomplete)
    }
  })
  $.editable.addInputType('catcomplete', { //Plugin Autocomplete avec categories pour jEditable
    element: $.editable.types.text.element,
    plugin: function (settings, original) {
      $('input', this).catcomplete(settings.autocomplete)
    }
  })
  $.editable.addInputType('spinner', { //Plugin spinner pour jEditable
    element: $.editable.types.text.element,
    plugin: function (settings, original) {
      $('input', this).spinner(settings.spinner)
    }
  })
  $.widget("custom.catcomplete", $.ui.autocomplete, { // Widget autocomplete avec gestion des categories
    _create: function () {
      this._super()
      this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)")
    },
    _renderMenu: function (ul, items) {
      var that = this,
        currentCategory = ""
      $.each(items, function (index, item) {
        var li
        if (item.category != currentCategory) {
          ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>")
          currentCategory = item.category
        }
        li = that._renderItemData(ul, item)
        if (item.category) {
          li.attr("aria-label", item.category + " : " + item.label)
        }
      })
    }
  })


  $(document).tooltip()
  $("#chrono_ajust").mask("99:99")
  $("#periode_ajust").mask("99:99")
  $("#time_evt").mask("99:99")
  $("#end_match_time, #time_end_match").mask("99h99")
  /* COMPO EQUIPE */
  $('#equipeA, #equipeB').dataTable({
    "paging": false,
    "ordering": false,
    "info": false,
    "searching": false,
    bJQueryUI: true,
  })

  //    $('#accordion').accordion({
  //        header: "h3",
  //        heightStyle: "content"
  //    });
  $('#typeMatch').buttonset()
  $('#controleMatch').buttonset()
  $('#publiMatch').buttonset()
  $('#list_up').hide()
  $('#liste_evt').click(function (e) {
    e.preventDefault()
    $('#list, #list_header, #list_up, #list_down').toggle()
  })

  $("#idFeuille").focus()

})
