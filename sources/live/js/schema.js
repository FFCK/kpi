function Init(voie)
{
	SetVoie(voie);
    
    // Refresh de la page toute les 60 secondes ...
	window.setInterval("reFresh()",60000);
}	


function reFresh() {
    location.reload(true);
}

