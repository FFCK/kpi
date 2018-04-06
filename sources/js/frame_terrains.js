jq = jQuery.noConflict();
jq(document).ready(function(){

    // Refresh de la page toute les 60 secondes ...
	window.setInterval("reFresh()",60000);
});


function reFresh() {
    location.reload(true);
}

