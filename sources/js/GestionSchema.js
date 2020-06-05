/* jq = jQuery.noConflict(); */

$(document).ready(function() {
    // equipe rouge au survol
    $('a.equipe').mouseenter(function(){
        var team = $(this).text();
        console.log(team);
        $('a.btn:contains('+team+')').each(function(){
            if ($(this).text() == team) {
                $(this).addClass('btn-danger');
            }
        });
    }).mouseleave(function(){
        $('a.btn-danger').removeClass('btn-danger');
    });
	

});

