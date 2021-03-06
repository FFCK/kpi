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
            <link href="./css/tv2.css?v=<?= NUM_VERSION ?>" rel="stylesheet">

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
		return "<img class='centre text-top' src='../img/Nations/".$nation.".png' height='32' width='32' />";
	}
	
	function ImgNation48($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre text-top' src='../img/Nations/".$nation.".png' height='48' width='48' />";
	}

	
	function ImgNation64($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre text-top' src='../img/Nations/".$nation.".png' height='64' width='64' />";
	}
	
	function ImgNationFull($nation)
	{
		$nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) { return ''; }
		return "<img class='centre text-top' src='../img/Nations/".$nation.".png' />";
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
	
    function GetMedal($rank) {
        switch ($rank) {
            case 1:
                return 'GOLD';
                break;
            case 2:
                return 'SILVER';
                break;
            case 3:
                return 'BRONZE';
                break;
            default:
                return '';
                break;
        }
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
		$cmd  = "SELECT c.Libelle, c.Code_club 
            FROM kp_competition_equipe c 
            LEFT OUTER JOIN kp_match m ON (c.Id = m.Id_equipe" . $equipe . ") 
            WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        // Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, 
            CASE WHEN a.Capitaine = 'E' THEN 1 ELSE 0 END joueur 
            FROM kp_match_joueur a, kp_licence b 
            WHERE a.Id_match = $idMatch 
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            ORDER BY joueur, a.Numero ";
            //  . "AND (a.Capitaine Is Null OR a.Capitaine != 'E') "

		$tJoueurs = null;
        $coach = false;
		$db->LoadTable($cmd, $tJoueurs);
        
        echo '
            <div class="container-fluid ban_list">
                <div class="logo_sm2"></div>
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-right">
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                        ' . $this->ImgNation64(utyGetString($rEquipe, 'Code_club', 'FRA')) . '&nbsp;
                    </div>
                    <div id="banner_line2" class="h2 text-center">
                        <span>CANOE POLO</span>
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
                                    <div class="col-md-2 text-right clair">' . utyGetInt($joueur, 'Numero', 999) . '</div>
                                    <div class="col-md-10">' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . $captain . '</div>
<!--                                    <span class="label label-primary numero">' . utyGetInt($joueur, 'Numero', 999) . '</span>
                                    &nbsp;
                                    <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . $captain . '</span>
-->                             </div>';
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
		$cmd  = "SELECT c.Libelle, c.Code_club 
            FROM kp_competition_equipe c 
            LEFT OUTER JOIN kp_match m ON (c.Id = m.Id_equipe" . $equipe . ") 
            WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        // Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, 
            CASE WHEN a.Capitaine = 'E' THEN 1 ELSE 0 END joueur 
            FROM kp_match_joueur a, kp_licence b 
            WHERE a.Id_match = $idMatch 
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            ORDER BY joueur, a.Numero ";
            // . "AND (a.Capitaine Is Null OR a.Capitaine != 'E') "

		$tJoueurs = null;
        $coach = false;
		$db->LoadTable($cmd, $tJoueurs);
        
        echo '
            <div class="container-fluid ban_list">
                <div class="logo_sm2"></div>
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-right">
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                        ' . $this->ImgNation64(utyGetString($rEquipe, 'Code_club', 'FRA')) . '&nbsp;
                    </div>
                    <div id="banner_line2" class="h2 text-center">
                        <span>CANOE POLO</span>
                    </div>
                <div id="banner_lines">
            ';
                    foreach ($tJoueurs as $key => $joueur) {
                        if(utyGetString($joueur, 'Capitaine', '???') == 'E') {
                            echo '
                                <div class="banner_line">
                                    <div class="col-md-2 text-right clair">COACH</div>
                                    <div class="col-md-10">' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . '</div>
<!--                                    <span>' . utyGetString($joueur, 'Nom', '???') . '&nbsp;' . utyGetPrenom($joueur, 'Prenom', '???') . ' (Coach)</span> -->
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
		$cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi, c.Soustitre2 
            FROM kp_competition c, kp_competition_equipe ce 
            WHERE ce.Code_compet = '$competition' 
            AND ce.Code_saison = $saison 
            AND c.Code = ce.Code_compet 
            AND c.Code_saison = ce.Code_saison 
            ORDER BY CltNiveau_publi 
            LIMIT 0, 3 ";
		
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
		$cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi rank, c.Soustitre2 
            FROM kp_competition c, kp_competition_equipe ce 
            WHERE ce.Code_compet = '$competition' 
            AND ce.Code_saison = $saison 
            AND c.Code = ce.Code_compet 
            AND c.Code_saison = ce.Code_saison 
            ORDER BY CltNiveau_publi 
            LIMIT $start, 10 ";
		
		$tEquipes = null;
		$db->LoadTable($cmd, $tEquipes);
        
        echo '
            <div class="container-fluid ban_list">
                <div class="logo_sm2"></div>
                <div id="banner_list" class="final_ranking">
                    <div id="banner_line1" class="h2 text-center">
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
                                <span>' . utyGetString($equipe, 'Libelle', '???') . '&nbsp;</span>' 
                                . $this->ImgMedal48($this->GetMedal(utyGetInt($equipe, 'rank', 999))) . '
                                    
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
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, 
            b.Naissance, b.Numero_comite_dept 
            FROM kp_match_joueur a, kp_licence b 
            WHERE a.Id_match = $idMatch 
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            AND a.Capitaine != 'E' 
            AND a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
        $num = '<span class="clair">' . $numero . '</span> ';
        
        if(utyGetString($rJoueur, 'Capitaine', '???') == 'C') {
            $capitaine = ' <span class="label label-warning capitaine">C</span>';
        } else if(utyGetString($rJoueur, 'Capitaine', '???') == 'E') {
            $capitaine = ' (Coach)';
            $num = '';
        } else {
            $capitaine = '';
        }
        
        echo '
            <div class="container-fluid ban_goal_card">
                <div id="goal_card">' . $this->ImgNationFull(utyGetString($rJoueur, 'Numero_comite_dept', '???')) . '</div>
                <div id="banner_goal_card" class="text-left">
                    <div id="match_event_line2" class="banner_line text-left">
                        &nbsp;
                        <span>' . $num
                            . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') 
                            . $capitaine . '
                        </span>
                    </div>
                    <div id="match_event_line1" class="banner_line text-left">
                        ' . utyGetString($rJoueur, 'Numero_comite_dept', '???') . '
                    </div>
                </div>
            </div>';
	}

	function Content_Coach()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		$equipe = $this->GetParam('team', 'A');
		$numero = $this->GetParam('number', '1');
		
		// Chargement Joueurs  
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, 
            b.Naissance, b.Numero_comite_dept 
            FROM kp_match_joueur a, kp_licence b 
            WHERE a.Id_match = $idMatch 
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            AND a.Capitaine = 'E' 
            AND a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
        $num = '<span class="clair">' . $numero . '</span> ';
        
        if(utyGetString($rJoueur, 'Capitaine', '???') == 'C') {
            $capitaine = ' <span class="label label-warning capitaine">C</span>';
        } else if(utyGetString($rJoueur, 'Capitaine', '???') == 'E') {
            $capitaine = ' (Coach)';
            $num = '';
        } else {
            $capitaine = '';
        }
        
        echo '
            <div class="container-fluid ban_goal_card">
                <div id="goal_card">' . $this->ImgNationFull(utyGetString($rJoueur, 'Numero_comite_dept', '???')) . '</div>
                <div id="banner_goal_card" class="text-left">
                    <div id="match_event_line2" class="banner_line text-left">
                        &nbsp;
                        <span>' . $num
                            . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') 
                            . $capitaine . '
                        </span>
                    </div>
                    <div id="match_event_line1" class="banner_line text-left">
                        ' . utyGetString($rJoueur, 'Numero_comite_dept', '???') . '
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
		$cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, 
            b.Naissance, b.Numero_comite_dept 
            FROM kp_match_joueur a, kp_licence b 
            WHERE a.Id_match = $idMatch 
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            AND a.Numero = $numero ";

		$rJoueur = null;
		$db->LoadRecord($cmd, $rJoueur);
        $num = '<span class="label label-primary numero">' . $numero . '</span> ';

        if(utyGetString($rJoueur, 'Capitaine', '???') == 'C') {
            $capitaine = ' <span class="label label-warning capitaine">C</span>';
        } else if(utyGetString($rJoueur, 'Capitaine', '???') == 'E') {
            $capitaine = ' (Coach)';
            $num = '';
        } else {
            $capitaine = '';
        }
        
        echo '
            <div class="container-fluid ban_double">
                <div id="banner_double" class="text-center">
                    <div class="banner_line">' . $this->ImgNation48($rJoueur['Numero_comite_dept']) . '&nbsp;
                        <span>' . $num
                            . utyGetString($rJoueur, 'Nom', '???') 
                            . ' ' . utyGetPrenom($rJoueur, 'Prenom','...') 
                            . $capitaine . '
                        </span>
                    </div>
                    <div class="banner_line">' . $this->ImgMedal48($medaille) . '&nbsp;
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
        $sql = "SELECT m.*, 
            lc1.Numero_comite_dept nation1, lc1.Nom nom_arb1, lc1.Prenom prenom_arb1, 
            lc2.Numero_comite_dept nation2, lc2.Nom nom_arb2, lc2.Prenom prenom_arb2 
            FROM kp_match m 
            LEFT OUTER JOIN kp_licence lc1 ON (m.Matric_arbitre_principal = lc1.Matric) 
            LEFT OUTER JOIN kp_licence lc2 ON (m.Matric_arbitre_secondaire = lc2.Matric) 
            WHERE Id = $idMatch";
		$db->LoadRecord($sql, $rMatch);
		
//		$arbitre1  = $this->CutReferee($rMatch['Arbitre_principal']);
        $arbitre1 = strtoupper($rMatch['nom_arb1']) . ' ' . utyUcName($rMatch['prenom_arb1']);
        $nation1 = $rMatch['nation1'];
		$nation1 = $this->VerifNation($nation1);
        $nation1par = ($nation1 != '') ? ' (' . $nation1 . ')' : '';

//		$arbitre2  = $this->CutReferee($rMatch['Arbitre_secondaire']);
        $arbitre2 = strtoupper($rMatch['nom_arb2']) . ' ' . utyUcName($rMatch['prenom_arb2']);
        $nation2 = $rMatch['nation2'];
		$nation2 = $this->VerifNation($nation2);
        $nation2par = ($nation2 != '') ? ' (' . $nation2 . ')' : '';
        
        echo '
            <div class="container-fluid ban_info_2_lines">
                <div id="ban_info_2_lines" class="text-center">
                    <div class="logo_sm2"></div>
                    <div id="banner_line1" class="h2 text-right">REFEREES</div>
                    <div id="banner_line2" class="h2 text-right">
                        <span>CANOE POLO</span>
                    </div>
                    <div class="banner_line">
                        <div class="col-md-3 text-right clair">' . $this->ImgNation48($nation1) . ' ' . $nation1 . '</div>
                        <div class="col-md-9 text-left">' . $arbitre1 . '</div>
                    </div>
                    <div class="banner_line"> 
                        <div class="col-md-3 text-right clair">' . $this->ImgNation48($nation2) . ' ' . $nation2 . '</div>
                        <div class="col-md-9 text-left">' . $arbitre2 . '</div>
                    </div>
                </div>
            </div>';
	}

	function Content_Match()
    {
		$db = new MyBdd();
		
		$idMatch = $this->GetParamInt('match',-1);
		
		// Chargement Match
		$cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, 
            ce1.Code_club ClubA, ce2.Code_club ClubB, 
            m.Terrain, j.Phase, c.Soustitre2 categorie 
            FROM kp_journee j, kp_competition c, kp_match m 
            LEFT OUTER JOIN kp_competition_equipe ce1 ON (ce1.Id = m.Id_equipeA) 
            LEFT OUTER JOIN kp_competition_equipe ce2 ON (ce2.Id = m.Id_equipeB) 
            WHERE m.Id = $idMatch 
            AND m.Id_journee = j.Id 
            AND j.Code_competition = c.Code 
            AND j.Code_saison = c.Code_saison ";

		$rMatch = null;
		$db->LoadRecord($cmd, $rMatch);
        
        echo '
            <div class="container-fluid ban_presentation">
                <div class="logo_lg"></div>
                <div id="banner_presentation" class="text-center">
                    <div class="banner_line line1">
                        ' . utyGetString($rMatch, 'categorie', '???') . '
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '
                    </div>
                    <div class="row banner_line line2">
                        <div class="col-md-6 text-right">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                            &nbsp;' . $this->ImgNation48($rMatch['ClubA']) . '
                        </div>
                        <div class="col-md-6 text-left">
                            ' . $this->ImgNation48($rMatch['ClubB']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '???') . '
                            </span>
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
		$cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, 
            ce1.Code_club ClubA, ce2.Code_club ClubB, 
            m.Terrain, m.ScoreDetailA, m.ScoreDetailB, j.Phase, c.Soustitre2 categorie 
            FROM kp_journee j, kp_competition c, kp_match m 
            LEFT OUTER JOIN kp_competition_equipe ce1 ON (ce1.Id = m.Id_equipeA) 
            LEFT OUTER JOIN kp_competition_equipe ce2 ON (ce2.Id = m.Id_equipeB) 
            WHERE m.Id = $idMatch 
            AND m.Id_journee = j.Id 
            AND j.Code_competition = c.Code 
            AND j.Code_saison = c.Code_saison ";

		$rMatch = null;
		$db->LoadRecord($cmd, $rMatch);
        
        echo '
            <div class="container-fluid ban_info_1_lines">
                <div id="ban_info_1_lines" class="text-center">
                    <div class="logo_sm2"></div>
                    <div id="banner_line1" class="h2 text-right">
                        ' . utyGetString($rMatch, 'Phase', '???') . '
                    <!--     - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '???') . '-->
                    </div>
                    <div id="banner_line2" class="h2 text-right">' . utyGetString($rMatch, 'categorie', '???') . '</div>

                    <div class="row banner_line">
                        <div class="col-md-5 text-left">
                            ' . $this->ImgNation48($rMatch['ClubA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '???') . '
                            </span>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="label label-primary numero">' . $rMatch['ScoreDetailA'] . '</span>
                             &nbsp;
                            <span class="label label-primary numero">' . $rMatch['ScoreDetailB'] . '</span>
                        </div>
                        <div class="col-md-5 text-right">
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
		$cmd  = "SELECT c.Libelle, c.Code_club 
            FROM kp_competition_equipe c 
            LEFT OUTER JOIN kp_match m ON (c.Id = m.Id_equipe" . $equipe . ") 
            WHERE m.Id = $idMatch";

		$rEquipe = null;
		$db->LoadRecord($cmd, $rEquipe);
        
        echo '
            <div class="container-fluid ban_single">
                <div class="logo_xs"></div>
                <div id="banner_single" class="text-center">
                    <div class="banner_line">
                        <span>
                            ' . ' ' . utyGetString($rEquipe, 'Libelle', '???') . '
                        </span>
                        ' . $this->ImgNation48($rEquipe['Code_club']) . '&nbsp;
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
		$cmd  = "SELECT c.Libelle, c.Code_club 
            FROM kp_competition_equipe c 
            LEFT OUTER JOIN kp_match m ON (c.Id = m.Id_equipe" . $equipe . ") 
            WHERE m.Id = $idMatch";

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
                    <div class="banner_line">' . $this->ImgMedal48($medaille) . '&nbsp;
                        <span>
                        ' . $this->LabelMedal($medaille) . '
                        </span>
                    </div>
                </div>
            </div>';

	}

	function Content_Voie()
    {
        $voie = $this->GetParamInt('voie',0);
        echo '
            <div class="container-fluid nuage">
                <div class="voie">
                    <button type="button" class="btn btn-light btn-lg">' . $voie . '</button>
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
            case 'voie':
                $this->Content_Voie();
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
            case 'coach':
                $this->Content_Coach();
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
		$voie = $this->GetParamInt('voie', 0);
		$intervalle = $this->GetParamInt('intervalle', 3000);

		$show = $this->GetParam('show');
		if ($show == 'command') {
            $voie = 0;
        }
        ?>
		<script type="text/javascript" src="./js/voie.js?v=<?= NUM_VERSION ?>" ></script>
 		<script type="text/javascript" src="./js/tv.js?v=<?= NUM_VERSION ?>" ></script>
        <script type="text/javascript">
            $(document).ready(function(){ 
                Init(<?= $voie; ?>, <?= $intervalle; ?>);
                document.title = 'KPI TV (' + <?= $voie; ?> + ')';
            }); 
        </script>	
        <?php
    }
}

new TV($_GET);
