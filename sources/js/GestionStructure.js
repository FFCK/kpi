jq = jQuery.noConflict();

var langue = [];

if(lang == 'en')  {
    langue['Echec_geocodage'] = 'Geocode was not successful for the following reason: ';
} else {
    langue['Echec_geocodage'] = 'Le géocodage a échoué pour les raisons suivantes';
}

// GOOGLE MAP API

//var map;
//var geocoder;
//var gmarkers = [];
//var coord = [];
//var coord2 = [];
//var postal = [];
//var www = [];
//var email = [];
//var i = 0;

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
                alert(langue['Echec_geocodage'] + status);
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

