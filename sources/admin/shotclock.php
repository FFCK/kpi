<?php 
    $version = isset($_GET['v']) ? htmlspecialchars($_GET['v'], ENT_QUOTES, 'UTF-8') : 'version';
?><!DOCTYPE html>
<html>
    <head>
        <title>KPI Shotclock</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../js/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <style type="text/css">
            body {
                font-family: Lucida Grande, Lucida Sans, Arial, sans-serif;
                background-color: black;
            }
            #shotclock {
                font-weight: 800;
                font-size: 80vh;
            }

        </style>
    </head>
    <body class="text-white">
        <div class="container-fluid2">

            <!-- <div class="row"> -->
                <div id="shotclock" class="text-center"></div>
            <!-- </div> -->

        </div>

        <script src="../js/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="v2/shotclock.js?<?= $version ?>"></script>
    </body>
</html>