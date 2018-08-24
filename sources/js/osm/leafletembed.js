/* 
 *     Created on : 7 mai 2018, 10:49:24
 *     Author     : laurent
 */


var map;
var ajaxRequest;
var plotlist;
var plotlayers=[];
var plotlayers2={};

//settings
var thisMinZoom = 4;
var thisMaxZoom = 15;
var thisStartZoom = 5;
var thisStartLat = 46.85;
var thisStartLon = 1.73;
// var thisPlotsAPIUrl='APIurl.php?format=leaflet&bbox='+minll.lng+','+minll.lat+','+maxll.lng+','+maxll.lat;
var thisPlotsAPIUrl = 'test.json';

initmap();


function initmap() {
    // set up AJAX request
    ajaxRequest=getXmlHttpObject();
    if (ajaxRequest==null) {
        alert ("This browser does not support HTTP Request");
        return;
    }
    
    // set up the map
    map = new L.Map('map');

    // create the tile layer with correct attribution
    var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
    var osm = new L.TileLayer(osmUrl, {minZoom: thisMinZoom, maxZoom: thisMaxZoom, attribution: osmAttrib});

    // start the map in South-East England
    map.setView(new L.LatLng(thisStartLat, thisStartLon),thisStartZoom);
    map.addLayer(osm);
    askForPlots();
    map.on('moveend', onMapMove);
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
        //use the info here that was returned
        if (ajaxRequest.status==200) {
            plotlist=eval("(" + ajaxRequest.responseText + ")");
            removeMarkers();
            for (i=0;i<plotlist.length;i++) {
                var plotll = new L.LatLng(plotlist[i].lat,plotlist[i].lon, true);
                var plotmark = new L.Marker(plotll);
                plotmark.data=plotlist[i];
                map.addLayer(plotmark);
                plotmark.bindPopup("<h3>"+plotlist[i].name+"</h3>"+plotlist[i].details);
                plotlayers.push(plotmark);
                plotlayers2[plotmark.data.id] = plotmark;
                document.getElementById('liste').innerHTML += '<div id="link_'+plotmark.data.id+'" >'+plotmark.data.id+'</div>';
            }
//            console.log(plotlayers);
            console.log(plotlayers2);
            active();
        }
    }
}

function removeMarkers() {
    for (i=0;i<plotlayers.length;i++) {
        map.removeLayer(plotlayers[i]);
    }
    plotlayers=[];
}

function active() {
    document.getElementById('link_2254').onclick = function(){
        plotlayers2[2254].fire('click');
    };
    document.getElementById('link_2255').onclick = function(){
        plotlayers2[2255].fire('click');
    };
}