// j = jQuery.noConflict();

jq(function(){

    vanillaAutocomplete('#rechercheEquipe', 'searchEquipes.php', {
        minChars: 2,
        dataType: 'json',
        formatItem: (item) => item.label || item.value,
        formatResult: (item) => item.value,
        onSelect: function(item) {
            if (item) {
                jq( "#equipeId" ).val(item.idEquipe);
                jq( "#nomEquipe" ).text(item.value);
                jq( "#nomClub, #equipeTeam, #equipePalmares" ).text('');
                jq( "#equipeColors" ).html('<i>Chargement...</i>');
                jq('.fb-like').attr('data-href', 'https://www.kayak-polo.info/kpequipes.php?Equipe='+item.idEquipe);
                jq(location).attr('href','kpequipes.php?Equipe='+item.idEquipe);
            }
        }
    });

});