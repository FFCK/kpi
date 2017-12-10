<?php
include_once('page.php');	

class Presentation extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>F.F.C.K.</title>
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
		<link href="./css/liste_matchHD.css" rel="stylesheet">
		
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
		<div id="bandeau_presentation"></div>
		
		<div id="zone1"><div>ZONE1</diV></div>
  		<div id="zone2">ZONE2</div>
  		<div id="zone3">ZONE3</div>
  		<div id="zone4">ZONE4</div>
	
		<?php
    }

    function Script()
    {
        parent::Script();
		
		$idMatch = $this->GetParamInt('match',3772279);
/*
		<script type="text/javascript" src="./js/presentation.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo $idMatch;?>); }); </script>	
*/		
        ?>
        <script type="text/javascript" src="./js/match.js" ></script>
        <?php
    }
}

new Presentation($_GET);
?>