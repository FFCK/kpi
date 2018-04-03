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
            <title>KPI TV ()</title>
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
            <link href="./css/tv2.css" rel="stylesheet">

        </head>
    <?php
    }
	
	function VerifNation($nation)
	{
		if (strlen($nation) > 3) $nation = substr($nation, 0, 3);
		
		for ($i=0; $i<strlen($nation); $i++)
		{
			$c = substr($nation, $i, 1);
			if ($c >= '0' && $c <= '9') return 'FRA';
		}
		return $nation;
	}

	function ImgNation($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre' src='./img/nation/".$nation.".png' height='32' width='32' />";
	}
	
	function ImgNation48($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre' src='./img/nation/".$nation.".png' height='48' width='48' />";
	}

	
	function ImgNation64($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre' src='./img/nation/".$nation.".png' height='64' width='64' />";
	}
	
	function ImgNationCss($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='img_nation' src='./img/nation/".$nation.".png'>";
	}
	
	function ImgNationCss2($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='img_nation2' src='./img/nation/".$nation.".png'>";
	}
	
	function ImgMedal($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
		return "<img class='centre' src='./img/".$medal.".gif' height='32' width='32' />";
	}
	
	function ImgMedal48($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
		return "<img class='centre' src='./img/".$medal.".gif' height='48' width='48' />";
	}
	
	function ImgMedal64($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
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
	
	function CutReferee(&$referee)
	{
		$referee = explode(' (', trim($referee));
		
		return $referee[0];
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
		
		// Chargement Equipe  
		$cmd  = "SELECT c.Libelle, c.Code_club "
                . "FROM gickp_Competitions_Equipes c "
                . "LEFT OUTER JOIN gickp_Matchs m ON (c.Id = m.Id_equipe" . $equipe . ") "
                . "WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        // Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, "
                . "CASE WHEN a.Capitaine = 'E' THEN 1 ELSE 0 END joueur "
                . "FROM gickp_Matchs_Joueurs a, gickp_Liste_Coureur b "
                . "WHERE a.Id_match = $idMatch "
                . "AND a.Equipe = '$equipe' "
                . "AND a.Matric = b.matric "
//                . "AND (a.Capitaine Is Null OR a.Capitaine != 'E') "
                . "ORDER BY joueur, a.Numero ";

		$tJoueurs = null;
        $coach = false;
		$db->LoadTable($cmd, $tJoueurs);
        
        echo '
            <div class="container-fluid">
                <div id="banner_list" class="text-center">
                    <div id="banner_line1" class="h2">' . $this->ImgNation48($rEquipe['Code_club']) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
            ';
        foreach ($tJoueurs as $key => $joueur) {
            if(utyGetString($joueur, 'Capitaine', '???') != 'E') {
                if(utyGetString($joueur, 'Capitaine', '') == 'C') {
                    $captain = ' (Capt)';
                } else {
                    $captain = '';
                }
                echo '
                    <div class="banner_line">
                        <span class="badge">' . utyGetInt($joueur, 'Numero', 999) . '</span>
                        &nbsp;
                        <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . $captain . '</span>
                    </div>';
            } else {
                $coach = true;
            }
        }
        if($coach) {
            echo '<div class="banner_line">&nbsp;</div>';
            foreach ($tJoueurs as $key => $joueur) {
                if(utyGetString($joueur, 'Capitaine', '???') == 'E') {
                    echo '
                        <div class="banner_line">
                            <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . ' (Coach)</span>
                        </div>';
                }
            }
        }
        echo '
                </div>
            </div>';
    }

	function Content_List_Medals()
    {
		$db = new MyBdd();
		
		$competition = $this->GetParam('competition');

		// Chargement Record Compétition ...
//		$rCompetition = null;
//		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$competition."' And Code_saison = " . utyGetSaison(), $rCompetition);

		// Chargement des Equipes Classées ...
		$cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi, c.Soustitre2 "
                . "FROM gickp_Competitions c, gickp_Competitions_Equipes ce "
                . "WHERE ce.Code_compet = '".$competition."' "
                . "AND ce.Code_saison = " . utyGetSaison() . " "
                . "AND c.Code = ce.Code_compet "
                . "AND c.Code_saison = ce.Code_saison "
                . "ORDER BY CltNiveau_publi "
                . "LIMIT 0, 3 ";
		
		$tEquipes = null;
		$db->LoadTable($cmd, $tEquipes);
//		echo "<div id='banner_presentation'></div>\n";
//		echo "<div id='list_medals_title'>$title</div>\n";

		if (count($tEquipes) != 3) {
            return;
        }
		
        echo '
            <div class="container-fluid">
                <div id="podium" class="text-center">
                    <div id="podium_line1">' . $this->ImgNationCss($tEquipes[0]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[0], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_line2">' . $this->ImgNationCss($tEquipes[1]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[1], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_line3">' . $this->ImgNationCss($tEquipes[2]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[2], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_categorie">
                        <span>' . utyGetString($tEquipes[0], 'Soustitre2', '???') . '</span>
                    </div>
                </div>
            </div>';
		?>
<!--		<table id="table_medals">
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
		</table>-->
        
	<?php
    

    
	}

	function Content_Player()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$numero = $this->GetParam('number', '1');
		
		// Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Numero_comite_dept "
                . "FROM gickp_Matchs_Joueurs a, gickp_Liste_Coureur b "
                . "WHERE a.Id_match = $idMatch "
                . "AND a.Equipe = '$equipe' "
                . "AND a.Matric = b.matric "
                . "AND a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">' . $this->ImgNationCss($rJoueur['Numero_comite_dept']) . '&nbsp;
                        <span>
                        ' . ' ' . $numero
                            . ' - ' . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') . '
                        </span>
                    </div>
                </div>
            </div>';
	}

	function Content_Player_Medal()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$numero = $this->GetParam('number', '1');
		$medaille = $this->GetParam('medal');
		
		// Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Numero_comite_dept "
                . "FROM gickp_Matchs_Joueurs a, gickp_Liste_Coureur b "
                . "WHERE a.Id_match = $idMatch "
                . "AND a.Equipe = '$equipe' "
                . "AND a.Matric = b.matric "
                . "AND a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">' . $this->ImgNationCss($rJoueur['Numero_comite_dept']) . '&nbsp;
                        <span>
                        ' . ' ' . $numero
                            . ' - ' . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') . '
                        </span>
                    </div>
                    <div id="banner_line2">' . $this->ImgMedal($medaille) . '&nbsp;
                        <span>
                        ' . $this->LabelMedal($medaille) . '
                        </span>
                    </div>
                </div>
            </div>';
	}

	function Content_Referee()
    {
		$db = new MyBdd();
		$idMatch = $this->GetParamInt('match',-1);
		
		$rMatch = null;
        $sql = "SELECT m.*, lc1.Numero_comite_dept nation1, lc2.Numero_comite_dept nation2 "
                . "FROM gickp_Matchs m "
                . "LEFT OUTER JOIN gickp_Liste_Coureur lc1 ON (m.Matric_arbitre_principal = lc1.Matric) "
                . "LEFT OUTER JOIN gickp_Liste_Coureur lc2 ON (m.Matric_arbitre_secondaire = lc2.Matric) "
                . "WHERE Id = $idMatch";
		$db->LoadRecord($sql, $rMatch);
		
		$arbitre1  = $this->CutReferee($rMatch['Arbitre_principal']);
        $nation1 = $rMatch['nation1'];
		$nation1 = $this->VerifNation($nation1);

		$arbitre2  = $this->CutReferee($rMatch['Arbitre_secondaire']);
        $nation2 = $rMatch['nation2'];
		$nation2 = $this->VerifNation($nation2);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">First Referee : 
                        ' . $this->ImgNationCss($nation1) . '&nbsp;
                        <span>' . $arbitre1 . ' (' . $nation1 . ')</span>
                    </div>
                    <div id="banner_line2">Second Referee : 
                        ' . $this->ImgNationCss($nation2) . '&nbsp;
                        <span>' . $arbitre2 . ' (' . $nation2 . ')</span>
                    </div>
                </div>
            </div>';
	}

	function Content_Match()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		
		// Chargement Match
		$cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, "
                . "ce1.Code_club ClubA, ce2.Code_club ClubB, "
                . "m.Terrain, j.Phase, c.Soustitre2 categorie "
                . "FROM gickp_Journees j, gickp_Competitions c, gickp_Matchs m "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON (ce1.Id = m.Id_equipeA) "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON (ce2.Id = m.Id_equipeB) "
                . "WHERE m.Id = $idMatch "
                . "AND m.Id_journee = j.Id "
                . "AND j.Code_competition = c.Code "
                . "AND j.Code_saison = c.Code_saison ";

		$rMatch = null;
		$db->LoadRecord($cmd, $rMatch);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">
                        ' . utyGetString($rMatch, 'categorie', '???') . '
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '
                    </div>
                    <div id="banner_line2" class="row">
                        <div class="col-md-6">
                            ' . $this->ImgNationCss2($rMatch['ClubA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '???') . '
                            </span>
                            &nbsp;' . $this->ImgNationCss2($rMatch['ClubB']) . '
                        </div>
                    </div>
                </div>
            </div>';
	}

	function Content_Match_Score()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		
		// Chargement Match
		$cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, "
                . "ce1.Code_club ClubA, ce2.Code_club ClubB, "
                . "m.Terrain, m.ScoreDetailA, m.ScoreDetailB, j.Phase, c.Soustitre2 categorie "
                . "FROM gickp_Journees j, gickp_Competitions c, gickp_Matchs m "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON (ce1.Id = m.Id_equipeA) "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON (ce2.Id = m.Id_equipeB) "
                . "WHERE m.Id = $idMatch "
                . "AND m.Id_journee = j.Id "
                . "AND j.Code_competition = c.Code "
                . "AND j.Code_saison = c.Code_saison ";

		$rMatch = null;
		$db->LoadRecord($cmd, $rMatch);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">
                        ' . utyGetString($rMatch, 'categorie', '???') . '
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '
                    </div>
                    <div id="banner_line2" class="row">
                        <div class="col-md-5">
                            ' . $this->ImgNationCss2($rMatch['ClubA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                        </div>
                        <div class="col-md-2">
                            ' . $rMatch['ScoreDetailA'] . ' - ' . $rMatch['ScoreDetailB'] . '
                        </div>
                        <div class="col-md-5">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '???') . '
                            </span>
                            &nbsp;' . $this->ImgNationCss2($rMatch['ClubB']) . '
                        </div>
                    </div>
                </div>
            </div>';
	}
	
	function Content_Team()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		
		// Chargement Equipe  
		$cmd  = "SELECT c.Libelle, c.Code_club "
                . "FROM gickp_Competitions_Equipes c "
                . "LEFT OUTER JOIN gickp_Matchs m ON (c.Id = m.Id_equipe" . $equipe . ") "
                . "WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">' . $this->ImgNationCss($rEquipe['Code_club']) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
                </div>
            </div>';
	}

	function Content_Team_Medal()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$medaille = $this->GetParam('medal');
			
        // Chargement Equipe  
		$cmd  = "SELECT c.Libelle, c.Code_club "
                . "FROM gickp_Competitions_Equipes c "
                . "LEFT OUTER JOIN gickp_Matchs m ON (c.Id = m.Id_equipe" . $equipe . ") "
                . "WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        echo '
            <div class="container-fluid">
                <div id="banner" class="text-center">
                    <div id="banner_line1">' . $this->ImgNationCss($rEquipe['Code_club']) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
                    <div id="banner_line2">' . $this->ImgMedal($medaille) . '&nbsp;
                        <span>
                        ' . $this->LabelMedal($medaille) . '
                        </span>
                    </div>
                </div>
            </div>';

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
<!--		<form>
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
		</table>-->
	
<?php	
	}
	
    function Content()
    {
		$show = $this->GetParam('show', 'command');
        switch ($show) {
            case 'list_team':
                $this->Content_List_Team();
                return;
                break;
            case 'list_medals':
                $this->Content_List_Medals();
                return;
                break;
            case 'player':
                $this->Content_Player();
                return;
                break;
            case 'player_medal':
                $this->Content_Player_Medal();
                return;
                break;
            case 'referee':
                $this->Content_Referee();
                return;
                break;
            case 'match':
                $this->Content_Match();
                return;
                break;
            case 'match_score':
                $this->Content_Match_Score();
                return;
                break;
            case 'team':
                $this->Content_Team();
                return;
                break;
            case 'team_medal':
                $this->Content_Team_Medal();
                return;
                break;
            case 'command':
                $this->Content_Command();
                return;
                break;
        }
	}

    function Script()
    {
        parent::Script();
		$voie = $this->GetParamInt('voie',0);

		$show = $this->GetParam('show');
		if ($show == 'command') {
            $voie = 0;
        }
        ?>
		<script type="text/javascript" src="./js/voie.js" ></script>
 		<script type="text/javascript" src="./js/tv.js" ></script>
        <script type="text/javascript">
            $(document).ready(function(){ 
                Init(<?= $voie; ?>);
                document.title = 'KPI TV (' + <?= $voie; ?> + ')';
            }); 
        </script>	
        <?php
    }
}

new TV($_GET);
