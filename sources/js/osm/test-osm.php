<!DOCTYPE html>
<!--
    Created on : 7 mai 2018
    Author     : laurent
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Test OSM</title>
        <!-- style -->
        <link rel="stylesheet" type="text/css" href="../leaflet/leaflet.css" />
        <link rel="stylesheet" type="text/css" href="test-osm.css" />
    </head>
    <body>
        <div id="map"></div>
        <div id="liste">Liste :</div>
        <!-- Javascript -->
        <script type="text/javascript" src="../leaflet/leaflet.js"></script>
        <script type="text/javascript" src="leafletembed.js"></script>
        <script>
            var timezone_offset_minutes = new Date().getTimezoneOffset();
            timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

            // Timezone difference in minutes such as 330 or -360 or 0
            console.log(timezone_offset_minutes);
        </script>
            
    </body>
</html>
