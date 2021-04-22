<?php
include_once('base.php');
include_once('create_cache_match.php');
include_once('page.php');

class Terrain extends MyPage
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
	
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
        </head>
    <?php
    }

    function Liste(&$db, $pitch)
  	{
		/* TODO: Event et Date_match dynamique */
        $idEvent = 93;
		
		echo "<h1>Terrain n°$pitch</h1>\n";
	
		$cmd  = "SELECT a.Id, a.Terrain, a.Heure_match, a.Heure_fin, a.Statut, b.Code_competition, 
			a.Id_EquipeA, a.Id_EquipeB, c.Libelle LibelleA, d.Libelle LibelleB 
			FROM kp_match a, kp_journee b, kp_competition_equipe c, kp_competition_equipe d 
			WHERE a.Id_journee = b.Id 
			AND a.Id_EquipeA = c.Id 
			AND a.Id_EquipeB = d.Id 
			AND a.Date_match = '2018-04-04' 
			AND a.Terrain = $pitch 
			AND b.Code_competition IN ('CMH', 'CMF', 'CMH21', 'CMF21', 'MCP', 'MCP2') 
			ORDER BY a.Heure_match ";

		$tMatch = null;
		$db->LoadTable($cmd, $tMatch);
		
		echo "<table class='table'>";
		for ($i=0;$i<count($tMatch);$i++)
		{
			echo "<tr>\n";
			$idMatch = $tMatch[$i]['Id'];
			echo "<td>".$idMatch."</td>";
			echo "<td>".$tMatch[$i]['Terrain']."</td>";
			echo "<td>".$tMatch[$i]['Heure_match']."</td>";
			echo "<td>".$tMatch[$i]['Heure_fin']."</td>";
			echo "<td>".$tMatch[$i]['Statut']."</td>";
			echo "<td>".$tMatch[$i]['Code_competition']."</td>";
			echo "<td>".$tMatch[$i]['LibelleA']."</td>";
			echo "<td>".$tMatch[$i]['LibelleB']."</td>";
			echo "<td><button class='go' data-event='$idEvent' data-pitch='$pitch' data-match='$idMatch'>GO</button></td>";
			echo "</tr>\n";
		}
		
		echo "</table>\n";
	}
	
    function Content()
  	{
		$db = new MyBdd();
		
		$this->Liste($db, 1);
		$this->Liste($db, 2);
		$this->Liste($db, 3);
		$this->Liste($db, 4);
		
//		$cache = new CacheMatch($_GET);
//		$cache->Event($db, 54, '2014-09-24', '10:10');

/*		
 ?>
 		<form method='GET' action='#' name='terrain_form' id='terrain_form' enctype='multipart/form-data'> 
		
		<label for='terrain1'>Terrain 1 </label>
		<input type='text' id='terrain1' name='terrain1' Value=''>
		<br>
		
		<label for='terrain1'>Terrain 2 </label>
		<input type='text' id='terrain2' name='terrain2' Value=''>
		<br>

		<label for='terrain1'>Terrain 3 </label>
		<input type='text' id='terrain3' name='terrain3' Value=''>
		<br>

		<label for='terrain1'>Terrain 4 </label>
		<input type='text' id='terrain4' name='terrain3' Value=''>
		<br>
		
		</form>
	
		<?php
*/
    }

    function Script()
    {
        parent::Script();
        ?>
        <script type="text/javascript" src="./js/terrain.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(); }); </script>	
        <?php
    }
}

new Terrain($_GET);
