// j = jQuery.noConflict();

jq(function(){

    jq( "#rechercheEquipe" ).autocomplete({
        source: 'searchEquipes.php',
        minLength: 2,
        select: function( event, ui ) {
            jq( "#equipeId" ).val(ui.item.idEquipe);
            jq( "#nomEquipe" ).text(ui.item.value);
            jq( "#nomClub, #equipeTeam, #equipePalmares" ).text('');
            jq( "#equipeColors" ).html('<i>Chargement...</i>');
            jq('.fb-like').attr('data-href', 'https://www.kayak-polo.info/kpequipes.php?Equipe='+ui.item.idEquipe);
            jq(location).attr('href','kpequipes.php?Equipe='+ui.item.idEquipe);
        }
    });

});