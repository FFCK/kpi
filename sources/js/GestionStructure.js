jq = jQuery.noConflict();

// GOOGLE MAP API

    var map;
    var geocoder;
	var gmarkers = [];
	var coord = [];
	var coord2 = [];
	var postal = [];
	var www = [];
	var email = [];
	var i = 0;

	// A function to create the marker and set up the event window
	function createMarker2(point,name,html,web,mail,point2,post,logo) { // Markeur de club
		var icon = new GIcon(G_DEFAULT_ICON);
		icon.image = "https://www.kayak-polo.info/img/ffck_mappoint.png";

		var cor = new String(point);
		var coor = cor.substring(1,cor.indexOf(')',1));
		var cor2 = new String(point2);
		var coor2 = cor2.substring(1,cor2.indexOf(')',1));
		coord[name] = coor;
		coord2[name] = coor2;
		postal[name] = post;
		www[name] = web;
		var mailLink = '<a href="mailto:'+mail+'">'+mail+'</a>';
		email[name] = mail;
		if(logo != '0')
			var logoImage = '<img src="img/logo/club'+logo+'.jpg" width="120px">';
		else
			var logoImage = '';
		var webLink = '<a href="'+web+'" target="_blank">'+logoImage+'<br>'+web+'</a>';
		var options = {
			icon: icon,
		};
		var marker = new GMarker(point, options);
		GEvent.addListener(marker, "click", function() {
			marker.openInfoWindowHtml('<div style="width: 350px">' + html + '<br>' + post + '<br>' + mailLink + '<br>' + webLink + '</div>');
			document.forms['formStructure'].elements['club'].value = name;
			document.forms['formStructure'].elements['coord'].value = coor;
			document.forms['formStructure'].elements['coord2'].value = coor2;
			document.forms['formStructure'].elements['postal'].value = post;
			document.forms['formStructure'].elements['www'].value = web;
			document.forms['formStructure'].elements['email'].value = mail;
		});
		gmarkers[name] = marker;

		return marker;
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

	// Sélection d'un club, affichage de sa fenêtre et remplissage du formulaire
	function handleSelected($markerClick = true) {
		var club = document.forms['formStructure'].elements['club'].value;
		if (club == "") {
			infowindow.close();
		} else if (markers[club]) {
            if($markerClick) {
                google.maps.event.trigger(markers[club],"click");
            }
			document.forms['formStructure'].elements['coord'].value = coord[club];
			document.forms['formStructure'].elements['coord2'].value = coord2[club];
			document.forms['formStructure'].elements['postal'].value = postal[club];
			document.forms['formStructure'].elements['www'].value = www[club];
			document.forms['formStructure'].elements['email'].value = email[club];
		} else {
			document.forms['formStructure'].elements['coord'].value = '';
			document.forms['formStructure'].elements['coord2'].value = '';
			document.forms['formStructure'].elements['postal'].value = '';
			document.forms['formStructure'].elements['www'].value = '';
			document.forms['formStructure'].elements['email'].value = '';
			infowindow.close();
		}
	}
	
    function createMarker(point,html) { //Markeur du géocodeur
		var icon = new GIcon(G_DEFAULT_ICON);
		icon.image = "https://www.kayak-polo.info/img/ffck_mappoint2.png";

		var options = {
		icon: icon,
		//zIndexProcess: 10,
		draggable: true
		};
		var marker = new GMarker(point, options);
		GEvent.addListener(marker, "dragstart", function() {
		map.closeInfoWindow();
		});

		GEvent.addListener(marker, "dragend", function() {
		htm = html + '<br />Coordonnées = ' + marker.getPoint().lat() + ', ' + marker.getPoint().lng();
		marker.openInfoWindowHtml(htm);
		});

		GEvent.addListener(marker, "click", function() {
		htm = html + '<br />Coord. = ' + marker.getPoint().lat() + ', ' + marker.getPoint().lng();
		marker.openInfoWindowHtml(htm);
		});

		return marker;
    }

    function geocode(address) {
      geocoder.getLatLng(
        address,
        function(point) {
			if (!point) {
				alert(address + " not found");
			} else {
	            map.setCenter(point, 14);
	            var marker = createMarker(point, address);
	            map.addOverlay(marker);
				htm = address + '<br />Coord. = ' + marker.getPoint().lat() + ', ' + marker.getPoint().lng();
	            marker.openInfoWindowHtml(htm);
			}
        }
    );
      
      return false;
    }

    function load() {
			if (GBrowserIsCompatible()) {
				map = new GMap2(document.getElementById("map_canvas"));
//				map.addControl(new GLargeMapControl());
//				map.addControl(new GMapTypeControl());

				map.addControl(new GLargeMapControl3D());
				map.addControl(new GScaleControl());
				map.addMapType(G_SATELLITE_3D_MAP);
				var customUI=map.getDefaultUI();
				customUI.controls.maptypecontrol=true;
				customUI.maptypes.hybrid=true;
				customUI.maptypes.normal=true;
				customUI.maptypes.physical=true;
				customUI.maptypes.satellite=true;
				customUI.zoom.scrollwheel=true;
				customUI.controls.overviewmapcontrol=true;
				map.setUI(customUI);

				map.setCenter(new GLatLng(46.85, 1.75), 7);
				geocoder = new GClientGeocoder();
			} else {
				alert("Desole, l'API Google Maps n'est pas compatible avec votre navigateur.");
			}
    }

// AUTRES FONCTIONS
	function AddCD()
	{
			var libelleCD = document.forms['formStructure'].elements['libelleCD'].value;
			var codeCD = document.forms['formStructure'].elements['codeCD'].value;
			var comiteReg = document.forms['formStructure'].elements['comiteReg'].value;
			if (comiteReg == "")
			{
				alert("Sélectionner un Comité Régional... Ajout Impossible !");
				return false;
			} else if (libelleCD.length == 0 || codeCD.length == 0) {
				alert("Le nom ou le code du comité départemental est vide... Ajout Impossible !");
				return false;
			} else {
				document.forms['formStructure'].elements['Cmd'].value = 'AddCD';
				document.forms['formStructure'].elements['ParamCmd'].value = '';
				document.forms['formStructure'].submit();
				return true;
			}
	}

	function AddClub()
	{
			var libelleClub = document.forms['formStructure'].elements['libelleClub'].value;
			var codeClub = document.forms['formStructure'].elements['codeClub'].value;
			var comiteDep = document.forms['formStructure'].elements['comiteDep'].value;
			if (comiteDep == "" || comiteDep == "0000") {
				alert("Sélectionner un Comité Départemental... Ajout Impossible !");
				return false;
			} else if (libelleClub.length == 0 || codeClub.length == 0) {
				alert("Le nom ou le code du club est vide... Ajout Impossible !");
				return false;
			} else {
				document.forms['formStructure'].elements['Cmd'].value = 'AddClub';
				document.forms['formStructure'].elements['ParamCmd'].value = '';
				document.forms['formStructure'].submit();
				return true;
			}
	}

	function UpdatClub()
	{
			var club = document.forms['formStructure'].elements['club'].value;
			if (club == "")
			{
				alert("Sélectionner un Club... Mise à jour Impossible !");
				return false;
			} else {
				document.forms['formStructure'].elements['Cmd'].value = 'UpdateClub';
				document.forms['formStructure'].elements['ParamCmd'].value = '';
				document.forms['formStructure'].submit();
				return true;
			}
	}
		
jq(document).ready(function() {

	//Autocomplete recherche equipe
	jq("#libelleEquipe2").autocomplete('Autocompl_equipe.php', {
		width: 550,
		max: 50,
		mustMatch: false,
	});
	jq("#libelleEquipe2").result(function(event, data, formatted) {
		if (data) {
			jq("#libelleEquipe2").val(data[2]);
		}
	});
	
	

});

