//if ( typeof(jq) == "undefined" ) {
//    jq = jQuery.noConflict();
//}
jq(document).ready(function() {

    var map;
    var ajaxRequest;
    var plotlist;
    var plotlayers=[];
    var clubList={};
    var limit = 10;

    //settings
    var thisMinZoom = 2;
    var thisMaxZoom = 18;
    var thisStartZoom = 5;
    var thisStartLat = 46.85;
    var thisStartLon = 1.73;
    // var thisPlotsAPIUrl='APIurl.php?format=leaflet&bbox='+minll.lng+','+minll.lat+','+maxll.lng+','+maxll.lat;
    var thisPlotsAPIUrl = 'json-clubs.php';
    var geocodApiUrl = 'https://api-adresse.data.gouv.fr/search/';

    var blueIcon = L.icon({
        iconUrl: 'img/Map-Marker-Ball-Right-Azure-icon.png',
    //    shadowUrl: 'leaf-shadow.png',

        iconSize:     [14, 25], // size of the icon
    //    shadowSize:   [50, 64], // size of the shadow
        iconAnchor:   [2, 25], // point of the icon which will correspond to marker's location
    //    shadowAnchor: [4, 62],  // the same for the shadow
        popupAnchor:  [6, -23] // point from which the popup should open relative to the iconAnchor
    });

    var redIcon = L.icon({
        iconUrl: 'img/Map-Marker-Ball-Left-Bronze-icon.png',
    //    shadowUrl: 'leaf-shadow.png',

        iconSize:     [14, 25], // size of the icon
    //    shadowSize:   [50, 64], // size of the shadow
        iconAnchor:   [12, 25], // point of the icon which will correspond to marker's location
    //    shadowAnchor: [4, 62],  // the same for the shadow
        popupAnchor:  [-6, -23] // point from which the popup should open relative to the iconAnchor
    });


    initmap();


    function initmap() {
        // set up AJAX request
        ajaxRequest=getXmlHttpObject();
        if (ajaxRequest==null) {
            alert ("This browser does not support HTTP Request");
            return;
        }

        // set up the map
        map = new L.Map('carte');

        // create the tile layer with correct attribution
        var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
        var osm = new L.TileLayer(osmUrl, {minZoom: thisMinZoom, maxZoom: thisMaxZoom, attribution: osmAttrib});

        // start the map in South-East England
        map.setView(new L.LatLng(thisStartLat, thisStartLon),thisStartZoom);
        map.addLayer(osm);
        askForPlots();
    //    map.on('moveend', onMapMove); 
    }

    function getXmlHttpObject() {
        if (window.XMLHttpRequest) { return new XMLHttpRequest(); }
        if (window.ActiveXObject)  { return new ActiveXObject("Microsoft.XMLHTTP"); }
        return null;
    }

    function onMapMove(e) {
        askForPlots();
    }

    function askForPlots() {
        // request the marker info with AJAX for the current bounds
        var bounds=map.getBounds();
        var minll=bounds.getSouthWest();
        var maxll=bounds.getNorthEast();
        var msg=thisPlotsAPIUrl+'?format=leaflet&bbox='+minll.lng+','+minll.lat+','+maxll.lng+','+maxll.lat;
        ajaxRequest.onreadystatechange = stateChanged;
        ajaxRequest.open('GET', msg, true);
        ajaxRequest.send(null);
    }

    function stateChanged() {
        // if AJAX returned a list of markers, add them to the map
        if (ajaxRequest.readyState==4) {
            // use the info here that was returned
            if (ajaxRequest.status==200) {
                plotlist=eval("(" + ajaxRequest.responseText + ")");
                removeMarkers();
                for (i=0; i<plotlist.length; i++) {
                    var plotll = new L.LatLng(plotlist[i].lat,plotlist[i].lon, true);
                    var plotmark = new L.Marker(
                        plotll, 
                        {icon: blueIcon}
                    );
                    plotmark.data=plotlist[i];
                    var layer = map.addLayer(plotmark);
                    var code = plotmark.data.Code;
                    var titre = '<b class="text-center">'+plotmark.data.Libelle+'</b>';
                    plotmark.bindPopup(titre);
                    plotmark.bindTooltip(plotmark.data.Libelle);
                    clubList[code]=plotmark;
                    plotmark.on('click', function(){
                        afficheClub(this.data.Code);
                        map.setView(this.getLatLng(), 10);
                    });
                    plotlayers.push(plotmark);
                }
                // si un club est pré-chargé, action !
                if(jq('#clubId').val() != '') {
                    jq( "#coord" ).click();
                }
            }
        }
    }

    function removeMarkers() {
        for (i=0;i<plotlayers.length;i++) {
            map.removeLayer(plotlayers[i]);
        }
        plotlayers=[];
    }

    function afficheClub(clubId) {
        club = clubList[clubId].data;
        jq('#clubId').val(clubId);
        jq('#clubLibelle').html(club.Libelle);
        jq('#clubLogo').html('<img class="img2" src="img/KIP/logo/'+clubId+'-logo.png" height="120" alt="">');
        jq('#comitedep').html(club.comitedep);
        jq('#comitereg').html(club.comitereg);
        jq('#www').html('<a href="'+club.www+'" target="_blank">'+club.www+'</a>');
        jq('#email').html(club.email);
        jq('#postal').html(club.Postal);
        club.Equipes.forEach(function(e) {
            var equipe = e.split('|');
            jq('#listEquipes').append('<a class="btn btn-sm btn-default" href="kpequipes.php?Equipe='+equipe[0]+'">'+equipe[1]+'</a>');
        });
    }

    jq( "#coord" ).click(function(){
        clubList[jq('#clubId').val()].fire('click');
    });

    jq( "#rechercheClub" ).autocomplete({
        source: 'searchClubs.php',
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            jq( "#clubId" ).val(ui.item.idClub);
            jq( "#coord" ).click();
        }
    });
    
    jq( "#address" ).autocomplete({
        source: function(request, response) {
            jq.get(geocodApiUrl, { 
                    q: request.term,
                    limit: limit
                }, function(data) {
                    var tab = [];
                    data.features.forEach(function(e) {
                        e.properties.lon = e.geometry.coordinates[0];
                        e.properties.lat = e.geometry.coordinates[1];
                        tab.push(e.properties);
                    });
                    response(tab);
            });
        },
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            console.log(ui.item);
            var lat = ui.item.lat;
            var lon = ui.item.lon;
            var plotll = new L.LatLng(lat,lon, true);
            var plotmark = new L.Marker(plotll, {icon: redIcon});
            var layer = map.addLayer(plotmark);
            var titre = '<div class="text-center"><b>'+ui.item.label+'</b><br>'+ui.item.lat+','+ui.item.lon+'</div>';
            plotmark.bindPopup(titre).openPopup();
            map.setView([lat, lon], 9);
        }
    });
    
//    var searchstring = function() {
//        var str = jq('#address').val();
//        var res = str.replace(/ /g, "+"); // remplace les espaces par des +
//        
//        // lance la requête en ajax
//        var adressedatagouv = jq.ajax({
//            url: "https://api-adresse.data.gouv.fr/search/?q=" + res + "&limit=" + limit,
//            dataType: "json",
//            responseType: "json",
//            error: function(xhr) {
//                alert(xhr.statusText);
//            }
//        });
//        
//        jq.when(adressedatagouv).done(function() {
//            
//        });
//    
//    }

    //    var geocoder;
    //    var carte;
    //    var markers;
    //    // affiche infoWindow au clic sur un marker
    //    function bindInfoWindow(marker, map, infoWindow, html) {
    //        google.maps.event.addListener(marker, 'click', function() {
    //            map.setZoom(7);
    //            map.setCenter(marker.getPosition());
    //            infoWindow.setContent(html);
    //            infoWindow.open(map, marker);
    //            j('#clubId').val(j('#infoWindowContent').attr('data-code'));
    //            //j('#clubLibelle').text(j('#infoWindowContent').attr('data-html'));
    //            infosActivation();
    //        });
    //    }
    //    // géocodage
    //    function codeAddress() {
    //        var address = document.getElementById('address').value;
    //        geocoder.geocode( { 'address': address}, function(results, status) {
    //            if (status == google.maps.GeocoderStatus.OK) {
    //                carte.setCenter(results[0].geometry.location);
    //                var marker = new google.maps.Marker({
    //                    map: carte,
    //                    position: results[0].geometry.location,
    //
    //                });
    //                carte.setZoom(9);
    //                var infoWindow = new google.maps.InfoWindow();
    //                infoWindow.setContent('<i>' + address + '</i>'
    //                        + '<br><br>' 
    //                        + results[0].formatted_address
    //                        + '<br>'
    //                        + results[0].geometry.location
    //                        );
    //                infoWindow.open(carte, marker);
    //            } else {
    //                alert('Geocode was not successful for the following reason: ' + status);
    //            }
    //        });
    //    }
    //    //Charge le script google maps API et lance la fonction initialiser()
    //    function loadScript() {
    //        var script = document.createElement("script");
    //        script.src = "https://maps.googleapis.com/maps/api/js?callback=initialiser&key=AIzaSyCeGM8c4y5LVVWhB-Rj07cSF8HWkvFiPXo";
    //        document.body.appendChild(script);
    //    }
    //    window.onload = loadScript;
    ////    window.onload = markActivation;
    //
    //// AUTRES FONCTIONS
    //    j(function(){
    //        if(j('#clubId').val() != ''){
    //            infosActivation();
    //            //markActivation(); 
    //        }
    //    });
    //    //Activation infoWindow sur événement extérieur
    //    function markActivation(){
    //        var mark = j('#clubId').val();
    //        if(markers[mark]){
    //            google.maps.event.trigger(markers[mark], 'click');
    //        }else{
    //			infosActivation();
    //		}
    //    }
    //    
    //
    //    function infosActivation(){
    //        var clubId = j('#clubId').val();
    //        j.get(
    //            'loadClub.php',
    //            {
    //                term : clubId
    //            },
    //            function(data){
    //                j( '#clubLibelle' ).html(data[0].label);
    //                j( '#postal' ).html(data[0].postal !== null ? data[0].postal : '');
    //                j( "#www" ).html(data[0].www !== null ? '<a class="btn btn-sm btn-default" href="'+data[0].www+'" target="_blank">'+data[0].www+'</a>' : '');
    //                j( "#email" ).html(data[0].email !== null ? '<a class="btn btn-sm btn-default" href="mailto:'+data[0].email+'">'+data[0].email+'</a>' : '');
    //                j( "#comitedep" ).text(data[0].comitedep !== null ? data[0].comitedep : '');
    //                j( "#comitereg" ).text(data[0].comitereg !== null ? data[0].comitereg : '');
    //                if( j( "#comitereg" ).text() == 'INTERNATIONAL' ){
    //                    j( "#comitereg" ).parent().hide();
    //                    j( "#comitedep" ).prev().text('Pays:');
    //                } else {
    //                    j( "#comitereg" ).parent().show();
    //                    j( "#comitedep" ).prev().text('CD:');
    //                }
    //                j('#listEquipes').text('');
    //                j.each(data[0].equipes, function(key, val) {
    //                    j('#listEquipes').append('<a class="btn btn-sm btn-default" href="kpequipes.php?Equipe='+val.Numero+'">'+val.Libelle+'</a>');
    //                });
    //                if(data[0].logo != ''){
    //                    j('#clubLogo').html('<img class="img2" src="'+data[0].logo+'" alt="'+data[0].club+'" />');
    //                }else{
    //                    j('#clubLogo').text('');
    //                }
    //                j( "#rechercheClub" ).val('');
    //                data[0].coord !== null ? j( "#coord" ).show() : j( "#coord" ).hide();
    //            },
    //            'json'
    //        );
    //            
    //    }
    //    
    //    j( "#coord" ).click(function(){
    //        markActivation();
    //    });
    //    
    //    j( "#rechercheClub" ).autocomplete({
    //        source: 'searchClubs.php',
    //        minLength: 2,
    //        select: function( event, ui ) {
    //            event.preventDefault();
    //            j( "#clubId" ).val(ui.item.idClub);
    //            markActivation();
    //        }
    //    });
    //
    //




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



});