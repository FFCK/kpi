<?php 
    $version = isset($_GET['v']) ? htmlspecialchars($_GET['v'], ENT_QUOTES, 'UTF-8') : 'version';
?><!DOCTYPE html>
<html>
    <head>
        <title>KPI Scoreboard</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../js/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
        <style type="text/css">
            body {
                font-family: Lucida Grande, Lucida Sans, Arial, sans-serif;
                background-color: black;
            }
            /* h1 {
                font-weight: 800;
            } */

            div {
                /* border: 1px solid red; */
            }


            #scoreA, #scoreB, #teamA, #teamB, #timer, #shotclock, #period {
                font-weight: 800;
            }
            #scoreA, #scoreB {
                font-size: 1000%;
            }
            #timer {
                font-size: 800%;
            }
            #shotclock {
                font-size: 300%;
                background-color: #204f7c;
                border-radius: 15px;
            }
            #brand img {
                background-color: #fff;
                /* border: 1px solid #f15a2a; */

                /* border-radius: 15px; */
                /* padding: 2px; */
            }

            /* Penalty */
            #penaltyA div, #penaltyB div {
                margin: 6px;
            }
            .pen-timer {
                /* margin: 3px; */
                padding: 5px;
                font-size: 1.5em;
                font-weight: bold;
            }
            div.pen-G .pen-timer {
                background-color: green;
            }
            div.pen-Y .pen-timer {
                background-color: yellow;
                color: black;
            }
            div.pen-R .pen-timer, div.pen-E .pen-timer {
                background-color: red;
            }
            div.pen-Custom .pen-timer, div.pen .pen-achieved {
                background-color: #252525;
                color: whitesmoke;
            }
        </style>
    </head>
    <body class="text-white">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="p-2 text-center">
                        <img id='nationA' src="../img/Nations/IOC.png" alt="" class="img-fluid">
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center">
                        <span id="timer" class="h1">10:00</span>
                        <span id="timerStatus" class="float-end bg-danger p-2 rounded-circle rounded-1">&nbsp;</span>
                    </div>
                    <div class="text-center h1">
                        <span id="period">Period 1</span>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2 text-center">
                        <img id='nationB' src="../img/Nations/IOC.png" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col text-center h1">
                    <span id="teamA">TEAM A</span>
                </div>
                <div class="col-4 text-center mb-3">
                    <span id="brand">
                        <img src="../img/New_KPI_logo.png" alt="" height="50px">
                    </span>
                </div>
                <div class="col text-center h1">
                    <span id="teamB">TEAM B</span>
                </div>
            </div>
            <div class="row">
                <div class="col text-center">
                    <span id="scoreA" class="h1">0</span>
                </div>
                <div class="col-4 text-center h1 mt-3">
                    <span id="shotclock" class="px-4 ">60</span>
                </div>
                <div class="col text-center">
                    <span id="scoreB" class="h1">0</span>
                </div>
            </div>
            <div class="row gx-5">
                <div class="col text-center" id="penaltyA">
                </div>
                <div class="col text-center" id="penaltyB">
                </div>
            </div>
            <div class="fixed-bottom">
                    <img src="../img/logo/B-CM-2024.jpg" alt="" width="100%">
            </div>
        </div>

        <script src="../js/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
        <script src="v2/scoreboard.js?<?= $version ?>"></script>
    </body>
</html>