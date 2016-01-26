<?php
include_once('../commun/MyBdd.php');
include_once('create_cache_match.php');
include_once('page.php');

class Event extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Gestion Live</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="F.F.C.K.">
        <meta name="Description" content="KAYAK POLO - LIVE" />
        <meta name="Keywords" content="kayak polo, ffck" />
        <meta name="rating" content="general">
        <meta name="Robots" content="all">
        
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
        <!-- CSS styles -->
        <link href="./css/bootstrap.min.css" rel="stylesheet">
	
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
        </head>
    <?php
    }

	function Content()
	{
		?>
		<form method='GET' action='#' name='event_form' id='event_form' enctype='multipart/form-data'> 
		
		<label for='id_event'>Evénement N°</label>
		<input type='text' id='id_event' name='id_event' Value='54'>
		<br>
		
		<label for='date_event'>Date</label>
		<input type='text' id='date_event' name='date_event' Value='2014-09-24'>
		<br>

		<label for='hour_event'>Heure</label>
		<input type='text' id='hour_event' name='hour_event' Value=''>
		<br>
		
		<label for='hour_event'>Temps de Préparation</label>
		<input type='text' id='offset_event' name='offset_event' Value='10'>
		<br>

		<label for='pitch_event'>Terrains</label>
		<input type='text' id='pitch_event' name='pitch_event' Value=''>
		<br>

		<label for='delay_event'>Délai de Rafraichissement (en s)</label>
		<input type='text' id='delay_event' name='delay_event' Value='20'>
		<br>
		
		<button id='btn_go'>Lancer la génération</button>
		<br>

		<div id='info'></div>

		</form>
		<?php
    }
	
    function Script()
    {
        parent::Script();
        ?>
        <script type="text/javascript" src="./js/event.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(); }); </script>	
        <?php
    }
}

new Event($_GET);
?>