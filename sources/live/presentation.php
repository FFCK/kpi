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
	<?php
		if ($this->GetParam('speaker') == '1')
		{
	?>
			<link href="./css/presentation_speaker.css" rel="stylesheet">
	<?php
		}
		else
		{
	?>
			<link href="./css/presentation.css" rel="stylesheet">
	<?php
		}
	?>
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
		
		<div id="presentation_line1"></div>
		<div id="presentation_line2"></div>
 	
		<?php
    }

    function Script()
    {
        parent::Script();
		
		$terrain = $this->GetParamInt('terrain',1);
		$speaker = $this->GetParamInt('speaker',0);
		
        ?>
        <script type="text/javascript" src="./js/match.js" ></script>
		<script type="text/javascript" src="./js/presentation.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo $terrain;?>,<?php echo $speaker;?>); }); </script>	
        <?php
    }
}

new Presentation($_GET);
?>