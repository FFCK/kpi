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
		return "<img class='centre' src='../img/Nations/".$nation.".png' height='32' width='32' />";
	}
	
	function ImgNation48($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre' src='../img/Nations/".$nation.".png' height='48' width='48' />";
	}

	
	function ImgNation64($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre' src='../img/Nations/".$nation.".png' height='64' width='64' />";
	}
	
	function ImgNationCss($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='img_nation' src='../img/Nations/".$nation.".png'>";
	}
	
	function ImgNationCss2($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='img_nation2' src='../img/Nations/".$nation.".png'>";
	}
	
	function ImgMedal($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
		return "<img class='centre' src='../img/".$medal.".gif' height='32' width='32' />";
	}
	
	function ImgMedal48($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
		return "<img class='centre' src='../img/".$medal.".gif' height='48' width='48' />";
	}
	
	function ImgMedal64($medal)
	{
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') { return ''; }
		return "<img class='centre' src='../img/".$medal.".gif' height='64' width='64' />";
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
            <div class="container-fluid ban_list">
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-center">' . $this->ImgNation48(utyGetString($rEquipe, 'Code_club', 'FRA')) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
                <div id="banner_lines">
            ';
                    foreach ($tJoueurs as $key => $joueur) {
                        if(utyGetString($joueur, 'Capitaine', '???') != 'E') {
                            if(utyGetString($joueur, 'Capitaine', '') == 'C') {
                                $captain = ' <span class="label label-warning capitaine">C</span>';
                            } else {
                                $captain = '';
                            }
                            echo '
                                <div class="banner_line">
                                    <span class="label label-primary numero">' . utyGetInt($joueur, 'Numero', 999) . '</span>
                                    &nbsp;
                                    <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . $captain . '</span>
                                </div>';
                        }
                    }
        echo '
                    </div>
                </div>
            </div>';
    }
	function Content_List_Coachs()
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
            <div class="container-fluid ban_list">
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-center">' . $this->ImgNation48(utyGetString($rEquipe, 'Code_club', 'FRA')) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
                <div id="banner_lines">
            ';
                    foreach ($tJoueurs as $key => $joueur) {
                        if(utyGetString($joueur, 'Capitaine', '???') == 'E') {
                            echo '
                                <div class="banner_line">
                                    <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . ' (coach)</span>
                                </div>';
                        }
                    }
        echo '
                    </div>
                </div>
            </div>';
    }

	function Content_List_Medals()
    {

        $db = new MyBdd();
		
		$competition = $this->GetParam('competition');
		$saison = $this->GetParam('saison', utyGetSaison());

		// Chargement des Equipes Classées ...
		$cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi, c.Soustitre2 "
                . "FROM gickp_Competitions c, gickp_Competitions_Equipes ce "
                . "WHERE ce.Code_compet = '".$competition."' "
                . "AND ce.Code_saison = " . $saison . " "
                . "AND c.Code = ce.Code_compet "
                . "AND c.Code_saison = ce.Code_saison "
                . "ORDER BY CltNiveau_publi "
                . "LIMIT 0, 3 ";
		
		$tEquipes = null;
		$db->LoadTable($cmd, $tEquipes);

        if (count($tEquipes) != 3) {
            return;
        }
		
        echo '
            <div class="container-fluid podium">
                <div id="podium" class="text-center">
                    <div id="podium_line1">' . $this->ImgNation48($tEquipes[0]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[0], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_line2">' . $this->ImgNation48($tEquipes[1]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[1], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_line3">' . $this->ImgNation48($tEquipes[2]['Code_club']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[2], 'Libelle', '???') . '</span>
                    </div>
                    <div id="podium_categorie">
                        <span>' . utyGetString($tEquipes[0], 'Soustitre2', '???') . '</span>
                    </div>
                </div>
            </div>';
	}

	function Content_Final_Ranking()
    {
        $db = new MyBdd();
		
		$competition = $this->GetParam('competition');
		$saison = $this->GetParam('saison', utyGetSaison());
		$start = $this->GetParam('start', 0);
        
		// Chargement des Equipes Classées ...
		$cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi rank, c.Soustitre2 "
                . "FROM gickp_Competitions c, gickp_Competitions_Equipes ce "
                . "WHERE ce.Code_compet = '".$competition."' "
                . "AND ce.Code_saison = " . $saison . " "
                . "AND c.Code = ce.Code_compet "
                . "AND c.Code_saison = ce.Code_saison "
                . "ORDER BY CltNiveau_publi "
                . "LIMIT $start, 10 ";
		
		$tEquipes = null;
		$db->LoadTable($cmd, $tEquipes);

        echo '
            <div class="container-fluid ban_list">
                <div id="banner_list">
                    <div id="banner_line2" class="h2 text-center">
                        FINAL RANKING<br>
                        <span class="categorie">
                        ' . ' ' . utyGetString($tEquipes[0], 'Soustitre2', '???') . '
                        </span>
                    </div>
                <div id="banner_lines">
            ';
                    foreach ($tEquipes as $key => $equipe) {
                        echo '
                            <div class="banner_line">
                                <span class="label label-primary numero">' . utyGetInt($equipe, 'rank', 999) . '</span>
                                &nbsp;' . $this->ImgNation48(utyGetString($equipe, 'Code_club', 999)) . '&nbsp;
                                <span>' . utyGetString($equipe, 'Libelle', '???') . '&nbsp;</span>
                            </div>';
                    }
        echo '
                    </div>
                </div>
            </div>';
        
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
            <div class="container-fluid ban_single">
                <div id="banner_single" class="text-center">
                    <div class="banner_line">' . $this->ImgNation48(utyGetString($rJoueur, 'Numero_comite_dept', '???')) . '&nbsp;
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
            <div class="container-fluid ban_double">
                <div id="banner_double" class="text-center">
                    <div class="banner_line">' . $this->ImgNation48($rJoueur['Numero_comite_dept']) . '&nbsp;
                        <span>
                        ' . ' ' . $numero
                            . ' - ' . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') . '
                        </span>
                    </div>
                    <div class="banner_line">' . $this->ImgMedal($medaille) . '&nbsp;
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
            <div class="container-fluid ban_double">
                <div id="banner_double" class="text-center">
                    <div class="banner_line">First Referee : 
                        ' . $this->ImgNation48($nation1) . '&nbsp;
                        <span>' . $arbitre1 . ' (' . $nation1 . ')</span>
                    </div>
                    <div class="banner_line">Second Referee : 
                        ' . $this->ImgNation48($nation2) . '&nbsp;
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
            <div class="container-fluid ban_presentation">
                <div id="banner_presentation" class="text-center">
                    <div class="banner_line">
                        ' . utyGetString($rMatch, 'categorie', '???') . '
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '
                    </div>
                    <div class="row banner_line">
                        <div class="col-md-6">
                            ' . $this->ImgNation48($rMatch['ClubA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '???') . '
                            </span>
                            &nbsp;' . $this->ImgNation48($rMatch['ClubB']) . '
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
            <div class="container-fluid ban_presentation">
                <div id="banner_presentation" class="text-center">
                    <div class="banner_line">
                        ' . utyGetString($rMatch, 'categorie', '???') . '
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '
                    </div>
                    <div class="row banner_line">
                        <div class="col-md-5">
                            ' . $this->ImgNation48($rMatch['ClubA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                        </div>
                        <div class="col-md-2">
                            <span class="label label-primary numero">' . $rMatch['ScoreDetailA'] . '</span>
                             &nbsp;
                            <span class="label label-primary numero">' . $rMatch['ScoreDetailB'] . '</span>
                        </div>
                        <div class="col-md-5">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '???') . '
                            </span>
                            &nbsp;' . $this->ImgNation48($rMatch['ClubB']) . '
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
            <div class="container-fluid ban_single">
                <div id="banner_single" class="text-center">
                    <div class="banner_line">' . $this->ImgNation48($rEquipe['Code_club']) . '&nbsp;
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
            <div class="container-fluid ban_double">
                <div id="banner_double" class="text-center">
                    <div class="banner_line">' . $this->ImgNation48($rEquipe['Code_club']) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                    </div>
                    <div class="banner_line">' . $this->ImgMedal($medaille) . '&nbsp;
                        <span>
                        ' . $this->LabelMedal($medaille) . '
                        </span>
                    </div>
                </div>
            </div>';

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


	
    function Content()
    {
		$show = $this->GetParam('show', '');
        switch ($show) {
            case 'list_team':
                $this->Content_List_Team();
                return;
                break;
            case 'list_coachs':
                $this->Content_List_Coachs();
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
            case 'final_ranking':
                $this->Content_Final_Ranking();
                return;
                break;
            default:
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
