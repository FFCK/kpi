<?php
include_once('page.php');	

class Splitter extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Splitter</title>
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
		<link href="./css/splitter.css?tick=<?php echo uniqid()?>" rel="stylesheet">
		
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
		$count = 0;
		for ($i=1;$i<=4;$i++)
		{
			$src = $this->GetParam("frame$i");
			if ($src == '')
				break;
			++$count;
		}

		// Gestion 1, 2 ou 4 Frames ...
		if ($count > 2) $count = 4;
		
		for ($i=1; $i<=$count; $i++)
		{
			$src = $this->GetParam("frame$i");
			$src = str_replace("|Q|", "?", $src);
			$src = str_replace("|A|", "&", $src);

            if(strpos($src, '.jpg') !== false || strpos($src, '.jpeg') !== false || strpos($src, '.png') !== false || strpos($src, '.gif') !== false) {
                ?>
                    <div id="container_<?= $i.'_'.$count; ?>" class="container_<?= $count ?>">
                        <img src="<?= $src; ?>">
                    </div>
                <?php	
            } else {
                ?>
                    <div id="container_<?= $i.'_'.$count; ?>" class="container_<?= $count ?>">
                        <iframe src="<?= $src; ?>">
                        </iframe>
                    </div>
                <?php	
            }
		}
    }

    function Script()
    {
		$voie = $this->GetParamInt('voie', 0);

        parent::Script();
        ?>
 		<script type="text/javascript" src="./js/voie.js" ></script>
 		<script type="text/javascript" src="./js/splitter.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?= "$voie"; ?>); }); </script>	
        <?php
    }
}

new Splitter($_GET);
