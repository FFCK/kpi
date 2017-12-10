function Go(idEvent, idMatch, pitch)
{
	var param;
    param  = 'event='+idEvent;
    param += '&match='+idMatch;
    param += '&pitch='+pitch;
    
//   alert("ajax_cache_pitch.php?"+param);
    $.ajax({ type: "GET", url: "ajax_cache_pitch.php", dataType: "html", data: param, cache: false, 
                success: function(htmlData) {
						alert(htmlData);
				}
	});
}

function Init()
{
	$('.go').click(function () {
		Go($(this).attr('data-event'), $(this).attr('data-match'), $(this).attr('data-pitch'));
	});
}
