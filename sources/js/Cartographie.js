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
			var logoImage = '<img src="img/logo/club'+logo+'.jpg" height="100px">';
		else
			var logoImage = '';
		var webLink = '<a href="'+web+'" target="_blank">'+logoImage+'<br>'+web+'</a>';
		var options = {
			icon: icon
		};
		var marker = new GMarker(point, options);
		GEvent.addListener(marker, "click", function() {
			marker.openInfoWindowHtml('<div style="width: 350px; height: 200px">' + html + '<br>' + post + '<br>' + webLink + '<br>' + mailLink + '</div>');
			document.forms['formCartographie'].elements['club'].value = name;
			document.forms['formCartographie'].elements['coord'].value = coor;
			//document.forms['formCartographie'].elements['coord2'].value = coor2;
			document.forms['formCartographie'].elements['postal'].value = post;
			document.forms['formCartographie'].elements['www'].value = web;
			document.forms['formCartographie'].elements['email'].value = mail;
		});
		gmarkers[name] = marker;

		return marker;
	}

	// Sélection d'un club, affichage de sa fenêtre et remplissage du formulaire
	function handleSelected() {
		club = document.forms['formCartographie'].elements['club'].value;
		if (club == "") {
			map.closeInfoWindow();
		} else if (gmarkers[club]) {
			GEvent.trigger(gmarkers[club],"click");
			document.forms['formCartographie'].elements['coord'].value = coord[club];
			//document.forms['formCartographie'].elements['coord2'].value = coord2[club];
			document.forms['formCartographie'].elements['postal'].value = postal[club];
			document.forms['formCartographie'].elements['www'].value = www[club];
			document.forms['formCartographie'].elements['email'].value = email[club];
		} else {
			document.forms['formCartographie'].elements['coord'].value = '';
			//document.forms['formCartographie'].elements['coord2'].value = '';
			document.forms['formCartographie'].elements['postal'].value = '';
			document.forms['formCartographie'].elements['www'].value = '';
			document.forms['formCartographie'].elements['email'].value = '';
			map.closeInfoWindow();
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
		htm = html + '<br />Coordonnées =<br>' + marker.getPoint().lat() + ', ' + marker.getPoint().lng();
		marker.openInfoWindowHtml(htm);
		});

		GEvent.addListener(marker, "click", function() {
		htm = html + '<br />Coord. =<br>' + marker.getPoint().lat() + ', ' + marker.getPoint().lng();
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
	            map.setCenter(point, 13);
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
//				map.addControl(new GLargeMapControl3D());
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

				map.setCenter(new GLatLng(46.85, 1.75), 7); // modifié dans le php
				geocoder = new GClientGeocoder()
			} else {
				alert("Desole, l API Google Maps n'est pas compatible avec votre navigateur.");
			}
    }

// AUTRES FONCTIONS

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
