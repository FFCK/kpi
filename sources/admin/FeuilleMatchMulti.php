<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

require_once('../qrcode/qrcode.class.php');

// Gestion de la Feuille de Match
class PDF extends FPDF {
    var $x0;
}

class FeuilleMatch extends MyPage {

    function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd) {
        $sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
        $result = mysql_query($sql, $bdd->m_link) or die("Erreur Select");

        if (mysql_num_rows($result) != 1) {
            return;
        }

        $row = mysql_fetch_array($result);
        if ((int) $row['Nb'] > 0) {
            return;
        }

        $sql = "Replace Into gickp_Matchs_Joueurs ";
        $sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
        $sql .= "Where Id_equipe = $idEquipe ";
        $sql .= "AND Capitaine <> 'X' ";
        $sql .= "AND Capitaine <> 'A' ";
        mysql_query($sql, $bdd->m_link) or die("Erreur Replace InitTitulaireEquipe");
    }

    function FeuilleMatch() {
        MyPage::MyPage();

        $listMatch = utyGetGet('listMatch', -1);
        if($listMatch == -1 || $listMatch == '') {
            die('Aucun match à afficher !');
        }
        $chaqueMatch = explode(',', $listMatch);
        
        $myBdd = new MyBdd();

        //Création du PDF de base
        $pdf = new PDF('L');
        $pdf->Open();
        $pdf->SetTitle("Feuille de Marque");

        $pdf->SetAuthor("FFCK - Kayak-polo.info");
        $pdf->SetCreator("FFCK - Kayak-polo.info avec FPDF");

        for ($h = 0; $h < count($chaqueMatch); $h++) {
            // Infos match

            $sql = "Select a.Id, a.Numero_ordre, a.Date_match, a.Heure_match, a.Heure_fin, "
                    . "a.Libelle Intitule, a.Terrain, a.Secretaire, a.Chronometre, a.Timeshoot, a.Type, "
                    . "a.Id_equipeA, a.Id_equipeB, a.Arbitre_principal, a.Arbitre_secondaire, a.ScoreA, "
                    . "a.ScoreB, a.ColorA, a.ColorB, a.Commentaires_officiels, "
                    . "b.Nom, b.Phase, b.Libelle, b.Lieu, b.Departement, b.Organisateur, b.Responsable_R1, "
                    . "b.Responsable_insc, b.Delegue, b.ChefArbitre, b.Code_competition, b.Code_saison "
                    . "From gickp_Matchs a, gickp_Journees b "
                    . "Where a.Id in (" . $chaqueMatch[$h] . ") "
                    . "And a.Id_journee = b.Id ";
            $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Select <br />" . $sql);
            $num_results = mysql_num_rows($result);
            if ($num_results != 1) {
                die('Erreur Nb Matchs');
            }

            $row = mysql_fetch_array($result);
            $idMatch = $row['Id'];
            $saison = $row['Code_saison'];
            $categorie = $row['Code_competition'];
            $heure_fin = substr($row['Heure_fin'], -5);
            if ($heure_fin == '00:00') {
                $heure_fin = '';
            }

            // Données compétition
            $arrayCompetition = $myBdd->GetCompetition($categorie, $saison);
            
            $visuels = utyGetVisuels($arrayCompetition, TRUE);
            
            $idEquipeA = $row['Id_equipeA'];
            $idEquipeB = $row['Id_equipeB'];
            if ($idEquipeA == '') {
                $idEquipeA = 0;
            }
            if ($idEquipeB == '') {
                $idEquipeB = 0;
            }

            // drapeaux
            if ($arrayCompetition['Code_niveau'] == 'INT' && $idEquipeA != 0) {
                $paysA = substr($myBdd->GetCodeClubEquipe($idEquipeA), 0, 3);
                if (is_numeric($paysA[0]) || is_numeric($paysA[1]) || is_numeric($paysA[2])) {
                    $paysA = 'FRA';
                }
            } else {
                $paysA = '';
            }
            if ($arrayCompetition['Code_niveau'] == 'INT' && $idEquipeB != 0) {
                $paysB = substr($myBdd->GetCodeClubEquipe($idEquipeB), 0, 3);
                if (is_numeric($paysB[0]) || is_numeric($paysB[1]) || is_numeric($paysB[2])) {
                    $paysB = 'FRA';
                }
            } else {
                $paysB = '';
            }

            // Langue
            $getlang = utyGetGet('lang');
            $langue = parse_ini_file("../commun/MyLang.ini", true);
            if ($getlang == 'en') {
                $arrayCompetition['En_actif'] = 'O';
            } elseif ($getlang == 'fr') {
                $arrayCompetition['En_actif'] = '';
            }

            if ($arrayCompetition['En_actif'] == 'O') {
                $lang = $langue['en'];
            } else {
                $lang = $langue['fr'];
            }

            $competition = html_entity_decode($row['Nom']);
            $lieu = html_entity_decode($row['Lieu']);
            $dpt = $row['Departement'];
            $terrain = $row['Terrain'];

            $rep1 = array(" (Pool Arbitres 1)", " (Pool Arbitres 2)", " INT-A", " INT-B", " INT-C", " INT-S", " INT", " NAT-A", " NAT-B", " NAT-C", " NAT-S", " NAT", " REG-S", "REG", " OTM", " JO");
            $rep2 = array("[", "]");
            $rep3 = array("V", "P", "1er", "2e", "e");
            $rep4 = array("W", "L", "1st", "2nd", "th");

            $intitule = $row['Intitule'];
            $intitule2 = str_replace($rep2, '', $intitule);
            if ($arrayCompetition['En_actif'] == 'O') {
                $intitule2 = str_replace($rep3, $rep4, $intitule2);
            }

            // Nom Equipe A
            $equipea = '';
            $equipeaFormat = '';
            $sql1 = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeA";
            $result1 = mysql_query($sql1, $myBdd->m_link) or die("Erreur Load");
            if (mysql_num_rows($result1) == 1) {
                $row1 = mysql_fetch_array($result1);
                $equipea = $row1['Libelle'];
            }

            // Nom Equipe B
            $equipeb = '';
            $equipebFormat = '';
            $sql2 = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeB";
            $result2 = mysql_query($sql2, $myBdd->m_link) or die("Erreur Load");
            if (mysql_num_rows($result2) == 1) {
                $row2 = mysql_fetch_array($result2);
                $equipeb = $row2['Libelle'];
            }

            //Affect Auto
            if ($intitule != '') {
                if ($arrayCompetition['En_actif'] == 'O') {
                    $EquipesAffectAuto = utyEquipesAffectAuto($intitule);
                } else {
                    $EquipesAffectAuto = utyEquipesAffectAutoFR($intitule);
                }
            }
            if (($equipea == '') && isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '') {
                $equipea = $EquipesAffectAuto[0];
                $equipeaFormat = 'Auto';
            }
            if ($equipeb == '' && isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '') {
                $equipeb = $EquipesAffectAuto[1];
                $equipebFormat = 'Auto';
            }
            $arbsup = array(" (Pool Arbitres 1)", " (Pool Arbitres 2)", " INT-A", " INT-B", " INT-C", " INT-S", " INT", " NAT-A", " NAT-B", " NAT-C", " NAT-S", " NAT", " REG-S", "REG", " OTM", " JO");
            if ($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1') {
                $row['Arbitre_principal'] = str_replace($arbsup, '', $row['Arbitre_principal']);
            } elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '') {
                $row['Arbitre_principal'] = $EquipesAffectAuto[2];
            }
            if ($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1') {
                $row['Arbitre_secondaire'] = str_replace($arbsup, '', $row['Arbitre_secondaire']);
            } elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '') {
                $row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
            }
            //

            $principal = $row['Arbitre_principal'];
            if ($principal == '-1') {
                $principal = '';
            }
            $principal = str_replace($rep1, '', $principal);

            $secondaire = $row['Arbitre_secondaire'];
            if ($secondaire == '-1') {
                $secondaire = '';
            }
            $secondaire = str_replace($rep1, '', $secondaire);

            $organisateur = html_entity_decode($row['Organisateur']);
            if ($row['Responsable_R1'] || $arrayCompetition['En_actif'] == 'O') {
                $responsable = substr(html_entity_decode($row['Responsable_R1']), 0, 25);
                $responsableT = $lang['R1'] . ': ';
            } else {
                $responsable = substr(html_entity_decode($row['Responsable_insc']), 0, 25);
                $responsableT = 'Resp: ';
            }
            if ($arrayCompetition['En_actif'] == 'O') {
                $delegue = html_entity_decode($row['Delegue']);
                $delegueT = $lang['Delegue'] . ': ';
            } elseif ($row['Delegue']) {
                $delegue = html_entity_decode($row['Delegue']);
                $delegueT = 'Délégué CNA: ';
            } elseif ($row['ChefArbitre']) {
                $delegue = html_entity_decode($row['ChefArbitre']);
                $delegueT = 'Chef des arbitres: ';
            } else {
                $delegue = '';
                $delegueT = 'Délégué      : ';
            }
            $secretaire = $row['Secretaire'];
            $chronometre = $row['Chronometre'];
            $timeshoot = $row['Timeshoot'];
            $phase = $row['Phase'];
            if ($arrayCompetition['En_actif'] == 'O') {
                $date = $row['Date_match'];
                $dateprint = date('Y-m-d');
            } else {
                $date = utyDateUsToFr($row['Date_match']);
                $dateprint = date('d/m/Y');
            }
            $heure = $row['Heure_match'];
            $no = $row['Numero_ordre'];
            $colorA = $row['ColorA'];
            $colorB = $row['ColorB'];
            if ($row['ScoreA'] != '?' && $row['ScoreA'] != '') {
                $ScoreA = $row['ScoreA'];
            } else {
                $ScoreA = '';
            }

            if ($row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                $ScoreB = $row['ScoreB'];
            } else {
                $ScoreB = '';
            }

            $Commentaires = $row['Commentaires_officiels'];
            $Commentaires1 = str_split($Commentaires, 85); //85
            $Commentaires1 = $Commentaires1[0];
            if (strlen($Commentaires) > 85) {
                $Commentaires1 .= '...';
            }

            // Info Equipe A
            for ($i = 1; $i <= 10; $i++) {
                $na[$i] = '';
                $noma[$i] = '';
                $prenoma[$i] = '';
                $licencea[$i] = '';
                $saisona[$i] = '';
                $diva[$i] = '';
            }

            if ($row['Id_equipeA'] >= 1) {
                $this->InitTitulaireEquipe('A', $idMatch, $idEquipeA, $myBdd);
            }

            $sql3 = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, "
                    . "b.Origine, b.Reserve icf, c.Matric Matric_titulaire, "
                    . "IF (a.Capitaine='E', 1, 0) flagEntraineur "
                    . "FROM gickp_Matchs_Joueurs a "
                    . "LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs c ON (c.Id_equipe = $idEquipeA AND c.Matric = a.Matric), "
                    . "gickp_Liste_Coureur b "
                    . "WHERE a.Matric = b.Matric "
                    . "AND a.Id_match = $idMatch "
                    . "AND a.Capitaine <> 'A' "
                    . "AND a.Equipe = 'A' "
                    . "ORDER BY flagEntraineur, Numero, Nom, Prenom ";
            $result3 = mysql_query($sql3, $myBdd->m_link) or die("Erreur Load 1 : " . $sql3);
            $num_results3 = mysql_num_rows($result3);

            $j = 0;
            for ($i = 1; $i <= $num_results3; $i++) {
                $j++;
                $row3 = mysql_fetch_array($result3);
                if ($row3["Capitaine"] == 'E' && $j <= 10) {
//                                    $j=10;
                    $noma[$j] = strtoupper($row3['Nom']) . ' (' . $lang['Entraineur'] . ')';
                    $na[$j] = 'E';
                } elseif ($row3["Capitaine"] == 'C') {
                    $noma[$j] = strtoupper($row3['Nom']) . ' (Cap)';
                    $na[$j] = $row3['Numero'];
                } elseif ($row3["Capitaine"] != 'E') {
                    $noma[$j] = strtoupper($row3['Nom']);
                    $na[$j] = $row3['Numero'];
                }

                $prenoma[$j] = $row3['Prenom'];
                if ($row3['Matric'] > 2000000 && $row3['icf'] != NULL) {
                    $licencea[$j] = 'Icf-' . $row3['icf'];
                } elseif ($row3['Matric'] < 2000000) {
                    $licencea[$j] = $row3['Matric'];
                } else {
                    $licencea[$j] = '';
                }

                if ($row3['Nom'] != '' && $row3['Matric'] < 2000000 && $row3['Origine'] != '' && $row3['Origine'] < $saison) {
                    $saisona[$j] = ' (' . $row3['Origine'] . ')';
                }

                if ($row3['Matric_titulaire'] != $row3['Matric']) {
                    $diva[$j] = utyCodeCategorie2($row3['Naissance']) . '(sup)';
                } else {
                    $diva[$j] = utyCodeCategorie2($row3['Naissance']);
                }
            }
            // Info Equipe B
            for ($i = 1; $i <= 10; $i++) {
                $nb[$i] = '';
                $nomb[$i] = '';
                $prenomb[$i] = '';
                $licenceb[$i] = '';
                $saisonb[$i] = '';
                $divb[$i] = '';
            }

            if ($row['Id_equipeB'] >= 1) {
                $this->InitTitulaireEquipe('B', $idMatch, $idEquipeB, $myBdd);
            }

            $sql4 = "SELECT a.Matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance, "
                    . "b.Origine, b.Reserve icf, c.Matric Matric_titulaire, "
                    . "IF (a.Capitaine='E', 1, 0) flagEntraineur "
                    . "FROM gickp_Matchs_Joueurs a "
                    . "LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = $idEquipeB And c.Matric = a.Matric), "
                    . "gickp_Liste_Coureur b "
                    . "WHERE a.Matric = b.Matric "
                    . "AND a.Id_match = $idMatch "
                    . "AND a.Capitaine <> 'A' "
                    . "AND a.Equipe = 'B' "
                    . "ORDER BY flagEntraineur, Numero, Nom, Prenom ";
            $result4 = mysql_query($sql4, $myBdd->m_link) or die("Erreur Load 1 : " . $sql4);
            $num_results4 = mysql_num_rows($result4);

            $j = 0;
            for ($i = 1; $i <= $num_results4; $i++) {
                $j++;
                $row4 = mysql_fetch_array($result4);

                if ($row4["Capitaine"] == 'E' && $j <= 10) {
//                                    $j=10;
                    $nomb[$j] = strtoupper($row4['Nom']) . ' (' . $lang['Entraineur'] . ')';
                    $nb[$j] = 'E';
                } elseif ($row4["Capitaine"] == 'C') {
                    $nomb[$j] = strtoupper($row4['Nom']) . ' (Cap)';
                    $nb[$j] = $row4['Numero'];
                } elseif ($row4["Capitaine"] != 'E') {
                    $nomb[$j] = strtoupper($row4['Nom']);
                    $nb[$j] = $row4['Numero'];
                }

                $prenomb[$j] = $row4['Prenom'];
                if ($row4['Matric'] > 2000000 && $row4['icf'] != NULL) {
                    $licenceb[$j] = 'Icf-' . $row4['icf'];
                } elseif ($row4['Matric'] < 2000000) {
                    $licenceb[$j] = $row4['Matric'];
                } else {
                    $licenceb[$j] = '';
                }
                if ($row4['Nom'] != '' && $row4['Matric'] < 2000000 && $row4['Origine'] != '' && $row4['Origine'] < $saison) {
                    $saisonb[$j] = ' (' . $row4['Origine'] . ')';
                }

                if ($row4['Matric_titulaire'] != $row4['Matric']) {
                    $divb[$j] = utyCodeCategorie2($row4['Naissance']) . '(sup)';
                } else {
                    $divb[$j] = utyCodeCategorie2($row4['Naissance']);
                }
            }
            //Détail Match
            $detail = array();
            $detail2 = array();
            $scoreDetailA = 0;
            $scoreDetailB = 0;

            $sql5 = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.Competiteur, d.Numero, d.Equipe_A_B, ";
            $sql5 .= "c.Nom, c.Prenom ";
            $sql5 .= "From gickp_Matchs_Detail d Left Outer Join gickp_Liste_Coureur c On d.Competiteur = c.Matric ";
            $sql5 .= "Where d.Id_match = $idMatch ";
            $sql5 .= "AND d.Id_evt_match != 'T' ";
            $sql5 .= "AND d.Id_evt_match != 'A' ";
            $sql5 .= "Order By d.Periode ASC, d.Temps DESC, d.Id ";

            $result5 = mysql_query($sql5, $myBdd->m_link) or die("Erreur Load " . $sql5);
            $num_results5 = mysql_num_rows($result5);

            $scoreMitempsA = '';
            $scoreMitempsB = '';
            $nblignes = 0;

            for ($i = 1; $i <= $num_results5; $i++) {
                $row5 = mysql_fetch_array($result5);
                for ($j = 1; $j <= 11; $j++) {
                    $d[$j] = '';
                }
                if ($row5['Id']) {
                    if ($row5['Equipe_A_B'] == 'A') {
                        if ($row5['Nom'] != '') {
                            $d[1] = $row5['Numero'] . '-' . ucwords(strtolower($row5['Nom'])) . ' ' . $row5['Prenom']{0} . '.';
                        } else {
                            $d[1] = $lang['EQUIPE'] . ' A';
                        }
                        switch ($row5['Id_evt_match']) {
                            case 'B':
                                $d[5] = 'X';
                                $scoreDetailA++;
                                if ($row5['Periode'] == 'M1') {
                                    $scoreMitempsA ++;
                                }
                                break;
                            case 'V':
                                $d[2] = 'X';
                                break;
                            case 'J':
                                $d[3] = 'X';
                                break;
                            case 'R':
                                $d[4] = 'X';
                                break;
                        }
                    } else {
                        if ($row5['Nom'] != '') {
                            $d[11] = $row5['Numero'] . '-' . ucwords(strtolower($row5['Nom'])) . ' ' . $row5['Prenom']{0} . '.';
                        } else {
                            $d[11] = $lang['EQUIPE'] . ' B';
                        }
                        switch ($row5['Id_evt_match']) {
                            case 'B':
                                $d[7] = 'X';
                                $scoreDetailB++;
                                if ($row5['Periode'] == 'M1') {
                                    $scoreMitempsB ++;
                                }
                                break;
                            case 'V':
                                $d[8] = 'X';
                                break;
                            case 'J':
                                $d[9] = 'X';
                                break;
                            case 'R':
                                $d[10] = 'X';
                                break;
                        }
                    }
                    $d[6] = $row5['Periode'] . ' - ';
                    if (strftime("%M:%S", strtotime($row5['Temps'])) != '00:00') {
                        $d[6] .= strftime("%M:%S", strtotime($row5['Temps']));
                    }
                }
                if ($i <= 26) {
                    array_push($detail, array('d1' => $d[1], 'd2' => $d[2], 'd3' => $d[3], 'd4' => $d[4], 'd5' => $d[5], 'd6' => $d[6],
                        'd7' => $d[7], 'd8' => $d[8], 'd9' => $d[9], 'd10' => $d[10], 'd11' => $d[11]));
                } else {
                    array_push($detail2, array('d1' => $d[1], 'd2' => $d[2], 'd3' => $d[3], 'd4' => $d[4], 'd5' => $d[5], 'd6' => $d[6],
                        'd7' => $d[7], 'd8' => $d[8], 'd9' => $d[9], 'd10' => $d[10], 'd11' => $d[11]));
                    $nblignes = $i;
                }
            }

            if (($scoreDetailA != $ScoreA or $scoreDetailB != $ScoreB) && ($scoreDetailA != '' or $scoreDetailB != '')) {
                $typeScore = $lang['Provisoire'];
            } else {
                $typeScore = $lang['Final'];
            }

            if ($scoreMitempsA != '' && $scoreMitempsB == '') {
                $scoreMitempsB = 0;
            }
            if ($scoreMitempsB != '' && $scoreMitempsA == '') {
                $scoreMitempsA = 0;
            }



            // Production de la feuille de match PDF suivante
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 1);

            //variables :
            $pdf->SetSubject("Match " . $equipea . "/" . $equipeb);
            $pdf->SetKeywords("kayak-polo, canoe-polo, match, canoe, kayak, " . $equipea . ", " . $equipeb);

            //Colonne 1
            $x0 = 10;
            $pdf->SetLeftMargin($x0);
            $pdf->SetX($x0);
            $pdf->SetY(9);

            // Bandeau
            if($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])){
                $img = redimImage($visuels['bandeau'], 153, 10, 11, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
            } elseif($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                $pdf->Image('../img/logoKPI-small.jpg', 10, 10, 0, 11, 'jpg', "https://www.kayak-polo.info");
                $img = redimImage($visuels['logo'], 153, 10, 11, 'R');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
            } elseif($arrayCompetition['Kpi_ffck_actif'] == 'O') {
                $pdf->Image('../img/logoKPI-small.jpg', 65, 10, 0, 11, 'jpg', "https://www.kayak-polo.info");
            // Logo
            } elseif($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])){
                $img = redimImage($visuels['logo'], 153, 10, 11, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }
            // Sponsor
            if($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])){
                $img = redimImage($visuels['sponsor'], 297, 10, 11, 'C');
                $pdf->Image($img['image'], $img['positionX'], 190, 0, $img['newHauteur']);
            }
            
//            // logo
//            if ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
//                $pdf->Image('../img/logoKPI-small.jpg', 65, 10, 0, 11, 'jpg', "https://www.kayak-polo.info");
//            }
//            if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($bandeau)) {
//                $size = getimagesize($bandeau);
//                $largeur = $size[0];
//                $hauteur = $size[1];
//                $ratio = 11 / $hauteur;
//                $newlargeur = $largeur * $ratio;
//                $posi = 77 - ($newlargeur / 2);
//                $pdf->image($bandeau, $posi, 8, 0, 11);
//            } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($logo)) {
//                $size = getimagesize($logo);
//                $largeur = $size[0];
//                $hauteur = $size[1];
//                $ratio = 11 / $hauteur;
//                $newlargeur = $largeur * $ratio;
//                $posi = 77 - ($newlargeur / 2);
//                $pdf->image($logo, $posi, 8, 0, 11);
//            }
//
//            if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor)) {
//                $size = getimagesize($sponsor);
//                $largeur = $size[0];
//                $hauteur = $size[1];
//                $ratio = 11 / $hauteur; //hauteur imposée de 11mm
//                $newlargeur = $largeur * $ratio;
//                $posi = 150 - ($newlargeur / 2); //210mm = largeur de page
//                $pdf->image($sponsor, $posi, 190, 0, 11);
//            }

            $pdf->Ln(11);

            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 14);
//    		$pdf->Cell(135,6,$lang['FEUILLE_DE_MARQUE'],'B','1','C');
            $pdf->Cell(135, 2, '', 'B', '1', 'C');

            $pdf->SetFont('Arial', 'I', 7);
//			$pdf->Cell(135,4,$lang['A_remplir'],'LR','1','C');
            $pdf->Cell(135, 1, "", 'LR', '1', 'C');

            $pdf->SetFont('Arial', '', 10);
            if ($arrayCompetition['Titre_actif'] == 'O') {
                $pdf->Cell(111, 4, $competition, 'L', '0', 'L');
            } else {
                $pdf->Cell(111, 4, $arrayCompetition['Soustitre'], 'L', '0', 'L');
            }
            if ($arrayCompetition['Soustitre2'] != '') {
                $pdf->Cell(24, 4, $arrayCompetition['Soustitre2'], 'R', '1', 'R');
            } else {
                $pdf->Cell(24, 4, $categorie, 'R', '1', 'R');
            }

            $pdf->Cell(111, 4, $lang['Organisateur'] . ": " . ucwords(strtolower($organisateur)), 'L', '0', 'L');
            $pdf->Cell(24, 4, $lang['Saison'] . ": " . $saison, 'R', '1', 'L');

            $pdf->Cell(68, 4, $responsableT . ucwords(strtolower($responsable)), 'L', '0', 'L');
            $pdf->Cell(67, 4, $delegueT . ucwords(strtolower($delegue)), 'R', '1', 'L');

            $pdf->Cell(135, 1, "", 'LR', '1', 'C');
            $pdf->Cell(135, 1, "", 'LTR', '1', 'C', 1);
            $pdf->Cell(60, 4, $lang['Lieu'] . ": " . $lieu . " (" . $dpt . ")", 'L', '0', 'L', 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 4, $date . "   " . $heure, '0', '0', 'L', 1);
            $pdf->Cell(35, 4, $lang['Terrain'] . ": " . $terrain, 'R', '1', 'R', 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(60, 4, $lang['Phase'] . ": " . $phase, 'L', '0', 'L', 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(30, 4, $lang['Match_no'] . $no, '0', '0', 'L', 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(45, 4, $intitule2, 'R', '1', 'R', 1);

            $pdf->Cell(135, 1, "", 'LR', '1', 'C', 1);
            $pdf->Cell(135, 1, "", 'LTR', '1', 'L');
            $pdf->Cell(135, 4, $lang['Arbitre_1'] . ": " . $principal, 'LR', 1, 'L');
            $pdf->Cell(135, 4, $lang['Arbitre_2'] . ": " . $secondaire, 'LR', 1, 'L');
            $pdf->Cell(135, 4, $lang['Secretaire'] . ": " . $secretaire, 'LR', 1, 'L');
            $pdf->Cell(135, 4, $lang['Chronometre'] . ": " . $chronometre, 'LR', 1, 'L');
            $pdf->Cell(135, 4, $lang['Time_shoot2'] . ": " . $timeshoot, 'LR', 1, 'L');
            $pdf->Cell(135, 1, "", 'LBR', '1', 'C');

            //Equipe A

            $pdf->Ln(1);

            $pdf->Cell(45, 5, $lang['Equipe_A'] . ":", 'LTB', '0', 'C', 1);
            if ($equipeaFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 9);
                $pdf->Cell(90, 5, $equipea, 'TRB', '1', 'L', 1);
            } else {
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(90, 5, $equipea, 'TRB', '1', 'C', 1);
            }
            $pdf->SetFont('Arial', '', 10);

            $pdf->Cell(6, 6, $lang['Num'], '1', '0', 'C');
            $pdf->Cell(45, 6, $lang['Nom'], '1', '0', 'C');
            $pdf->Cell(45, 6, $lang['Prenom'], '1', '0', 'C');
            $pdf->Cell(24, 6, "Licence", '1', '0', 'C');
            $pdf->Cell(15, 6, "Cat.", '1', '1', 'C');

            for ($i = 1; $i <= 10; $i++) {
                if ($na[$i] == 'E') {
                    $pdf->SetFillColor(235, 235, 190);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(6, 4, $na[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(45, 4, $noma[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(45, 4, ucwords(strtolower($prenoma[$i])), 'LRB', '0', 'C', 1);
                $pdf->Cell(24, 4, $licencea[$i] . $saisona[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(15, 4, $diva[$i], 'LRB', '1', 'C', 1);
                $indiqsaison = '';
            }
            $pdf->SetFillColor(200, 200, 200);

            //Equipe B

            $pdf->Ln(1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(45, 5, $lang['Equipe_B'] . ":", 'LTB', '0', 'C', 1);
            if ($equipebFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 9);
                $pdf->Cell(90, 5, $equipeb, 'TRB', '1', 'L', 1);
            } else {
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(90, 5, $equipeb, 'TRB', '1', 'C', 1);
            }
            $pdf->SetFont('Arial', '', 10);

            $pdf->Cell(6, 6, $lang['Num'], '1', '0', 'C');
            $pdf->Cell(45, 6, $lang['Nom'], '1', '0', 'C');
            $pdf->Cell(45, 6, $lang['Prenom'], '1', '0', 'C');
            $pdf->Cell(24, 6, "Licence", '1', '0', 'C');
            $pdf->Cell(15, 6, "Cat", '1', '1', 'C');

            for ($i = 1; $i <= 10; $i++) {
                if ($nb[$i] == 'E') {
                    $pdf->SetFillColor(245, 245, 180);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(6, 4, $nb[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(45, 4, $nomb[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(45, 4, ucwords(strtolower($prenomb[$i])), 'LRB', '0', 'C', 1);
                $pdf->Cell(24, 4, $licenceb[$i] . $saisonb[$i], 'LRB', '0', 'C', 1);
                $pdf->Cell(15, 4, $divb[$i], 'LRB', '1', 'C', 1);
                $indiqsaison = '';
            }
            $pdf->SetFillColor(200, 200, 200);

            //signatures
            $pdf->Ln(1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(21, 12, $lang['Signatures'], 'LRT', '0', 'C');
            $pdf->Cell(38, 12, "", '1', '0', 'C');
            $pdf->Cell(38, 12, "", '1', '0', 'C');
            $pdf->Cell(38, 12, "", '1', '1', 'C');
            $pdf->Cell(21, 4, $lang['avant_match'], 'LRB', '0', 'C');
            $pdf->Cell(38, 4, $lang['Capitaine'] . " A", '1', '0', 'C');
            $pdf->Cell(38, 4, $lang['Capitaine'] . " B", '1', '0', 'C');
            $pdf->Cell(38, 4, $lang['Arbitre_1'], '1', '1', 'C');

            //Colonne 2

            $x0 = 150;
            $pdf->SetLeftMargin($x0);
            $pdf->SetX($x0);
            $pdf->SetY(8);

            $pdf->SetFont('Arial', 'B', 14);
            if ($row['Type'] == 'E') {
                $pdf->Cell(70, 6, $lang['FEUILLE_DE_MARQUE'], 0, 0, 'C');
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(58, 6, $lang['Vainqueur_obligatoire'], 1, 1, 'C', 1);
                $pdf->SetFont('Arial', 'BI', 14);
                $pdf->SetFillColor(200, 200, 200);
            } else {
                $pdf->Cell(135, 6, $lang['FEUILLE_DE_MARQUE'], 0, 1, 'C');
            }

            // Type de match
            $pdf->image('../img/type' . $row['Type'] . '2.png', 214, 14, 6, 0);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, 5, $lang['Equ_A'] . ": ", 'LT', 0, 'L', 1);

            if ($equipeaFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(42, 5, $equipea, 'TR', 0, 'C', 1);
            } else {
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(42, 5, $equipea, 'TR', 0, 'C', 1);
            }

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(1, 5, "", 0, 0, 'C');
            $pdf->Cell(19, 5, '', 'LTR', 0, 'C');
            $pdf->Cell(1, 5, "", 0, 0, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, 5, $lang['Equ_B'] . ": ", 'LT', 0, 'L', 1);

            if ($equipebFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(42, 5, $equipeb, 'TR', 1, 'C', 1);
            } else {
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(42, 5, $equipeb, 'TR', 1, 'C', 1);
            }

            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(15, 3, '', 'LB', 0, 'C', 1);
            $pdf->Cell(42, 3, $colorA, 'RB', 0, 'C', 1);
            $pdf->Cell(1, 3, "", 0, 0, 'C');
            $pdf->Cell(19, 3, $lang['Periode'], 'LR', 0, 'C');
            $pdf->Cell(1, 3, "", 0, 0, 'C');
            $pdf->Cell(15, 3, '', 'LB', 0, 'C', 1);
            $pdf->Cell(42, 3, $colorB, 'RB', 1, 'C', 1);

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(5, 5, $lang['V'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(5, 5, $lang['J'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(5, 5, $lang['R'], 1, 0, 'C', 1);
            $pdf->Cell(36, 5, $lang['Num'] . " - " . $lang['Nom'], 1, 0, 'C');
            $pdf->Cell(6, 5, $lang['But'], 1, 0, 'C');
            $pdf->Cell(1, 5, "", 0, 0, 'C');
            $pdf->Cell(19, 5, "+ " . $lang['Temps'], 'LRB', '0', 'C');
            $pdf->Cell(1, 5, "", 0, 0, 'C');
            $pdf->Cell(6, 5, $lang['But'], 1, 0, 'C');
            $pdf->Cell(36, 5, $lang['Num'] . " - " . $lang['Nom'], 1, 0, 'C');
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(5, 5, $lang['V'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(5, 5, $lang['J'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(5, 5, $lang['R'], 1, 1, 'C', 1);

            for ($i = 0; $i < 26; $i++) {
//			for($i=0;$i<23;$i++)	// @COSANDCO_WAMPSER
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d2']) ? $detail[$i]['d2'] : '', 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d3']) ? $detail[$i]['d3'] : '', 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d4']) ? $detail[$i]['d4'] : '', 1, 0, 'C', 1);
                $pdf->Cell(36, 4, isset($detail[$i]['d1']) ? $detail[$i]['d1'] : '', 1, 0, 'L');
                $pdf->Cell(6, 4, isset($detail[$i]['d5']) ? $detail[$i]['d5'] : '', 1, 0, 'C');
                $pdf->Cell(1, 4, "", 0, 0, 'C');
                $pdf->Cell(19, 4, isset($detail[$i]['d6']) ? $detail[$i]['d6'] : '', 1, 0, 'C');
                $pdf->Cell(1, 4, "", 0, 0, 'C');
                $pdf->Cell(6, 4, isset($detail[$i]['d7']) ? $detail[$i]['d7'] : '', 1, 0, 'C');
                $pdf->Cell(36, 4, isset($detail[$i]['d11']) ? $detail[$i]['d11'] : '', 1, 0, 'L');
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d8']) ? $detail[$i]['d8'] : '', 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d9']) ? $detail[$i]['d9'] : '', 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(5, 4, isset($detail[$i]['d10']) ? $detail[$i]['d10'] : '', 1, 1, 'C', 1);
            }
            $pdf->Ln(1);

            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(44, 8, $lang['Equipe_A'], 0, 0, 'C');
            $pdf->Cell(15, 8, $scoreMitempsA, 1, 0, 'C');
            $pdf->Cell(17, 8, $lang['mi-temps'], 0, 0, 'C');
            $pdf->Cell(15, 8, $scoreMitempsB, 1, 0, 'C');
            $pdf->Cell(44, 8, $lang['Equipe_B'], 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', 13);
            $pdf->Cell(57, 3, "", 0, 0);
            $pdf->Cell(21, 3, $lang['Score'], 0, 0, 'C');
            $pdf->Cell(57, 3, "", 0, 1);

            $pdf->SetLineWidth(1);

            if ($equipeaFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(41, 8, $equipea, 0, 0, 'C');
            } else {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(41, 8, $equipea, 0, 0, 'C');
            }

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(15, 8, $ScoreA, 1, 0, 'C');
            $pdf->Cell(23, 8, $typeScore, 0, 0, 'C');
            $pdf->Cell(15, 8, $ScoreB, 1, 0, 'C');

            if ($equipebFormat == 'Auto') {
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(41, 8, $equipeb, 0, 1, 'C');
            } else {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(41, 8, $equipeb, 0, 1, 'C');
            }

            $pdf->Ln(2);

            $pdf->SetLineWidth(0.2);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(93, 4, $lang['Remarques'], 'LRT', 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(21, 4, $lang['Heure_debut'], 'LRT', 0, 'C');
            $pdf->Cell(21, 4, $lang['Heure_fin'], 'LRT', 1, 'C');
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(93, 3, $lang['A_defaut'], 'LR', 0, 'L');
            $pdf->Cell(21, 3, '', 'LR', 0, 'L');
            $pdf->Cell(21, 3, '', 'LR', 1, 'L');
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(93, 4, '', 'LR', 0, 'L');
            $pdf->Cell(21, 4, '', 'LRB', 0, 'C');
            $pdf->Cell(21, 4, $heure_fin, 'LRB', 1, 'C');
            $pdf->Cell(135, 4, $Commentaires1, 'LR', 1, 'L');
            $pdf->Cell(135, 4, '', 'LRB', 1, 'L');

            //signatures
            $pdf->Ln(1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(21, 12, $lang['Signatures'], 'LRT', '0', 'C');
            $pdf->Cell(31, 12, "", '1', '0', 'C');
            $pdf->Cell(31, 12, "", '1', '0', 'C');
            $pdf->Cell(52, 12, "", '1', '1', 'C');
            $pdf->Cell(21, 4, $lang['apres_match'], 'LRB', 0, 'C');
            $pdf->Cell(31, 4, $lang['Capitaine'] . " A", 1, 0, 'C');
            //$pdf->Cell(31,4,$lang['Entraineur']." A",1,0,'C');
            $pdf->Cell(31, 4, $lang['Capitaine'] . " B", 1, 0, 'C');
            //$pdf->Cell(31,4,$lang['Entraineur']." B",1,0,'C');
            $pdf->Cell(31, 4, $lang['Arbitre_1'], 1, 0, 'C');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(21, 4, "ID #" . $idMatch, 1, 1, 'C');
            $pdf->SetX(10);
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(140, 3, $lang['observation'], 0, 0, 'L');

            // QRCode
            $qrcode = new QRcode('https://kayak-polo.info/admin/FeuilleMarque2.php?idMatch=' . $idMatch, 'L'); // error level : L, M, Q, H
            //$qrcode->displayFPDF($fpdf, $x, $y, $s, $background, $color);
            $qrcode->displayFPDF($pdf, 264, 164, 21);

            $pdf->Cell(135, 3, $lang['impression'] . ": " . $dateprint . " " . date("H:i"), 0, 1, 'R');
            // Pays
            if ($arrayCompetition['Code_niveau'] == 'INT' && $paysA != '') {
                $pdf->image('../img/Pays/' . $paysA . '.png', 151, 15, 9, 6);
            }
            if ($arrayCompetition['Code_niveau'] == 'INT' && $paysB != '') {
                $pdf->image('../img/Pays/' . $paysB . '.png', 229, 15, 9, 6);
            }

            $pdf->SetX(10);

            // Commentaires sur la 2ème page
            if (strlen($Commentaires) > 85 or $nblignes > 26) {
                $pdf->AddPage();
                $pdf->SetAutoPageBreak(true, 1);
                // Rappel entête
                $x0 = 10;
                $pdf->SetLeftMargin($x0);
                $pdf->SetX($x0);
                $pdf->SetY(9);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->SetFont('Arial', '', 10);
                if ($arrayCompetition['Titre_actif'] == 'O') {
                    $pdf->Cell(111, 4, $competition, 'LT', '0', 'L');
                } else {
                    $pdf->Cell(111, 4, $arrayCompetition['Soustitre'], 'LT', '0', 'L');
                }
                if ($arrayCompetition['Soustitre2'] != '') {
                    $pdf->Cell(24, 4, $arrayCompetition['Soustitre2'], 'RT', '1', 'R');
                } else {
                    $pdf->Cell(24, 4, $categorie, 'RT', '1', 'R');
                }

                $pdf->Cell(111, 4, $lang['Organisateur'] . ": " . ucwords(strtolower($organisateur)), 'L', '0', 'L');
                $pdf->Cell(24, 4, $lang['Saison'] . ": " . $saison, 'R', '1', 'L');

                $pdf->Cell(68, 4, $responsableT . ucwords(strtolower($responsable)), 'L', '0', 'L');
                $pdf->Cell(67, 4, $delegueT . ucwords(strtolower($delegue)), 'R', '1', 'L');

                $pdf->Cell(135, 1, "", 'LR', '1', 'C');
                $pdf->Cell(135, 1, "", 'LTR', '1', 'C', 1);
                $pdf->Cell(60, 4, $lang['Lieu'] . ": " . $lieu . " (" . $dpt . ")", 'L', '0', 'L', 1);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(40, 4, $date . "   " . $heure, '0', '0', 'L', 1);
                $pdf->Cell(35, 4, $lang['Terrain'] . ": " . $terrain, 'R', '1', 'R', 1);

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(60, 4, $lang['Phase'] . ": " . $phase, 'L', '0', 'L', 1);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(30, 4, $lang['Match_no'] . $no, '0', '0', 'L', 1);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(45, 4, $intitule, 'R', '1', 'R', 1);

                $pdf->Cell(135, 1, "", 'LR', '1', 'C', 1);
                $pdf->Cell(135, 1, "", 'LTR', '1', 'L');
                $pdf->Cell(135, 4, $lang['Arbitre_1'] . ": " . $principal, 'LR', '1', 'L');
                $pdf->Cell(135, 4, $lang['Arbitre_2'] . ": " . $secondaire, 'LR', '1', 'L');
                $pdf->Cell(68, 4, $lang['Secretaire'] . ": " . $secretaire, 'L', '0', 'L');
                $pdf->Cell(67, 4, $lang['Chronometre'] . ": " . $chronometre, 'R', '1', 'L');
                $pdf->Cell(135, 1, "", 'LBR', '1', 'C');

                //Equipe A
                $pdf->Ln(1);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(45, 5, $lang['Equipe_A'] . ":", 'LTB', '0', 'C', 1);
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(90, 5, $equipea, 'TRB', '1', 'C', 1);
                $pdf->SetFont('Arial', '', 10);

                //Equipe B
                $pdf->Ln(1);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(45, 5, $lang['Equipe_B'] . ":", 'LTB', '0', 'C', 1);
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(90, 5, $equipeb, 'TRB', '1', 'C', 1);
                $pdf->SetFont('Arial', '', 10);

                // colonne 2
                $x0 = 150;
                $pdf->SetLeftMargin($x0);
                $pdf->SetX($x0);
                $pdf->SetY(9);
                $pdf->SetLineWidth(0.2);
                if ($nblignes > 26) {
                    for ($i = 0; $i < ($nblignes - 26); $i++) {
                        $pdf->SetFillColor(170, 255, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d2']) ? $detail2[$i]['d2'] : '', 1, 0, 'C', 1);
                        $pdf->SetFillColor(255, 255, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d3']) ? $detail2[$i]['d3'] : '', 1, 0, 'C', 1);
                        $pdf->SetFillColor(255, 170, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d4']) ? $detail2[$i]['d4'] : '', 1, 0, 'C', 1);
                        $pdf->Cell(36, 4, isset($detail2[$i]['d1']) ? $detail2[$i]['d1'] : '', 1, 0, 'L');
                        $pdf->Cell(6, 4, isset($detail2[$i]['d5']) ? $detail2[$i]['d5'] : '', 1, 0, 'C');
                        $pdf->Cell(1, 4, "", 0, 0, 'C');
                        $pdf->Cell(19, 4, isset($detail2[$i]['d6']) ? $detail2[$i]['d6'] : '', 1, 0, 'C');
                        $pdf->Cell(1, 4, "", 0, 0, 'C');
                        $pdf->Cell(6, 4, isset($detail2[$i]['d7']) ? $detail2[$i]['d7'] : '', 1, 0, 'C');
                        $pdf->Cell(36, 4, isset($detail2[$i]['d11']) ? $detail2[$i]['d11'] : '', 1, 0, 'L');
                        $pdf->SetFillColor(170, 255, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d8']) ? $detail2[$i]['d8'] : '', 1, 0, 'C', 1);
                        $pdf->SetFillColor(255, 255, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d9']) ? $detail2[$i]['d9'] : '', 1, 0, 'C', 1);
                        $pdf->SetFillColor(255, 170, 170);
                        $pdf->Cell(5, 4, isset($detail2[$i]['d10']) ? $detail2[$i]['d10'] : '', 1, 1, 'C', 1);
                    }
                    $pdf->Ln(1);
                }

                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(100, 4, $lang['Remarques'], 'LT', 0, 'L');
                $pdf->Cell(35, 4, "PAGE 2", 'RT', 1, 'R');
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(135, 3, "", 'LR', '1', 'L');
                $pdf->SetFont('Arial', 'I', 9);
                $pdf->MultiCell(135, 4, $Commentaires, 'LR', 'L');
                $pdf->Cell(135, 3, "", 'LRB', '1', 'L');
                $pdf->Ln(1);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(21, 12, $lang['Signatures'], 'LRT', '0', 'C');
                $pdf->Cell(38, 12, "", '1', '0', 'C');
                $pdf->Cell(38, 12, "", '1', '0', 'C');
                $pdf->Cell(38, 12, "", '1', '1', 'C');
                $pdf->Cell(21, 4, $lang['apres_match'], 'LRB', '0', 'C');
                $pdf->Cell(38, 4, $lang['Capitaine'] . " A", '1', '0', 'C');
                $pdf->Cell(38, 4, $lang['Capitaine'] . " B", '1', '0', 'C');
                $pdf->Cell(38, 4, $lang['Arbitre_1'], '1', '1', 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(135, 3, "ID #" . $idMatch . " - " . $lang['impression'] . ": " . $dateprint . " " . date("H:i"), 0, 0, 'L');
            }
        }

        $pdf->Output('Match(s) ' . $listMatch . '.pdf', 'I');
    }

}

//Création des feuilles
$page = new FeuilleMatch();


