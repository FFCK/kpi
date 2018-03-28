<?php
//include_once('base.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');

include_once('page.php');
	
class TV extends MyPage
{
	function Header() {}
    function Footer() {}
	
    function Head()
    {
    ?>
        <head>
        <title>TV</title>
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
		<link href="./css/tv_black.css?v3" rel="stylesheet">

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
		
		for ($i=0;$i<strlen($nation);$i++)
		{
			$c = substr($nation,$i,1);
			if ($c >= '0' && $c <= '9') return 'FRA';
		}
		return $nation;
	}

	function ImgNation($nation)
	{
		$nation = $this->VerifNation($nation);
		return "<img class='centre' src='./img/nation/".$nation.".png' height='32' width='32' />";
	}
	
	function ImgNation48($nation)
	{
		$nation = $this->VerifNation($nation);
		return "<img class='centre' src='./img/nation/".$nation.".png' height='48' width='48' />";
	}

	
	function ImgNation64($nation)
	{
		$nation = $this->VerifNation($nation);
		return "<img class='centre' src='./img/nation/".$nation.".png' height='64' width='64' />";
	}
	
	function ImgMedal($medal)
	{
		return "<img class='centre' src='./img/".$medal.".gif' height='32' width='32' />";
	}
	
	function ImgMedal48($medal)
	{
		return "<img class='centre' src='./img/".$medal.".gif' height='48' width='48' />";
	}
	
	function ImgMedal64($medal)
	{
		return "<img class='centre' src='./img/".$medal.".gif' height='64' width='64' />";
	}

	function LabelMedal($medal)
	{
		if ($medal == 'GOLD') return 'Gold medal';
		if ($medal == 'SILVER') return 'Silver medal';
		if ($medal == 'BRONZE') return 'Bronze medal';

		return '';
	}

	function VerifReferee(&$referee)
	{
		$referee = trim($referee);
		if (substr($referee, -5, 3) == 'INT')
			$referee = substr($referee,0, strlen($referee)-5);
			
		$referee = trim($referee);
		if (substr($referee, -1) != ')')
			return '';
			
		$nation = substr($referee,-4,3);
		$referee = substr($referee,0,strlen($referee)-6);
		return $nation;
	}
	
	function GetPlayer(&$tJoueurs, $row)
	{
		if ($row >= count($tJoueurs))
			return;
			
		$prenom = $tJoueurs[$row]['Prenom'];
		
		$line = '<span class="numero_player">';
		if ($tJoueurs[$row]['Numero'] != '0')
		{
			$line .= $tJoueurs[$row]['Numero'].' - ';
		}
		$line .= '</span><span class="name_player">';
		$line .= strtoupper($tJoueurs[$row]['Nom']);
		$line .= ' ';
		$line .= strtoupper(substr($prenom,0,1)).strtolower(substr($prenom,1));
		
		if ($tJoueurs[$row]['Capitaine'] == 'C')
			$line .= ' (Captain) ';
		elseif ($tJoueurs[$row]['Capitaine'] == 'E')
			$line .= ' (Coach) ';
		$line .= '</span>';

		return $line;
	}

	function Content_List_Team()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		if ($equipe == 'A')
			$idEquipe =  $rMatch['Id_equipeA'];
		else
			$idEquipe =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe 
		$rEquipe = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipe", $rEquipe);

		// Chargement Joueurs  
		$cmd  = "Select a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = '$equipe' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "And (a.Capitaine Is Null Or a.Capitaine != 'E') ";
		$cmd .= "Order By a.Numero ";

		$tJoueurs = null;
		$db->LoadTable($cmd, $tJoueurs);
	
		$title1 = $this->ImgNation64($rEquipe['Code_club']);
		$title1 .= "&nbsp;<span>";
		$title1 .= $rEquipe['Libelle'];
		$title1 .= "</span>";
		
//		$title2 = $rCompetition['Soustitre2'];

		echo "<div id='banner_list'></div>\n";
		echo "<div id='list_team_title1'>$title1</div>\n";
//		echo "<div id='list_team_title2'>$title2</div>\n";

		// Max 10 Joueurs
		echo "<div id='list_team_player1' class='list_team_player'>".$this->GetPlayer($tJoueurs,0)."</div>\n";
		echo "<div id='list_team_player2' class='list_team_player'>".$this->GetPlayer($tJoueurs,1)."</div>\n";
		echo "<div id='list_team_player3' class='list_team_player'>".$this->GetPlayer($tJoueurs,2)."</div>\n";
		echo "<div id='list_team_player4' class='list_team_player'>".$this->GetPlayer($tJoueurs,3)."</div>\n";
		echo "<div id='list_team_player5' class='list_team_player'>".$this->GetPlayer($tJoueurs,4)."</div>\n";
		echo "<div id='list_team_player6' class='list_team_player'>".$this->GetPlayer($tJoueurs,5)."</div>\n";
		echo "<div id='list_team_player7' class='list_team_player'>".$this->GetPlayer($tJoueurs,6)."</div>\n";
		echo "<div id='list_team_player8' class='list_team_player'>".$this->GetPlayer($tJoueurs,7)."</div>\n";
		echo "<div id='list_team_player9' class='list_team_player'>".$this->GetPlayer($tJoueurs,8)."</div>\n";
		echo "<div id='list_team_player10' class='list_team_player'>".$this->GetPlayer($tJoueurs,9)."</div>\n";
		
		// Chargement Entraineur  
		$cmd  = "Select a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = '$equipe' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "And (a.Capitaine Is Not Null And a.Capitaine = 'E') ";
		$cmd .= "Order By a.Numero ";

		$tJoueurs = null;
		$db->LoadTable($cmd, $tJoueurs);

		// Max 3 Entraineurs
		echo "<div id='list_team_player11' class='list_team_player'>".$this->GetPlayer($tJoueurs,0)."</div>\n";
		echo "<div id='list_team_player12' class='list_team_player'>".$this->GetPlayer($tJoueurs,1)."</div>\n";
		echo "<div id='list_team_player13' class='list_team_player'>".$this->GetPlayer($tJoueurs,2)."</div>\n";
    }

	function Content_List_Medals()
    {
		$db = new MyBdd();
		
		$competition = $this->GetParam('competition');

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$competition."' And Code_saison = " . utyGetSaison(), $rCompetition);

		// Chargement des Equipes Classées ...
		$cmd  = "Select * FROM gickp_Competitions_Equipes ";
		$cmd .= "Where Code_compet = '".$competition."' And Code_saison = " . utyGetSaison() . " ";
		$cmd .= "Order By CltNiveau_publi ";
		
		$tEquipes = null;
		$db->LoadTable($cmd, $tEquipes);
		echo "<div id='banner_presentation'></div>\n";
		$title = $rCompetition['Soustitre2'];
		echo "<div id='list_medals_title'>$title</div>\n";

		if (count($tEquipes) < 3) return;
		
		?>
		<table id="table_medals">
			<tr>
			<td class="col_img_medal"><?php echo $this->ImgMedal48('GOLD');?></td>
			<td class="col_silver"></td>
			<td class="col_gold"><?php echo $this->VerifNation($tEquipes[0]['Libelle']).' '.$this->ImgNation48($tEquipes[0]['Code_club']);?></td>
			<td class="col_bronze"></td>
			</tr>

			<tr>
			<td class="col_img_medal"><?php echo $this->ImgMedal48('SILVER');?></td>
			<td class="col_silver"><?php echo $this->VerifNation($tEquipes[1]['Libelle']).' '.$this->ImgNation48($tEquipes[1]['Code_club']);?></td>
			<td class="col_gold"></td>
			<td class="col_bronze"></td>
			</tr>

			<tr>
			<td class="col_img_medal"><?php echo $this->ImgMedal48('BRONZE');?></td>
			<td class="col_silver"></td>
			<td class="col_gold"></td>
			<td class="col_bronze"><?php echo $this->VerifNation($tEquipes[2]['Libelle']).' '.$this->ImgNation48($tEquipes[2]['Code_club']);?></td>
			</tr>
		</table>
	<?php
	}

	function Content_Player()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$numero = $this->GetParam('number', '1');
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		if ($equipe == 'A')
			$idEquipe =  $rMatch['Id_equipeA'];
		else
			$idEquipe =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe 
		$rEquipe = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipe", $rEquipe);

		// Chargement Joueurs  
		$cmd  = "Select a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = '$equipe' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "And a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
		
		$title = $this->ImgNation64($rEquipe['Code_club']);
		$title .= "&nbsp;<span>";
		$title .= utyGetString($rJoueur, 'Numero', '999');
		$title .= ' - ';
		$title .= utyGetString($rJoueur, 'Nom', '???');
		$title .= ' ';
		$title .= utyGetString($rJoueur, 'Prenom','...');
		$title .= "</span>";

		echo "<div id='banner_single'></div>\n";
		echo "<div id='player_title' class='player_title'>$title</div>\n";
/*
		echo "<div id='player_title1' class='player_title'>Title 1</div>\n";
		echo "<div id='player_title2' class='player_title'>Title 2</div>\n";
*/
	}

	function Content_Player_Medal()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$numero = $this->GetParam('number', '1');
		$medaille = $this->GetParam('medal');
		
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		if ($equipe == 'A')
			$idEquipe =  $rMatch['Id_equipeA'];
		else
			$idEquipe =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe 
		$rEquipe = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipe", $rEquipe);

		// Chargement Joueurs  
		$cmd  = "Select a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = '$equipe' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "And a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
	
		$title  = $this->ImgNation64($rEquipe['Code_club']);
		$title .= "&nbsp;<span>";
		$title .= utyGetString($rJoueur, 'Numero', '999');
		$title .= ' - ';
		$title .= utyGetString($rJoueur, 'Nom', '???');
		$title .= ' ';
		$title .= utyGetString($rJoueur, 'Prenom', '...');
		$title .= "</span>";

		echo "<div id='banner_single'></div>\n";
		echo "<div id='player_title1' class='player_title'>$title</div>\n";
		
		$title  = $this->ImgMedal($medaille);
		$title .= "&nbsp;<span>";
		$title .= $this->LabelMedal($medaille);
		$title .= "</span>";
		echo "<div id='player_title2' class='player_title'>$title</div>\n";
	}

	function Content_Referee()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);
		
		echo "<div id='banner_referee'></div>\n";

		$arbitre  = $rMatch['Arbitre_principal'];
		$nation = $this->VerifReferee($arbitre);
		echo "<div id='referee_line1'>First Referee : ".$this->ImgNation64($nation)."&nbsp;<span>".$arbitre."<span></div>\n";

		$arbitre  = $rMatch['Arbitre_secondaire'];
		$nation = $this->VerifReferee($arbitre);
		echo "<div id='referee_line2'>Second Referee : ".$this->ImgNation64($nation)."&nbsp;<span>".$arbitre."<span></div>\n";
	}

	function Content_Match()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		$idEquipeA = $rMatch['Id_equipeA'];
		$idEquipeB = $rMatch['Id_equipeB'];

		// Chargement Equipe A
		$rEquipeA = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeA", $rEquipeA);

		// Chargement Equipe B
		$rEquipeB = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeB", $rEquipeB);

		echo "<div id='banner_presentation'></div>\n";
		
		// ligne 1
		$line  = $rCompetition['Soustitre2'];
	 	$line .= " - Pitch ";
		$line .= $rMatch['Terrain'];
		echo "<div id='presentation_line1'>$line</div>\n";
		
		// Ligne 2
		$line  = $this->ImgNation64($rEquipeA['Code_club']);
		$line .= "&nbsp;<span>";
		$line .= $rEquipeA['Libelle'];
		$line .= " vs ";
		$line .=  $rEquipeB['Libelle'];
		$line .= "</span>&nbsp;";
		$line .= $this->ImgNation64($rEquipeB['Code_club']);
		echo "<div id='presentation_line2'>$line</div>\n";
	}

	function Content_Match_Score()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		$idEquipeA = $rMatch['Id_equipeA'];
		$idEquipeB = $rMatch['Id_equipeB'];

		// Chargement Equipe A
		$rEquipeA = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeA", $rEquipeA);

		// Chargement Equipe B
		$rEquipeB = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeB", $rEquipeB);

		echo "<div id='banner_presentation'></div>\n";
		
		// ligne 1
		$line  = $rCompetition['Soustitre2'];
	 	$line .= " - Pitch ";
		$line .= $rMatch['Terrain'];
		echo "<div id='presentation_line1'>$line</div>\n";
		
		// Ligne 2
		$line  = $this->ImgNation($rEquipeA['Code_club']);
		$line .= "&nbsp;<span>";
		$line .= $rEquipeA['Libelle'];
		$line .= "&nbsp;&nbsp;";
		$line .= $rMatch['ScoreDetailA'];
		$line .= " - ";
		$line .= $rMatch['ScoreDetailB'];
		$line .= "&nbsp;&nbsp;";
		$line .=  $rEquipeB['Libelle'];
		$line .= "</span>&nbsp;";
		$line .= $this->ImgNation($rEquipeB['Code_club']);
		echo "<div id='presentation_line2'>$line</div>\n";
	}
	
	function Content_Team()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		$idEquipeA = $rMatch['Id_equipeA'];
		$idEquipeB = $rMatch['Id_equipeB'];
		
		if ($equipe == 'A')
			$idEquipe =  $rMatch['Id_equipeA'];
		else
			$idEquipe =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe 
		$rEquipe = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipe", $rEquipe);

		echo "<div id='banner_single'></div>\n";
		
		$title  = $this->ImgNation64($rEquipe['Code_club']);
		$title .= '&nbsp;<span>';
		$title .= $rEquipe['Libelle'];
		$title .= '</span>';
		echo "<div id='player_title' class='player_title'>$title</div>\n";
	}

	function Content_Team_Medal()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$medaille = $this->GetParam('medal');
			
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		$idEquipeA = $rMatch['Id_equipeA'];
		$idEquipeB = $rMatch['Id_equipeB'];
		
		if ($equipe == 'A')
			$idEquipe = $rMatch['Id_equipeA'];
		else
			$idEquipe = $rMatch['Id_equipeB'];
		
		// Chargement Equipe 
		$rEquipe = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipe", $rEquipe);

		echo "<div id='banner_single'></div>\n";
		
		$title  = $this->ImgNation64($rEquipe['Code_club']);
		$title .= '&nbsp;<span>';
		$title .= $rEquipe['Libelle'];
		$title .= '</span>';
		echo "<div id='player_title1' class='player_title'>$title</div>\n";
		
		echo "<div id='banner_single'></div>\n";
		echo "<div id='player_title1' class='player_title'>$title</div>\n";
		
		$title  = $this->ImgMedal($medaille);
		$title .= "&nbsp;<span>";
		$title .= $this->LabelMedal($medaille);
		$title .= "</span>";
		echo "<div id='player_title2' class='player_title'>$title</div>\n";
	}

	function Content_Command_Channel($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		<option value="1">Channel 1</option> 
		<option value="2">Channel 2</option> 
		<option value="3">Channel 3</option> 
		<option value="4">Channel 4</option> 
		<option value="5">Channel 5</option> 
		<option value="6">Channel 6</option> 
		<option value="7">Channel 7</option> 
		<option value="8">Channel 8</option> 
		<option value="9">Channel 9</option> 
	  </select>
<?php 
	}

	function Content_Command_Competition($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		  <option value="CEH">CEH</option> 
		  <option value="CEF">CEF</option>
		  <option value="CEH21">CEH21</option>
		  <option value="CEF21">CEF21</option>
	  </select>
<?php 
	}

	function Content_Command_Match($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		<option value="79261780">79261780 : GER Men - ESP Men</option>
		<option value="79261829">79261829 : GER Women - FRA Women</option>
 
	  </select>
<?php 
	}

	function Content_Command_Team($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		  <option value="A">Team A</option> 
		  <option value="B">Team B</option>
	  </select>
<?php 
	}

	function Content_Command_Number($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		  <option value="1">Number 1</option> 
		  <option value="2">Number 2</option> 
		  <option value="3">Number 3</option> 
		  <option value="4">Number 4</option> 
		  <option value="5">Number 5</option> 
		  <option value="6">Number 6</option> 
		  <option value="7">Number 7</option> 
		  <option value="8">Number 8</option> 
		  <option value="9">Number 9</option> 
		  <option value="10">Number 10</option> 
	  </select>
<?php 
	}
	
	function Content_Command_Medal($id)
	{
		echo "<select name='$id' id='$id'>";
?>		
		  <option value="GOLD">Gold</option> 
		  <option value="SILVER">Silver</option>
		  <option value="BRONZE">Bronze</option>
	  </select>
<?php 
	}
	
	
	function Content_Command_Url($url)
	{
		echo "<select name='$url' id='$url'>";
?>		
		  <option value="live/score.php">live/score.php</option> 
		  <option value="live/multi_score.php">live/multi_score.php</option> 
		  <option value="live/multi_score.php?tv=2">live/multi_score.php?tv=2</option> 
		  <option value="live/schema.php">live/schema.php</option> 
		  <option value="frame_terrains.php?Saison=2017&Group=CE&lang=en&Css=sainto_hd&filtreJour=2017-08-24">frame_terrains.php?Saison=2017&Group=CE&lang=en&Css=sainto_hd&filtreJour=2017-08-24</option> 
	  </select>
<?php 
//		  https://www.kayak-polo.info/frame_terrains.php?Saison=2017&Group=CE&lang=en&Css=sainto_hd&filtreJour=2017-08-24
	}
	
	function Content_Command_Scenario($scenario)
	{
		echo "<table name='$scenario' id='$scenario'>";
?>		
			<thead>
			<tr>
				<th>N°</th>
				<th>Url</th>
				<th>Durée</th>
			</tr>
			</thead>
			<tbody>
			<?php for ($i=1;$i<=8;$i++) { ?>
				<tr>
					<td><?php echo $i;?></td>
					<td><input type="text" style="width:1024px" name="scenario_url<?php echo$i;?>" id="scenario_url<?php echo$i;?>"></td>
					<td><input type="text" style="width:64px"name="scenario_duree<?php echo$i;?>" id="scenario_duree<?php echo$i;?>"></td>
				</tr>
			<?php } ?>
			</tbody>
	  </table>
<?php 
	}

	function Content_Command()
	{
?>
		<form>
		<table class='table'>

		<tr>
			<td><button id="list_medals_btn">Medals Presentation</button></td>
			<td><?php $this->Content_Command_Channel('list_medals_channel') ?></td>
			<td><label>Competition</label></td> 
			<td><?php $this->Content_Command_Competition('list_medals_competition') ?></td>
		</tr>
			
		<tr>
			<td><button id="referee_btn">Referees Presentation</button></td>
			<td><?php $this->Content_Command_Channel('referee_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('referee_match') ?></td>
		</tr>
	
		<tr>
			<td><button id="player_btn">Player Presentation</button></td>
			<td><?php $this->Content_Command_Channel('player_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('player_match') ?></td>
			<td><label>Team</label> </td>
			<td><?php $this->Content_Command_Team('player_team') ?></td>
			<td><label>Number</label></td> 
			<td><?php $this->Content_Command_Number('player_number') ?></td>
		</tr>
	
		<tr>
			<td><button id="player_medal_btn">Player width Medal Presentation</button></td>
			<td><?php $this->Content_Command_Channel('player_medal_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('player_medal_match') ?></td>
			<td><label>Team</label> </td>
			<td><?php $this->Content_Command_Team('player_medal_team') ?></td>
			<td><label>Number</label></td> 
			<td><?php $this->Content_Command_Number('player_medal_number') ?></td>
			<td><label>Medal</label></td> 
			<td><?php $this->Content_Command_Medal('player_medal_medal') ?></td>
		</tr>
		
		<tr>
			<td><button id="team_btn">Team Presentation</button></td>
			<td><?php $this->Content_Command_Channel('team_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('team_match') ?></td>
			<td><label>Team</label> </td>
			<td><?php $this->Content_Command_Team('team_team') ?></td>
		</tr>

		<tr>
			<td><button id="team_medal_btn">Team Medal Presentation</button></td>
			<td><?php $this->Content_Command_Channel('team_medal_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('team_medal_match') ?></td>
			<td><label>Team</label> </td>
			<td><?php $this->Content_Command_Team('team_medal_team') ?></td>
			<td><label>Medal</label></td> 
			<td><?php $this->Content_Command_Medal('team_medal_medal') ?></td>
		</tr>

		<tr>
			<td><button id="match_btn">Match Presentation</button></td>
			<td><?php $this->Content_Command_Channel('match_channel') ?></td>
			<td><label>Match</label></td> 
			<td><?php $this->Content_Command_Match('match_match') ?></td>
		</tr>
	
		<tr>
			<td><button id="match_score_btn">Match + Score Presentation</button></td>
			<td><?php $this->Content_Command_Channel('match_score_channel') ?></td>
			<td><label>Match</label></td>
			<td><?php $this->Content_Command_Match('match_score_match') ?></td>
		</tr>

		<tr>
			<td><button id="list_team_btn">List Team Presentation</button></td>
			<td><?php $this->Content_Command_Channel('list_team_channel') ?></td>
			<td><label>Match</label> </td>
			<td><?php $this->Content_Command_Match('list_team_match') ?></td>
			<td><label>Team</label> </td>
			<td><?php $this->Content_Command_Team('list_team_team') ?></td>
		</tr>
		
		<tr>
			<td><button id="list_presentation_btn">Autre Présentation</button></td>
			<td><?php $this->Content_Command_Channel('list_presentation_channel') ?></td>
			<td><label>Url</label> </td>
			<td><?php $this->Content_Command_Url('list_presentation_url') ?></td>
		</tr>

		<tr>
			<td><button id="scenario_btn">Scénario</button></td>
			<td><?php $this->Content_Command_Channel('scenario_channel') ?>
				<br><br>
				<button id="url_splitter">Url Splitter</button>
			</td>
			<td colspan="4"><?php $this->Content_Command_Scenario('scenario') ?></td>
		</tr>
		
		<tr>
			<td><button id="raz_btn">Reset</button></td>
		</tr>

		<tr>
			<td>Message</td>
			<td colspan="4"><div id="tv_message">Message</div></td>
		</tr>
		
		</form>
		</table>
	
<?php	
	}
	
    function Content()
    {
		$show = $this->GetParam('show', 'command');
		if ($show == 'list_team')
		{
			$this->Content_List_Team();
			return;
		}

		if ($show == 'list_medals')
		{
			$this->Content_List_Medals();
			return;
		}

		if ($show == 'player')
		{
			$this->Content_Player();
			return;
		}

		if ($show == 'player_medal')
		{
			$this->Content_Player_Medal();
			return;
		}

		if ($show == 'referee')
		{
			$this->Content_Referee();
			return;
		}

		if ($show == 'match')
		{
			$this->Content_Match();
			return;
		}

		if ($show == 'match_score')
		{
			$this->Content_Match_Score();
			return;
		}

		if ($show == 'team')
		{
			$this->Content_Team();
			return;
		}

		if ($show == 'team_medal')
		{
			$this->Content_Team_Medal();
			return;
		}
		
		if ($show == 'command')
		{
			$this->Content_Command();
			return;
		}
	}

    function Script()
    {
        parent::Script();
		$voie = $this->GetParamInt('voie',0);

		$show = $this->GetParam('show');
		if ($show == 'command')
			$voie = 0;

        ?>
		<script type="text/javascript" src="./js/voie.js" ></script>
 		<script type="text/javascript" src="./js/tv.js" ></script>
        <script type="text/javascript"> $(document).ready(function(){ Init(<?php echo $voie;?>); }); </script>	
        <?php
    }
}

new TV($_GET);
?>