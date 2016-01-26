<?php
include_once('page.php');	

class MultiScore extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>LIVE F.F.C.K.</title>
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
		<link href="./css/multi_score.css" rel="stylesheet">
		
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
			<div id="container_<?php echo $i?>">
				<div class="bandeau_score" id="bandeau_score_<?php echo $i?>"></div>
		
				<div class="match_horloge" id="match_horloge_<?php echo $i?>"></div>
				<div class="match_periode" id="match_periode_<?php echo $i?>"></div>
		 
				<div class="equipe1" id="equipe1_<?php echo $i?>"></div>
				<div class="equipe2" id="equipe2_<?php echo $i?>"></div>
		 
				<div class="nation1" id="nation1_<?php echo $i?>"></div>
				<div class="nation2" id="nation2_<?php echo $i?>"></div>
		  
				<div class="score1" id="score1_<?php echo $i?>"></div>
				<div class="score_separation" id="score_separation_<?php echo $i?>">-</div>
				<div class="score2" id="score2_<?php echo $i?>"></div>

				<div class="bandeau_goal" id="bandeau_goal_<?php echo $i?>">
					<div class="match_event_line1" id="match_event_line1_<?php echo $i?>"></div>
					<div class="match_event_line2" id="match_event_line2_<?php echo $i?>"></div>
				</div>

				<div class="lien_pdf" id="lien_pdf_<?php echo $i?>"></div>
			</div>
<?php	
		}
    }

    function Script()
    {
        parent::Script();
	
		$id_event = $this->GetParamInt('event', 0);
		$count = $this->GetParamInt('count', 4);

		?>
        <script type="text/javascript" src="./js/match.js" ></script>
        <script type="text/javascript" src="./js/multi_score.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo "$id_event,$count";?>); }); </script>	
        <?php
    }
}

new MultiScore($_GET);
?>