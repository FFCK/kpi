<?php
//include_once('base.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');

include_once('page.php');

class TV extends MyPage
{
    function Header()
    {
    }
    function Footer()
    {
    }

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
            <link href="../lib/bootstrap-5.1.3-dist/css/bootstrap.min.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
            <link href="../css/animate/animate.4.1.1.css?v=<?= NUM_VERSION ?>" rel="stylesheet" />
            <link href="./css/tv2.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
            <?= $this->CheckCss() ?>

        </head>
    <?php
    }

    function VerifNation($nation)
    {
        if (strlen($nation) > 3) $nation = substr($nation, 0, 3);

        for ($i = 0; $i < strlen($nation); $i++) {
            $c = substr($nation, $i, 1);
            if ($c >= '0' && $c <= '9') return 'FRA';
        }
        return $nation;
    }

    function ImgNation($nation, $logo = null)
    {
        if ($logo) {
            return "<img src='../img/" . $logo . "?v=" . NUM_VERSION . "' height='32' />";
        }
        $nation = $this->VerifNation($nation, $logo = null);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' height='32' />";
    }

    function ImgNation48($nation, $logo = null)
    {
        if ($logo) {
            return "<img src='../img/" . $logo . "?v=" . NUM_VERSION . "' height='48' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' height='48' />";
    }


    function ImgNation64($nation, $logo = null)
    {
        if ($logo) {
            return "<img src='../img/" . $logo . "?v=" . NUM_VERSION . "' height='64' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' height='64' />";
    }

    function ImgNation80($nation, $logo = null)
    {
        if ($logo) {
            return "<img src='../img/" . $logo . "?v=" . NUM_VERSION . "' height='80' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' height='80' />";
    }

    function ImgNation200($nation, $logo = null, $anime = 0)
    {
        if ($anime > 0) {
            $classAnime = " class='align-middle animate__animated animate__slower animate__infinite animate__pulse' ";
        } else {
            $classAnime = " class='align-middle' ";
        }
        if ($logo) {
            return "<img $classAnime src='../img/" . $logo . "?v=" . NUM_VERSION . "' height='200' width='200' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img $classAnime src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' height='200' width='200' />";
    }

    function ImgNationFull($nation, $logo = null)
    {
        if ($logo) {
            return "<img class='align-top' src='../img/" . $logo . "?v=" . NUM_VERSION . "' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img class='align-top' src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "' />";
    }

    function ImgNationCss($nation, $logo = null)
    {
        if ($logo) {
            return "<img class='img_nation' src='../img/" . $logo . "?v=" . NUM_VERSION . "' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img class='img_nation' src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "'>";
    }

    function ImgNationCss2($nation, $logo = null)
    {
        if ($logo) {
            return "<img class='img_nation2' src='../img/" . $logo . "?v=" . NUM_VERSION . "' />";
        }
        $nation = $this->VerifNation($nation);
        if (strlen($nation) != 3) {
            return '';
        }
        return "<img class='img_nation2' src='../img/Nations/" . $nation . ".png?v=" . NUM_VERSION . "'>";
    }

    function ImgMedal($medal)
    {
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') {
            return '';
        }
        return "<img src='../img/" . $medal . ".png?v=" . NUM_VERSION . "' height='32' width='32' class='medal' />";
    }

    function ImgMedal48($medal)
    {
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') {
            return '';
        }
        return "<img src='../img/" . $medal . ".png?v=" . NUM_VERSION . "' height='48' width='48' class='medal' />";
    }

    function ImgMedal64($medal)
    {
        if ($medal != 'GOLD' && $medal != 'SILVER' && $medal != 'BRONZE') {
            return '';
        }
        return "<img src='../img/" . $medal . ".png?v=' . NUM_VERSION . '' height='64' width='64' class='medal' />";
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
            $referee = substr($referee, 0, strlen($referee) - 5);

        $referee = trim($referee);
        if (substr($referee, -1) != ')')
            return '';

        $nation = substr($referee, -4, 3);
        $referee = substr($referee, 0, strlen($referee) - 6);
        return $nation;
    }

    function CutReferee(&$referee)
    {
        $referee = explode(' (', trim($referee));

        return $referee[0];
    }

    function GetMedal($rank)
    {
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
        if ($tJoueurs[$row]['Numero'] != '0') {
            $line .= $tJoueurs[$row]['Numero'] . ' - ';
        }
        $line .= '</span><span class="name_player">';
        $line .= strtoupper($tJoueurs[$row]['Nom']);
        $line .= ' ';
        $line .= strtoupper(substr($prenom, 0, 1)) . strtolower(substr($prenom, 1));

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

        $idMatch = $this->GetParamInt('match', -1);
        $equipe = $this->GetParam('team', 'A');

        // Chargement Equipe  
        $cmd  = "SELECT c.Libelle, c.Code_club, c.logo 
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
                <div class="logo_sm"></div>
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-center">
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '') . '
                        </span>
                        ' . $this->ImgNation64(utyGetString($rEquipe, 'Code_club', 'FRA'), $rEquipe['logo']) . '&nbsp;
                    </div>
                    <div id="banner_line2" class="h2 text-center">
                        <span>' . $this->Lang('KAYAK-POLO') . '</span>
                    </div>
                <div id="banner_lines">
            ';
        foreach ($tJoueurs as $key => $joueur) {
            if (utyGetString($joueur, 'Capitaine', '') != 'E') {
                if (utyGetString($joueur, 'Capitaine', '') == 'C') {
                    $captain = ' <span class="badge bg-warning capitaine">C</span>';
                } else {
                    $captain = '';
                }
                echo '
                    <div class="banner_line row">
                        <div class="col-md-12 text-start clair numero">
                            <span class="badge bg-primary numero">' . utyGetInt($joueur, 'Numero', '') . '</span>
                            ' . utyTruncateString(utyGetString($joueur, 'Nom', ''), 16)
                    . '&nbsp;'
                    . utyTruncateString(utyGetPrenom($joueur, 'Prenom', ''), 16) . $captain . '
                        </div>
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

        $idMatch = $this->GetParamInt('match', -1);
        $equipe = $this->GetParam('team', 'A');

        // Chargement Equipe  
        $cmd  = "SELECT c.Libelle, c.Code_club, c.logo 
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
                <div class="logo_sm"></div>
                <div id="banner_list">
                    <div id="banner_line1" class="h2 text-center">
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '') . '
                        </span>
                        ' . $this->ImgNation64(utyGetString($rEquipe, 'Code_club', 'FRA'), $rEquipe['logo']) . '&nbsp;
                    </div>
                    <div id="banner_line2" class="h2 text-center">
                        <span>' . $this->Lang('KAYAK-POLO') . '</span>
                    </div>
                <div id="banner_lines">
            ';
        foreach ($tJoueurs as $key => $joueur) {
            if (utyGetString($joueur, 'Capitaine', '') == 'E') {
                echo '
                    <div class="banner_line row">
                        <div class="col-md-12 text-start">
                            <span class="clair">COACH</span>
                            <span>' . utyTruncateString(utyGetString($joueur, 'Nom', ''), 15)
                    . '&nbsp;'
                    . utyTruncateString(utyGetPrenom($joueur, 'Prenom', ''), 15) . '
                        </div>
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
        $cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi, c.Soustitre2, ce.logo 
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
                    <div id="podium_line1">' . $this->ImgNation48($tEquipes[0]['Code_club'], $tEquipes[0]['logo']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[0], 'Libelle', '') . '</span>
                    </div>
                    <div id="podium_line2">' . $this->ImgNation48($tEquipes[1]['Code_club'], $tEquipes[1]['logo']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[1], 'Libelle', '') . '</span>
                    </div>
                    <div id="podium_line3">' . $this->ImgNation48($tEquipes[2]['Code_club'], $tEquipes[2]['logo']) . '&nbsp;
                        <span>' . utyGetString($tEquipes[2], 'Libelle', '') . '</span>
                    </div>
                    <div id="podium_categorie">
                        <span>' . utyGetString($tEquipes[0], 'Soustitre2', '') . '</span>
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
        $cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi ranking, c.Soustitre2, ce.logo 
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
            <div class="container-fluid ban_list ranking">
                <div class="logo_sm"></div>
                <div class="logo2_sm"></div>
                <div id="banner_list" class="final_ranking">
                    <div id="banner_line1" class="h2">
                    ' . $this->Lang('CLASSEMENT FINAL') . '<br>
                        <span class="categorie">
                        ' . ' ' . utyGetString($tEquipes[0], 'Soustitre2', '') . '
                        </span>
                    </div>
                <div id="banner_lines">
            ';
        foreach ($tEquipes as $key => $equipe) {
            echo '
                            <div class="banner_line">
                                <span class="badge bg-primary numero">' . utyGetInt($equipe, 'ranking', 999) . '</span>
                                &nbsp;' . $this->ImgNation48(utyGetString($equipe, 'Code_club', 999), $equipe['logo']) . '&nbsp;
                                <span>' . utyGetString($equipe, 'Libelle', '') . '&nbsp;</span>'
                . $this->ImgMedal48($this->GetMedal(utyGetInt($equipe, 'ranking', 999))) . '
                                    
                            </div>';
        }
        echo '
                    </div>
                </div>
            </div>';
    }

    function Content_Podium()
    {
        $db = new MyBdd();

        $competition = $this->GetParam('competition');
        $saison = $this->GetParam('saison', utyGetSaison());
        $anime = $this->GetParam('anime', 0);

        // Chargement des 3 premières équipes ...
        $cmd  = "SELECT ce.Libelle, ce.Code_club, ce.CltNiveau_publi ranking, 
                c.Soustitre2, ce.logo 
            FROM kp_competition c, kp_competition_equipe ce 
            WHERE ce.Code_compet = '$competition' 
            AND ce.Code_saison = $saison 
            AND c.Code = ce.Code_compet 
            AND c.Code_saison = ce.Code_saison 
            ORDER BY CltNiveau_publi 
            LIMIT 0, 3 ";

        $tEquipes = null;
        $db->LoadTable($cmd, $tEquipes);

        echo '
            <div id="banner_podium" class="podium">
                <div id="podium_title" class="h2 text-center">
                    PODIUM
                    <br>
                    <span class="categorie">
                        ' . utyGetString($tEquipes[0], 'Soustitre2', '') . '
                    </span>
                </div>
                <div id="podium_line1" class="podium_team h2 text-center">
                    ' . $this->ImgNation200(utyGetString($tEquipes[0], 'Code_club', 999), $tEquipes[0]['logo'], $anime) . '<br>
                    <span>' . utyGetString($tEquipes[1], 'Libelle', '') . '</span>
                </div>
                <div id="podium_line2" class="podium_team h2 text-center">
                    ' . $this->ImgNation200(utyGetString($tEquipes[1], 'Code_club', 999), $tEquipes[1]['logo'], $anime) . '<br>
                    <span>' . utyGetString($tEquipes[0], 'Libelle', '') . '</span>
                </div>
                <div id="podium_line3" class="podium_team h2 text-center">
                    ' . $this->ImgNation200(utyGetString($tEquipes[2], 'Code_club', 999), $tEquipes[2]['logo'], $anime) . '<br>
                    <span>' . utyGetString($tEquipes[2], 'Libelle', '') . '</span>
                </div>
            </div>';
    }

    function Content_Player()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
        $equipe = $this->GetParam('team', 'A');
        $numero = $this->GetParam('number', '1');

        // Chargement Joueurs  
        $cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, 
            b.Naissance, b.Numero_comite_dept, ce.logo 
            FROM kp_match_joueur a, kp_licence b, kp_match m, kp_competition_equipe ce
            WHERE a.Id_match = $idMatch
            AND a.Id_match = m.Id
            AND a.Equipe = '$equipe' 
            AND a.Matric = b.matric 
            AND a.Capitaine != 'E' 
            AND a.Numero = $numero
            AND m.Id_equipe$equipe = ce.Id ";

        $rJoueur = null;
        $db->LoadRecord($cmd, $rJoueur);
        $num = '<span class="badge bg-primary numero">' . $numero . '</span> ';

        if (utyGetString($rJoueur, 'Capitaine', '') == 'C') {
            $capitaine = ' <span class="badge bg-warning capitaine">C</span>';
        } else {
            $capitaine = '';
        }

        echo '
        <div class="container-fluid ban_single">
            <div class="logo_xs"></div>
            <div id="banner_single" class="text-center">
                <div class="banner_line">' . $this->ImgNation64($rJoueur['Numero_comite_dept'], $rJoueur['logo']) . '&nbsp;
                    <span>' . $num
            . utyTruncateString(utyGetString($rJoueur, 'Nom', ''), 22)
            . ' '
            . utyTruncateString(utyGetPrenom($rJoueur, 'Prenom', ''), 12)
            . $capitaine . '
                    </span>
                </div>
                <div id="pres_player">
                    <img src="/img/KIP/players/' . utyGetInt($rJoueur, 'Matric', 'none') . '.png?v=' . NUM_VERSION . '" alt="">
                </div>
            </div>
        </div>';
    }

    function Content_Coach()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
        $equipe = $this->GetParam('team', 'A');
        $numero = $this->GetParam('number', '1');

        // Chargement Joueurs  
        $cmd  = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, 
        b.Naissance, b.Numero_comite_dept, ce.logo 
        FROM kp_match_joueur a, kp_licence b, kp_match m, kp_competition_equipe ce
        WHERE a.Id_match = $idMatch
        AND a.Id_match = m.Id
        AND a.Equipe = '$equipe' 
        AND a.Matric = b.matric 
        AND a.Capitaine = 'E' 
        AND a.Numero = $numero
        AND m.Id_equipe$equipe = ce.Id ";

        $rJoueur = null;
        $db->LoadRecord($cmd, $rJoueur);
        $num = '<span class="clair">' . $numero . '</span> ';

        if (utyGetString($rJoueur, 'Capitaine', '') == 'C') {
            $capitaine = ' <span class="badge bg-warning capitaine">C</span>';
        } else if (utyGetString($rJoueur, 'Capitaine', '') == 'E') {
            $capitaine = ' (Coach)';
            $num = '';
        } else {
            $capitaine = '';
        }

        echo '
        <div class="container-fluid ban_single">
            <div class="logo_xs"></div>
            <div id="banner_single" class="text-center">
                <div class="banner_line">' . $this->ImgNation64($rJoueur['Numero_comite_dept'], $rJoueur['logo']) . '&nbsp;
                    <span>' . $num
            . utyGetString($rJoueur, 'Nom', '')
            . ' ' . utyGetPrenom($rJoueur, 'Prenom', '')
            . $capitaine . '
                    </span>
                </div>
            </div>
        </div>';
    }

    function Content_Player_Medal()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
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
        $num = '<span class="badge bg-primary numero">' . $numero . '</span> ';

        if (utyGetString($rJoueur, 'Capitaine', '') == 'C') {
            $capitaine = ' <span class="badge bg-warning capitaine">C</span>';
        } else if (utyGetString($rJoueur, 'Capitaine', '') == 'E') {
            $capitaine = ' (Coach)';
            $num = '';
        } else {
            $capitaine = '';
        }

        echo '
            <div class="container-fluid ban_single">
                <div id="banner_single" class="text-center">
                    <div class="banner_line row">' . $this->ImgNation48($rJoueur['Numero_comite_dept']) . '&nbsp;
                        <span>' . $num
            . utyGetString($rJoueur, 'Nom', '')
            . ' ' . utyGetPrenom($rJoueur, 'Prenom', '')
            . $capitaine . '
                        </span>
                    </div>
                    <div class="banner_line row">' . $this->ImgMedal48($medaille) . '&nbsp;
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
        $idMatch = $this->GetParamInt('match', -1);

        $rMatch = null;
        $sql = "SELECT c.Soustitre2 categorie, j.Phase,
            lc1.Matric matric1, lc1.Numero_comite_dept nation1, lc1.Nom nom_arb1, lc1.Prenom prenom_arb1, 
            lc2.Matric matric2, lc2.Numero_comite_dept nation2, lc2.Nom nom_arb2, lc2.Prenom prenom_arb2 
            FROM kp_match m
            LEFT JOIN kp_journee j ON (m.Id_journee = j.Id)
            LEFT JOIN kp_competition c ON 
                (j.Code_competition = c.Code 
                AND j.Code_saison = c.Code_saison)
            LEFT OUTER JOIN kp_licence lc1 ON (m.Matric_arbitre_principal = lc1.Matric) 
            LEFT OUTER JOIN kp_licence lc2 ON (m.Matric_arbitre_secondaire = lc2.Matric) 
            WHERE m.Id = $idMatch";
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
                <div id="ban_info_2_lines">
                    <div class="logo_sm"></div>
                    <div id="banner_line1" class="h2">' . $this->Lang('ARBITRES') . '</div>
                    <div id="banner_line2" class="h2">
                        <span>' . utyGetString($rMatch, 'categorie', '') . ' - ' . utyGetString($rMatch, 'Phase', '') . '</span>
                    </div>
                    <div class="banner_line3">
                        <div class="banner_line row">
                            <!--<div class="col-md-3 text-end clair">' . $this->ImgNation48($nation1) . ' ' . $nation1 . '</div>-->
                            <div class="col-md-9 text-start">' . $arbitre1 . '</div>
                        </div>
                        <div class="banner_line row"> 
                            <!--<div class="col-md-3 text-end clair">' . $this->ImgNation48($nation2) . ' ' . $nation2 . '</div>-->
                            <div class="col-md-9 text-start">' . $arbitre2 . '</div>
                        </div>
                    </div>
                    <div id="pres_ref2">
                        <img src="/img/KIP/players/' . utyGetInt($rMatch, 'matric2', 'none') . '.png?v=' . NUM_VERSION . '" alt="">
                    </div>
                    <div id="pres_ref1">
                        <img src="/img/KIP/players/' . utyGetInt($rMatch, 'matric1', 'none') . '.png?v=' . NUM_VERSION . '" alt="">
                    </div>
                </div>
            </div>';
    }

    function Content_Match()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);

        // Chargement Match
        $cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, 
            ce1.Code_club ClubA, ce2.Code_club ClubB, ce1.logo logoA, ce2.logo logoB,
            m.Terrain, m.Statut, m.Heure_match, j.Phase, c.Soustitre2 categorie 
            FROM kp_journee j, kp_competition c, kp_match m 
            LEFT OUTER JOIN kp_competition_equipe ce1 ON (ce1.Id = m.Id_equipeA) 
            LEFT OUTER JOIN kp_competition_equipe ce2 ON (ce2.Id = m.Id_equipeB) 
            WHERE m.Id = $idMatch 
            AND m.Id_journee = j.Id 
            AND j.Code_competition = c.Code 
            AND j.Code_saison = c.Code_saison ";

        $rMatch = null;
        $db->LoadRecord($cmd, $rMatch);

        if ($rMatch['Statut'] === 'ATT') {
            $score = '<span class="badge bg-primary"><img src="img/time.png" width="25" style="vertical-align: baseline">&nbsp;' . $rMatch['Heure_match'] . '</span>';
        }

        echo '
            <div class="container-fluid ban_presentation">
                <div class="logo_lg"></div>
                <div id="banner_presentation" class="text-center">
                    <div class="banner_line line1">
                        <span>' . utyGetString($rMatch, 'categorie', '') . '</span>
                        <span>' . utyGetString($rMatch, 'Phase', '') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '') . '</span>
                    </div>
                    <div class="row banner_line line2">
                        <div class="col-md-6 text-end">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '') . '
                            </span>
                            &nbsp;' . $this->ImgNation64($rMatch['ClubA'], $rMatch['logoA']) . '
                        </div>
                        <div class="col-md-6 text-start">
                            ' . $this->ImgNation64($rMatch['ClubB'], $rMatch['logoB']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '') . '
                            </span>
                        </div>
                    </div>
                </div>
            </div>';
    }

    function Content_Match2()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);

        // Chargement Match
        $cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, 
            ce1.Code_club ClubA, ce2.Code_club ClubB, ce1.logo logoA, ce2.logo logoB,
            m.Terrain, m.Statut, m.Heure_match, m.ScoreDetailA, m.ScoreDetailB, 
            j.Phase, c.Soustitre2 categorie 
            FROM kp_journee j, kp_competition c, kp_match m 
            LEFT OUTER JOIN kp_competition_equipe ce1 ON (ce1.Id = m.Id_equipeA) 
            LEFT OUTER JOIN kp_competition_equipe ce2 ON (ce2.Id = m.Id_equipeB) 
            WHERE m.Id = $idMatch 
            AND m.Id_journee = j.Id 
            AND j.Code_competition = c.Code 
            AND j.Code_saison = c.Code_saison ";

        $rMatch = null;
        $db->LoadRecord($cmd, $rMatch);

        // if ($rMatch['Statut'] === 'ATT') {
        //     $score = '<span class="badge bg-primary"><img src="img/time.png" width="25" style="vertical-align: baseline">&nbsp;' . $rMatch['Heure_match'] . '</span>';
        // }
        if ($rMatch['Statut'] === 'ATT') {
            // $score = '<div class="badge_score"><span><img src="../img/time_white.png" style="vertical-align: baseline">&nbsp;' . $rMatch['Heure_match'] . '</span></div>';
            $score = '<div class="badge_score"><span>' . $rMatch['Heure_match'] . '</span></div>';
        } else {
            $score = '<div class="badge_score"><span>' . $rMatch['ScoreDetailA'] . '</span>
                             -
                            <span>' . $rMatch['ScoreDetailB'] . '</span></div>';
        }

        echo '
            <div class="container-fluid ban_presentation_color">
                <div class="logo_lg"></div>
                <div class="logo2_lg"></div>
                <div class="boat_a"><img src="../img/KIP/boats/' . $rMatch['ClubA'] . '-boat.png?v=' . NUM_VERSION . '" alt=""></div>
                <div class="boat_b"><img src="../img/KIP/boats/' . $rMatch['ClubB'] . '-boat.png?v=' . NUM_VERSION . '" alt=""></div>
                <div class="vest_a"><img src="../img/KIP/vests/' . $rMatch['ClubA'] . '-vest.png?v=' . NUM_VERSION . '" alt=""></div>
                <div class="vest_b"><img src="../img/KIP/vests/' . $rMatch['ClubB'] . '-vest.png?v=' . NUM_VERSION . '" alt=""></div>
                <div class="helmet_a"><img src="../img/KIP/helmets/' . $rMatch['ClubA'] . '-helmet.png?v=' . NUM_VERSION . '" alt=""></div>
                <div class="helmet_b"><img src="../img/KIP/helmets/' . $rMatch['ClubB'] . '-helmet.png?v=' . NUM_VERSION . '" alt=""></div>
                <div id="banner_presentation" class="text-center">
                    <div class="row banner_line line2">
                        <div class="col-md-5 text-end">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '') . '
                            </span>
                            &nbsp;' . $this->ImgNation64($rMatch['ClubA'], $rMatch['logoA']) . '
                        </div>
                        <div class="col-md-2"></div>
                        ' . $score . '
                        <div class="col-md-5 text-start">
                            ' . $this->ImgNation64($rMatch['ClubB'], $rMatch['logoB']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '') . '
                            </span>
                        </div>
                    </div>
                    <div class="banner_line line1">
                        <span>' . utyGetString($rMatch, 'categorie', '') . '</span>
                        <span>' . utyGetString($rMatch, 'Phase', '') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '') . '</span>
                    </div>
                </div>
            </div>';
    }

    function Content_Match_Score()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
        $anime = $this->GetParamInt('anime', 0);
        if ($anime > 0) {
            $classAnime = " class='container-fluid ban_info_1_lines animate__animated animate__slower animate__infinite animate__pulse' ";
        } else {
            $classAnime = " class='container-fluid ban_info_1_lines' ";
        }

        // Chargement Match
        $cmd  = "SELECT ce1.Libelle LibelleA, ce2.Libelle LibelleB, 
            ce1.Code_club ClubA, ce2.Code_club ClubB, 
            m.Terrain, m.ScoreDetailA, m.ScoreDetailB, m.Statut, m.Heure_match,
            j.Phase, c.Soustitre2 categorie, ce1.logo logoA, ce2.logo logoB
            FROM kp_journee j, kp_competition c, kp_match m 
            LEFT OUTER JOIN kp_competition_equipe ce1 ON (ce1.Id = m.Id_equipeA) 
            LEFT OUTER JOIN kp_competition_equipe ce2 ON (ce2.Id = m.Id_equipeB) 
            WHERE m.Id = $idMatch 
            AND m.Id_journee = j.Id 
            AND j.Code_competition = c.Code 
            AND j.Code_saison = c.Code_saison ";

        $rMatch = null;
        $db->LoadRecord($cmd, $rMatch);

        if ($rMatch['Statut'] === 'ATT') {
            $score = '<span><img src="../img/time_white.png" style="vertical-align: baseline">&nbsp;' . $rMatch['Heure_match'] . '</span>';
        } else {
            $score = '<span>' . $rMatch['ScoreDetailA'] . '</span>
                             -
                            <span>' . $rMatch['ScoreDetailB'] . '</span>';
        }

        echo '
            <div ' . $classAnime . '>
                <div id="ban_info_1_lines" class="text-center">
                    <div class="logo_sm"></div>
                    <div class="logo2_sm"></div>
                    <div id="banner_line1" class="h2 text-end">
                        ' . utyGetString($rMatch, 'categorie', '') . '
                    </div>
                    <div id="banner_line2" class="h2 text-end">
                        ' . utyGetString($rMatch, 'Phase', '') . '
                         - Pitch
                        ' . utyGetString($rMatch, 'Terrain', '') . '
                    </div>

                    <div class="row banner_line">
                        <div class="col-md-5 text-center" id="nation1">
                            ' . $this->ImgNation80($rMatch['ClubA'], $rMatch['logoA']) . '&nbsp;
                            <span>
                            ' . utyGetString($rMatch, 'LibelleA', '') . '
                            </span>
                        </div>
                        <div class="col-md-2 text-center score">
                            ' . $score . '
                        </div>
                        <div class="col-md-5 text-center" id="nation2">
                            <span>
                            ' . utyGetString($rMatch, 'LibelleB', '') . '
                            </span>
                            &nbsp;' . $this->ImgNation80($rMatch['ClubB'], $rMatch['logoB']) . '
                        </div>
                    </div>
                </div>
            </div>';
    }

    function Content_Team()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
        $equipe = $this->GetParam('team', 'A');

        // Chargement Equipe  
        $cmd  = "SELECT c.Libelle, c.Code_club, c.logo 
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
                        ' . $this->ImgNation64($rEquipe['Code_club'], $rEquipe['logo']) . '&nbsp;
                        <span>
                            ' . ' ' . utyGetString($rEquipe, 'Libelle', '') . '
                        </span>
                    </div>
                </div>
            </div>';
    }

    function Content_Team_Medal()
    {
        $db = new MyBdd();

        $idMatch = $this->GetParamInt('match', -1);
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
                    <div class="banner_line row">' . $this->ImgNation48($rEquipe['Code_club']) . '&nbsp;
                        <span>
                        ' . ' ' . utyGetString($rEquipe, 'Libelle', '') . '
                        </span>
                    </div>
                    <div class="banner_line row">' . $this->ImgMedal48($medaille) . '&nbsp;
                        <span>
                        ' . $this->LabelMedal($medaille) . '
                        </span>
                    </div>
                </div>
            </div>';
    }

    function Content_Voie()
    {
        $voie = $this->GetParamInt('voie', 0);
        echo '
            <div class="container-fluid nuage">
                <div class="voie">
                    <button type="button" class="btn btn-light btn-lg">' . $voie . '</button>
                </div>
            </div>';
    }

    function Content_Logo()
    {
        $voie = $this->GetParamInt('voie', 0);
        echo '
            <div class="container-fluid logo_pleine_page">
            </div>';
    }

    function Content_Player_Pictures()
    {
        echo '
            <div class="container-fluid">';

        $i = 0;
        $dir    = '../img/KIP/players';
        $files = scandir($dir);
        foreach ($files as $file) {
            $i++;
            echo '<img src="../img/KIP/players/' . $file . '?v=' . NUM_VERSION . '" alt="" height="60px">';
        }
        echo '<span class="badge bg-dark">' . $i . '</span>';
        echo '</div>';
    }

    function Content_Empty()
    {
        echo '
            <div class="container-fluid">
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
            <?php for ($i = 1; $i <= 8; $i++) { ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><input type="text" style="width:1024px" name="scenario_url<?php echo $i; ?>" id="scenario_url<?php echo $i; ?>"></td>
                    <td><input type="text" style="width:64px" name="scenario_duree<?php echo $i; ?>" id="scenario_duree<?php echo $i; ?>"></td>
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
            case 'logo':
                $this->Content_Logo();
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
            case 'match2':
                $this->Content_Match2();
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
            case 'podium':
                $this->Content_Podium();
                return;
                break;
            case 'player_pictures':
                $this->Content_Player_Pictures();
                return;
                break;
            case 'empty':
            default:
                $this->Content_Empty();
                return;
                break;
        }
    }

    function Script()
    {
    ?>
        <script type="text/javascript" src="../js/axios/axios.min.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript" src="../lib/bootstrap-5.1.3-dist/js/bootstrap.min.js?v=<?= NUM_VERSION ?>"></script>
        <?php
        $voie = $this->GetParamInt('voie', 0);
        $intervalle = $this->GetParamInt('intervalle', 3000);
        $show = $this->GetParam('show');
        if ($show == 'command') {
            $voie = 0;
        }
        ?>
        <script type="text/javascript" src="./js/voie_ax.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript" src="./js/tv.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                Init(<?= $voie; ?>, <?= $intervalle; ?>);
                document.title = 'KPI TV (' + <?= $voie; ?> + ')';
            }, false)
        </script>
<?php
    }
}

new TV($_GET);
