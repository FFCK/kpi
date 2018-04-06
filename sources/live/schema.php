<?php
include_once('page.php');	

class Schema extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Schéma</title>
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
		<link href="./css/schema.css?tick=<?php echo uniqid()?>" rel="stylesheet">
		
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
		$count = $this->GetParamInt('count', 4);
		for ($i=1;$i<=$count;$i++)
		{
 ?>
			<div id="container_<?php echo $i;?>">
                <!--<img src="../img/schema/schema_2017_CEH21.png">-->
			</div>
<?php	
		}
    }

    function Script()
    {
		$voie = $this->GetParamInt('voie', 0);

        parent::Script();
        ?>
 		<script type="text/javascript" src="./js/voie.js" ></script>
 		<script type="text/javascript" src="./js/schema.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo "$voie";?>); }); </script>	
        <?php
    }
}

new Schema($_GET);
