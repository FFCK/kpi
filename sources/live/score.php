<?php
include_once('page.php');	

class Score extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>Score</title>
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
			<link href="./css/score_speaker.css" rel="stylesheet">
	<?php
		}
		else
		{
	?>
			<link href="./css/score.css" rel="stylesheet">
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
/*			<div id="match_horloge_etat"></div>*/

 ?>
		<div id="bandeau_score"></div>
		
		<div id="match_horloge"></div>
		<div id="match_periode"></div>
 
  		<div id="equipe1"></div>
  		<div id="equipe2"></div>
 
		<div id="nation1"></div>
  		<div id="nation2"></div>
  
		<div id="score1"></div>
		<div id="score_separation">-</div>
  		<div id="score2"></div>

		<div id="bandeau_goal">
			<div id="match_event_line1"></div>
			<div id="match_event_line2"></div>
		</div>

		<div id="lien_pdf"></div>
		
		<?php
    }

    function Script()
    {
        parent::Script();
		
		$terrain = $this->GetParamInt('terrain', 1);
		$speaker = $this->GetParamInt('speaker',0);
		$voie = $this->GetParamInt('voie', 0);

        ?>
        <script type="text/javascript" src="./js/match.js" ></script>
		<script type="text/javascript" src="./js/voie.js" ></script>
        <script type="text/javascript" src="./js/score.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo "$terrain,$speaker,$voie";?>); }); </script>	
        <?php
    }
}

new Score($_GET);
