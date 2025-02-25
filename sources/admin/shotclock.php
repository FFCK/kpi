<?php 
    $version = isset($_GET['v']) ? htmlspecialchars($_GET['v'], ENT_QUOTES, 'UTF-8') : 'version';
?><!DOCTYPE html>
<html>
    <head>
        <title>KPI Shotclock</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- <link href="../js/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
        <style type="text/css">
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-weight: bold;
                background-color: black;
                color: white;
            }
            .container {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 98vw;
                height: 70vh;
                font-size: 78vh;
                /* border: 1px solid red; */
            }
            #container2 {
                width: 98vw;
                height: 28vh;
                font-size: 30vh;
                /* border: 1px solid red; */
            }

            #shotclock {
                font-variant-numeric: tabular-nums;
                letter-spacing: -0.05em;
                margin-right: 4vw;

            }
            #timer {
                font-size: inherit;
            }
            .stop {
                color: black;
                background-color: white;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <div id="shotclock"></div>
        </div>
        <div id="container2" class="container">
            <div id="timer"></div>
        </div>

        <!-- <script src="../js/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
        <script src="v2/shotclock.js?<?= $version ?>"></script>
    </body>
</html>