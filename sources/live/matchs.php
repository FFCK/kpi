<?php
include_once('base.php');
include_once('page.php');

class Matchs extends MyPage
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
		<link href="./css/matchs.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
        </head>
    <?php
    }

	function VerifNation($nation)
	{
		if (strlen($nation) > 3) $nation = substr($nation, 0,3);
		if (strlen($nation) < 3) return 'FRA';
		
		for ($i=0;$i<strlen($nation);$i++)
		{
			$c = substr($nation, $i,1);
			if ($c >= '0' && $c <= '9') return 'FRA';
		}
		return $nation;
	}

	function ImgNation($nation)
	{
		$nation = $this->VerifNation($nation);
		return "<img class='centre' src='./img/nation/".$nation.".png' height='32' width='32' />";
	}

	function LoadMatch(&$db, &$tMatch, $terrain, $heureMatch)
	{
		$cmd  = "SELECT a.Id, a.Terrain, a.Heure_match, a.Heure_fin, a.Statut, b.Code_competition, ";
		$cmd .= "a.Id_EquipeA, a.Id_EquipeB, c.Libelle LibelleA, d.Libelle LibelleB, c.Code_club NationA, d.Code_club NationB ";
		$cmd .= "FROM gickp_Matchs a, gickp_Journees b, gickp_Competitions_Equipes c, gickp_Competitions_Equipes d ";
		$cmd .= "WHERE a.Id_journee = b.Id ";
		$cmd .= "And a.Id_EquipeA = c.Id ";
		$cmd .= "And a.Id_EquipeB = d.Id ";
		//$cmd .= "AND a.Date_match = '2014-09-25' ";
		$cmd .= "And b.Code_competition In ('CECF', 'CECH') ";
		$cmd .= "And a.Heure_fin = '00:00:00' ";
		$cmd .= "And a.Statut In ('ATT', 'ON') ";
		$cmd .= "And a.Heure_match > '$heureMatch' ";
		$cmd .= "And a.Terrain = $terrain ";
		$cmd .= "Order By a.Heure_match ";
		$cmd .= "Limit 1 ";

		$db->LoadTable($cmd, $tMatch);
	
		if (count($tMatch) < 1) return;
		
		$line  = 'Picth '.$terrain.' : ';
		$line .= $this->ImgNation($tMatch[0]['NationA']);
		$line .= "&nbsp;<span>";
		$line .= $tMatch[0]['LibelleA'];
		$line .= "&nbsp;&nbsp;";
		$line .= " vs ";
		$line .= "&nbsp;&nbsp;";
		$line .=  $tMatch[0]['LibelleB'];
		$line .= "</span>&nbsp;";
		$line .= $this->ImgNation($tMatch[0]['NationB']);
		
		echo "<div id='presentation_line$terrain'>".$line."</div>\n";
	}

    function Content()
    {
		$db = new MyBdd();
	
		$cmd  = "SELECT max(a.Heure_match) Heure_match ";
		$cmd .= "FROM gickp_Matchs a, gickp_Journees b ";
		$cmd .= "WHERE a.Id_journee = b.Id ";
		//$cmd .= "AND a.Date_match = '2014-09-25' ";
		$cmd .= "And b.Code_competition In ('CECF', 'CECH') ";
		$cmd .= "And a.Heure_fin != '00:00:00' ";
		$cmd .= "And a.Statut In ('END') ";
	
		$rMax = null;
		$db->LoadRecord($cmd, $rMax);
		
		$heureMatch = '00:00';
		if (isset($rMax['Heure_match']))
			$heureMatch = $rMax['Heure_match'];
		
//		echo "heureMatch = ".$heureMatch.'<br>';

		echo '<div id="bandeau_presentation"></div>';
		
		$tMatch1 = null;
		$this->LoadMatch($db, $tMatch1, 1, $heureMatch);

		$tMatch2 = null;
		$this->LoadMatch($db, $tMatch2, 2, $heureMatch);

		$tMatch3 = null;
		$this->LoadMatch($db, $tMatch3, 3, $heureMatch);
		
		$tMatch4 = null;
		$this->LoadMatch($db, $tMatch4, 4, $heureMatch);
    }

    function Script()
    {
        parent::Script();
	
        ?>
        <script type="text/javascript" src="./js/match.js" ></script>
        <?php
    }
}
// PdfMatchMulti.php?listMatch=

new Matchs($_GET);
