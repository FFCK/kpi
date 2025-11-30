// Vanilla JS - no jQuery dependency for Leaflet code
// Les traductions sont maintenant chargées depuis le fichier centralisé js_translations.php
// L'objet 'langue' est disponible globalement

var map;
var searchMarker = null;
var redIcon;

// Leaflet/OpenStreetMap initialization
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

function initMap() {
    // Initialize the map
    map = L.map('carte').setView([46.85, 1.75], 5);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);

    // Define red icon for search results
    redIcon = L.icon({
        iconUrl: '../img/Map-Marker-Ball-Left-Bronze-icon.png',
        iconSize: [14, 25],
        iconAnchor: [12, 25],
        popupAnchor: [-6, -23]
    });

    // Load markers from PHP (injected via template)
    // This code is now in the template and uses {$mapParam}
}

// Geocoding using Nominatim (OpenStreetMap)
function codeAddress() {
    var address = document.getElementById('address').value;
    if (!address) return false;

    var geocodApiUrl = 'https://nominatim.openstreetmap.org/search?q=' +
                       encodeURIComponent(address) + '&format=json&limit=1';

    fetch(geocodApiUrl)
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data && data.length > 0) {
                var result = data[0];
                var lat = parseFloat(result.lat);
                var lon = parseFloat(result.lon);

                // Remove previous search marker if exists
                if (searchMarker) {
                    map.removeLayer(searchMarker);
                }

                // Add new marker
                searchMarker = L.marker([lat, lon], {icon: redIcon}).addTo(map);
                var popupContent = '<i>' + address + '</i><br><br>' +
                                  result.display_name + '<br>' +
                                  lat + ', ' + lon;
                searchMarker.bindPopup(popupContent).openPopup();

                // Center map on result
                map.setView([lat, lon], 9);
            } else {
                alert(langue['Echec_geocodage']);
            }
        })
        .catch(function(error) {
            alert(langue['Echec_geocodage']);
        });

    return false;
}

// Sélection d'un club, affichage de sa fenêtre et remplissage du formulaire
function handleSelected(markerClick) {
    if (typeof markerClick === 'undefined') markerClick = true;

    var club = document.forms['formStructure'].elements['club'].value;
    if (club == "") {
        // Close all popups
        map.closePopup();
    } else if (markers[club]) {
        if(markerClick) {
            markers[club].openPopup();
            map.setView(markers[club].getLatLng(), 8);
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
        map.closePopup();
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

// Modern vanilla JS autocomplete
document.addEventListener('DOMContentLoaded', function() {
    // Uppercase converter for libelleClub
    var libelleClub = document.getElementById('libelleClub');
    if (libelleClub) {
        libelleClub.addEventListener('input', function() {
            var start = this.selectionStart;
            var end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });
    }

    // Modern autocomplete for libelleEquipe2
    var libelleEquipe2 = document.getElementById('libelleEquipe2');
    if (libelleEquipe2) {
        initAutocomplete(libelleEquipe2, 'Autocompl_equipe.php');
    }
});

// Vanilla JS Autocomplete function
function initAutocomplete(input, url) {
    var currentFocus = -1;
    var debounceTimer;
    var autocompleteList;

    // Create autocomplete container
    var container = document.createElement('div');
    container.className = 'autocomplete-items';
    container.style.cssText = 'position:absolute;border:1px solid #d4d4d4;border-top:none;z-index:99;top:100%;left:0;right:0;max-height:200px;overflow-y:auto;background:#fff;';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(container);

    // Input event handler with debounce
    input.addEventListener('input', function() {
        var val = this.value;
        closeAllLists();
        if (!val || val.length < 2) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetch(url + '?q=' + encodeURIComponent(val) + '&format=json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                currentFocus = -1;
                data.forEach(function(item) {
                    var div = document.createElement('div');
                    div.style.cssText = 'padding:10px;cursor:pointer;border-bottom:1px solid #d4d4d4;';
                    div.innerHTML = item.label;
                    div.addEventListener('mouseenter', function() {
                        removeActive();
                        this.classList.add('autocomplete-active');
                        this.style.backgroundColor = '#e9e9e9';
                    });
                    div.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    div.addEventListener('click', function() {
                        input.value = item.value;
                        closeAllLists();
                    });
                    container.appendChild(div);
                });
            })
            .catch(error => console.error('Autocomplete error:', error));
        }, 300);
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        var items = container.getElementsByTagName('div');
        if (e.keyCode === 40) { // Down arrow
            currentFocus++;
            addActive(items);
            e.preventDefault();
        } else if (e.keyCode === 38) { // Up arrow
            currentFocus--;
            addActive(items);
            e.preventDefault();
        } else if (e.keyCode === 13) { // Enter
            e.preventDefault();
            if (currentFocus > -1 && items[currentFocus]) {
                items[currentFocus].click();
            }
        } else if (e.keyCode === 27) { // Escape
            closeAllLists();
        }
    });

    function addActive(items) {
        if (!items || items.length === 0) return;
        removeActive();
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = items.length - 1;
        items[currentFocus].classList.add('autocomplete-active');
        items[currentFocus].style.backgroundColor = '#e9e9e9';
        items[currentFocus].scrollIntoView({ block: 'nearest' });
    }

    function removeActive() {
        var items = container.getElementsByTagName('div');
        for (var i = 0; i < items.length; i++) {
            items[i].classList.remove('autocomplete-active');
            items[i].style.backgroundColor = '';
        }
    }

    function closeAllLists() {
        container.innerHTML = '';
        currentFocus = -1;
    }

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (e.target !== input) {
            closeAllLists();
        }
    });
}

