// GOOGLE MAP API

    j = jQuery.noConflict();
                    
    var geocoder;
    var carte;
    var markers;
    // affiche infoWindow au clic sur un marker
    function bindInfoWindow(marker, map, infoWindow, html) {
        google.maps.event.addListener(marker, 'click', function() {
            map.setZoom(7);
            map.setCenter(marker.getPosition());
            infoWindow.setContent(html);
            infoWindow.open(map, marker);
            j('#clubId').val(j('#infoWindowContent').attr('data-code'));
            //j('#clubLibelle').text(j('#infoWindowContent').attr('data-html'));
            infosActivation();
        });
    }
    // géocodage
    function codeAddress() {
        var address = document.getElementById('address').value;
        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                carte.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: carte,
                    position: results[0].geometry.location,

                });
                carte.setZoom(9);
                var infoWindow = new google.maps.InfoWindow();
                infoWindow.setContent('<i>' + address + '</i>'
                        + '<br><br>' 
                        + results[0].formatted_address
                        + '<br>'
                        + results[0].geometry.location
                        );
                infoWindow.open(carte, marker);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }
    //Charge le script google maps API et lance la fonction initialiser()
    function loadScript() {
        var script = document.createElement("script");
        script.src = "https://maps.googleapis.com/maps/api/js?callback=initialiser&key=AIzaSyCeGM8c4y5LVVWhB-Rj07cSF8HWkvFiPXo";
        document.body.appendChild(script);
    }
    window.onload = loadScript;
//    window.onload = markActivation;

// AUTRES FONCTIONS
    j(function(){
        if(j('#clubId').val() != ''){
            infosActivation();
            //markActivation(); 
        }
    });
    //Activation infoWindow sur événement extérieur
    function markActivation(){
        var mark = j('#clubId').val();
        if(markers[mark]){
            google.maps.event.trigger(markers[mark], 'click');
        }else{
			infosActivation();
		}
    }
    

    function infosActivation(){
        var clubId = j('#clubId').val();
        j.get(
            'loadClub.php',
            {
                term : clubId
            },
            function(data){
                j( '#clubLibelle' ).html(data[0].label);
                j( '#postal' ).html(data[0].postal !== null ? data[0].postal : '');
                j( "#www" ).html(data[0].www !== null ? '<a class="btn btn-sm btn-default" href="'+data[0].www+'" target="_blank">'+data[0].www+'</a>' : '');
                j( "#email" ).html(data[0].email !== null ? '<a class="btn btn-sm btn-default" href="mailto:'+data[0].email+'">'+data[0].email+'</a>' : '');
                j( "#comitedep" ).text(data[0].comitedep !== null ? data[0].comitedep : '');
                j( "#comitereg" ).text(data[0].comitereg !== null ? data[0].comitereg : '');
                if( j( "#comitereg" ).text() == 'INTERNATIONAL' ){
                    j( "#comitereg" ).parent().hide();
                    j( "#comitedep" ).prev().text('Pays:');
                } else {
                    j( "#comitereg" ).parent().show();
                    j( "#comitedep" ).prev().text('CD:');
                }
                j('#listEquipes').text('');
                j.each(data[0].equipes, function(key, val) {
                    j('#listEquipes').append('<a class="btn btn-sm btn-default" href="kpequipes.php?Equipe='+val.Numero+'">'+val.Libelle+'</a>');
                });
                if(data[0].logo != ''){
                    j('#clubLogo').html('<img class="img2" src="'+data[0].logo+'" alt="'+data[0].club+'" />');
                }else{
                    j('#clubLogo').text('');
                }
                j( "#rechercheClub" ).val('');
                data[0].coord !== null ? j( "#coord" ).show() : j( "#coord" ).hide();
            },
            'json'
        );
            
    }
    
    j( "#coord" ).click(function(){
        markActivation();
    });
    
    j( "#rechercheClub" ).autocomplete({
        source: 'searchClubs.php',
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            j( "#clubId" ).val(ui.item.idClub);
            markActivation();
        }
    });






    // TODO
    function MailUpdat()
    {
            var club = document.forms['formCartographie'].elements['club'].value;
            if (club == "")
            {
                alert("Sélectionner un Club... Mise à jour Impossible !");
                return false;
            } else {
                alert("Ce bouton transmet le contenu du formulaire complété par mail à l adresse laurent@poloweb.org.");
                var postal = document.forms['formCartographie'].elements['postal'].value;
                var www = document.forms['formCartographie'].elements['www'].value;
                var email = document.forms['formCartographie'].elements['email'].value;
                var coord = document.forms['formCartographie'].elements['coord'].value;
                var texte = "Club = " + club + "<br>Adresse = " + postal + "<br>Web = " + www + "<br>Mail = " + email + "<br>GPS = " + coord;
                location.href="mailto:laurent@poloweb.org?subject=Kayak-polo.info : demande de mise à jour d'un club&body=" + texte;
            }
    }
