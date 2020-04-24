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
    var thisPlotsAPIUrl = 'clubs.json';
//    var geocodApiUrl = 'https://api-adresse.data.gouv.fr/search/';  // France
    var geocodApiUrl = ' https://nominatim.openstreetmap.org/search';  // International

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
                    var club = plotlist[i];
                    clubList[club.Code] = club;
                    // si le club est localisé
                    if(club.lat != '') {
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
                        clubList[code].marker=plotmark;
                        plotmark.on('click', function(){
                            afficheClub(this.data.Code);
                            map.setView(this.getLatLng(), 8);
                            jq( "#localise" ).show();
                        });
                        plotlayers.push(plotmark);
                    }
                }
                // si un club est pré-chargé et qu'il a des coordonnées, action !
                if(jq('#clubId').val() != '') {
                    jq( "#localise" ).show().click();
                } else {
                    jq( "#localise" ).hide();
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
        var club = clubList[clubId];
        jq('#clubId').val(clubId);
        jq('#clubLibelle').html(club.Libelle);
        jq('#clubLogo').html('<img class="img2" src="img/KIP/logo/'+clubId+'-logo.png?v='+version+'" height="120" alt="">');
        jq('#comitedep').html(club.comitedep);
        jq('#comitereg').html(club.comitereg);
        jq('#www').html('<a href="'+club.www+'" target="_blank">'+club.www+'</a>');
        jq('#email').html(club.email);
        jq('#postal').html(club.Postal);
        jq('#listEquipes').empty();
        club.Equipes.forEach(function(e) {
            var equipe = e.split('|');
            jq('#listEquipes').append('<a class="btn btn-sm btn-default" href="kpequipes.php?Equipe='+equipe[0]+'">'+equipe[1]+'</a>');
        });
    }

    jq( "#localise" ).click(function(){
        var club = jq('#clubId').val();
        afficheClub(club);
        jq( "#localise" ).show();
        var marker = clubList[club].marker;
        marker.openPopup();
        map.setView(marker.getLatLng(), 8);

    });

    jq( "#rechercheClub" ).autocomplete({
        source: 'searchClubs.php',
        minLength: 2,
        select: function( event, ui ) {
            event.preventDefault();
            jq( "#clubId" ).val(ui.item.idClub);
            jq( "#localise" ).show().click();
        }
    });
    
    jq( "#address" ).autocomplete({
        source: function(request, response) {
            jq.get(geocodApiUrl, { 
                    q: request.term,
//                    limit: limit
                    format: 'json',
                    polygon_geojson: 1
                }, function(data) {
                    var tab = [];
                    data.forEach(function(e) {
                        e.label = e.display_name;
                        tab.push(e);
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
            var plotmark = new L.Marker(plotll, {icon: redIcon, draggable: true});
            plotmark.on('dragend', function(event){
                var position = this.getLatLng();
                this.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
                map.panTo(new L.LatLng(position.lat, position.lng))
                var titre = '<div class="text-center"><i>'+ui.item.label+'</i><br><span class="coords">'+position.lat+','+position.lng+'</span></div>';
                this.bindPopup(titre).openPopup();
            });
            var layer = map.addLayer(plotmark);
            var titre = '<div class="text-center"><b>'+ui.item.display_name+'</b><br><span class="coords">'+ui.item.lat+','+ui.item.lon+'</span></div>';
            plotmark.bindPopup(titre).openPopup();
            map.setView([lat, lon], 8);
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
                    alert("Ce bouton transmet le contenu du formulaire complété par mail à l adresse contact@kayak-polo.info.");
                    var postal = document.forms['formCartographie'].elements['postal'].value;
                    var www = document.forms['formCartographie'].elements['www'].value;
                    var email = document.forms['formCartographie'].elements['email'].value;
                    var coord = document.forms['formCartographie'].elements['coord'].value;
                    var texte = "Club = " + club + "<br>Adresse = " + postal + "<br>Web = " + www + "<br>Mail = " + email + "<br>GPS = " + coord;
                    location.href="mailto:contact@kayak-polo.info?subject=Kayak-polo.info : demande de mise à jour d'un club&body=" + texte;
                }
        }



});