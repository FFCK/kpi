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
		<!--<link href="./css/multi_score.css" rel="stylesheet">-->
		
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
		$height = $this->GetParamInt('height', 400);
		switch ($count){
			case 1:
			case 2:
			case 3:
			case 4:
				$width = 6;
				break;
			case 5:
			case 6:
				$width = 4;
				break;
			case 7:
			case 8:
				$width = 3;
				break;
		}

		for ($i=1;$i<=$count;$i++)
		{
 ?>
			<div style="height:<?= $height ?>px;" id="container_<?php echo $i?>" class="panel panel-primary col-md-<?= $width ?>">
				<div class="panel-heading">
					<div class="">
						<div class="btn btn-default">Terrain <?= $i ?></div>
						<div class="pull-right btn btn-default">Cat: <span id="categ_<?= $i?>"></span></div>
					</div>
					<div class="btn btn-default pull-left">
						<div class="match_periode col-md-6" id="match_periode_<?= $i?>"></div>
						<div class="match_horloge col-md-6" id="match_horloge_<?= $i?>"></div>
					</div>
					<div class="btn btn-default">
						<div class="match_statut" id="match_statut_<?= $i?>"></div>
					</div>
					<div class="btn btn-info pull-right">
						<div class="match_phase col-md-6" id="phase_<?= $i?>"></div>
					</div>
				</div>
				<div class="bandeau_score" id="bandeau_score_<?= $i?>"></div>
				<div class="panel-body row">
					<div class="col-md-6">
						<div class="score1 badge pull-right" id="score1_<?= $i?>">0</div>
					</div>
					<!--<div class="score_separation" id="score_separation_<?= $i?>">-</div>-->
					<div class="col-md-6">
						<div class="score2 badge pull-left" id="score2_<?= $i?>">0</div>
					</div>
				</div>
				<div class="panel-body row">
					<div class="col-md-6">
						<div class="equipe1 pull-left btn btn-default" id="equipe1_<?= $i?>"></div>
					</div>
					<!--<div class="score_separation" id="score_separation_<?= $i?>">-</div>-->
					<div class="col-md-6">
						<div class="equipe2 pull-right btn btn-default" id="equipe2_<?= $i?>"></div>
					</div>
				</div>
		 
				<!--<div class="nation1 col-md-6" id="nation1_<?= $i?>"></div>
				<div class="nation2 col-md-6" id="nation2_<?= $i?>"></div>-->
		  

				<div class="bandeau_goal col-md-12" id="bandeau_goal_<?= $i?>">
					<div class="match_event_line1" id="match_event_line1_<?= $i?>"></div>
					<div class="match_event_line2" id="match_event_line2_<?= $i?>"></div>
				</div>

				<div class="lien_pdf col-md-12 text-center" id="lien_pdf_<?= $i?>"></div>
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