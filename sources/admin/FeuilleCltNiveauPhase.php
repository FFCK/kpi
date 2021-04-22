<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

// Gestion de la Feuille de Classement par Journee
class FeuilleCltNiveauPhase extends MyPage
{
    function __construct()
    {
        MyPage::MyPage();
        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        //Saison
        $codeSaison = $myBdd->GetActiveSaison();
        $titreDate = "Saison " . $codeSaison;

        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $titreCompet = 'Compétition : ' . $arrayCompetition['Libelle'] . ' (' . $codeCompet . ')';
        $qualif = $arrayCompetition['Qualifies'];
        $elim = $arrayCompetition['Elimines'];

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        $getlang = utyGetGet('lang', false);
		if ($getlang  == 'en') {
            $arrayCompetition['En_actif'] = 'O';
        } elseif ($getlang  == 'fr') {
            $arrayCompetition['En_actif'] = '';
        }

        if ($arrayCompetition['En_actif'] == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        //Création
        $pdf = new FPDF('P');
        $pdf->Open();
        $pdf->SetTitle("Classement par phase");

        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info");
        $pdf->AddPage();
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('../img/CNAKPI_small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
        }

        // titre
        $pdf->Ln(22);
        $pdf->SetFont('Arial','B',14);
        if ($arrayCompetition['Titre_actif'] == 'O') {
            $pdf->Cell(190,5,$arrayCompetition['Libelle'],0,1,'C');
        } else {
            $pdf->Cell(190,5,$arrayCompetition['Soustitre'],0,1,'C');
        }


//		$pdf->Ln(4);
        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre2'],0,1,'C');
        }

        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(190, 5, $lang['CLASSEMENT_PAR_PHASE'] . ' ' . $lang['PROVISOIRE'], 0, 0, 'C');

        $pdf->Ln(4);

        // données
        $myBdd = new MyBdd();

        $sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, cej.Id_journee, cej.Clt, 
            cej.Pts, cej.J, cej.G, cej.N, cej.P, cej.F, cej.Plus, cej.Moins, cej.Diff, 
            cej.PtsNiveau, cej.CltNiveau, j.Phase, j.Niveau, j.Lieu, j.Type, 
            IF(LEFT(j.Phase, 5) = 'Group' OR LEFT(j.Phase, 5) = 'Poule', j.Phase, 'Z') typePhase 
            FROM kp_competition_equipe ce, kp_competition_equipe_journee cej 
            JOIN kp_journee j ON (cej.Id_journee = j.Id) 
            WHERE ce.Id = cej.Id 
            AND j.Code_competition = ? 
            AND j.Code_saison = ? 
            ORDER BY typePhase, j.Niveau, j.Phase, j.Date_debut, 
                j.Lieu, cej.Clt, cej.Diff DESC, cej.Plus DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));

        $idJournee = 0;
        $niveau = 1;
        while ($row = $result->fetch()){
            if ($niveau != $row['Niveau']) {
                $pdf->Cell(85,4,"",0,0);
                $pdf->Cell(20,4,"","B",1);
            }
            $niveau = $row['Niveau'];
            if ($row['Type'] == 'E') {
                if ($row['Id_journee'] != $idJournee) {
                    $idJournee = $row['Id_journee'];
                    $pdf->Ln(5);
                    $pdf->SetFont('Arial', 'BI', 10);
                    $pdf->Cell(190, 5, $row['Phase'], 0, 1, 'C');
                    $pdf->SetFont('Arial', 'B', 9);
                    $sql2 = "SELECT m.ScoreA, m.ScoreB, ce1.Libelle EquipeA, ce2.Libelle EquipeB 
                        FROM kp_match m 
                        LEFT OUTER JOIN kp_competition_equipe ce1 ON (m.Id_equipeA = ce1.Id) 
                        LEFT OUTER JOIN kp_competition_equipe ce2 ON (m.Id_equipeB = ce2.Id) 
                        WHERE m.Id_journee = ? ";
                    $result2 = $myBdd->pdo->prepare($sql2);
                    $result2->execute(array($row['Id_journee']));
                    while($row2 = $result2->fetch()) {
                        if ($row2['ScoreA'] > $row2['ScoreB']) {
                            $pdf->SetFont('Arial', 'B', 9);
                        } else {
                            $pdf->SetFont('Arial', '', 9);
                        }
                        $pdf->Cell(89, 4, $row2['EquipeA'], 0, 0, 'R');
                        $pdf->Cell(5, 4, $row2['ScoreA'], 0, 0, 'C');
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->Cell(2, 4, '-', 0, 0, 'C');
                        if ($row2['ScoreA'] < $row2['ScoreB']) {
                            $pdf->SetFont('Arial', 'B', 9);
                        } else {
                            $pdf->SetFont('Arial', '', 9);
                        }
                        $pdf->Cell(5, 4, $row2['ScoreB'], 0, 0, 'C');
                        $pdf->Cell(89, 4, $row2['EquipeB'], 0, 1, 'L');
                    }
                }
            } else {
                $idEquipe = $row['Id'];
                if ($row['Id_journee'] != $idJournee) {
                    $idJournee = $row['Id_journee'];

                    $pdf->Ln(5);
                    if ($arrayCompetition['Points'] == '4-2-1-0') {
                        $pdf->Cell(26, 4, '', 0, 0, 'C');
                    } else {
                        $pdf->Cell(30, 4, '', 0, 0, 'C');
                    }
                    $pdf->SetFont('Arial', 'BI', 10);
                    $pdf->Cell(61, 4, $row['Phase'], 'B', 0, 'C'); //     "JOURNEE ".$codeCompet.'/'.$idJournee.'/'.
                    $pdf->SetFont('Arial', 'BI', 9);
                    $pdf->Cell(8, 4, "Pts", 'B', 0, 'C');
                    $pdf->Cell(7, 4, $lang['Joue'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $lang['G'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $lang['N'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $lang['P'], 'B', 0, 'C');
                    if ($arrayCompetition['Points'] == '4-2-1-0') {
                        $pdf->Cell(7, 4, $lang['F'], 'B', 0, 'C');
                    }
                    $pdf->Cell(8, 4, "+", 'B', 0, 'C');
                    $pdf->Cell(8, 4, "-", 'B', 0, 'C');
                    $pdf->Cell(8, 4, "+/-", 'B', 1, 'C');
                }

                $pts = $row['Pts'];
                $len = strlen($pts);
                if ($len > 2) {
                    if (substr($pts, $len - 2, 2) == '00') {
                        $pts = substr($pts, 0, $len - 2);
                    } else {
                        $pts = substr($pts, 0, $len - 2) . '.' . substr($pts, $len - 2, 2);
                    }
                }
                if ($row['J'] != '0') {
                    $pdf->SetFont('Arial', '', 9);
                    if ($arrayCompetition['Points'] == '4-2-1-0') {
                        $pdf->Cell(26, 4, '', 0, 0, 'C');
                    } else {
                        $pdf->Cell(30, 4, '', 0, 0, 'C');
                    }
                    $pdf->Cell(61, 4, $row['Clt'] . '. ' . $row['Libelle'], 'B', 0, 'L');
                    $pdf->Cell(8, 4, $pts, 'B', 0, 'C');
                    $pdf->Cell(7, 4, $row['J'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $row['G'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $row['N'], 'B', 0, 'C');
                    $pdf->Cell(7, 4, $row['P'], 'B', 0, 'C');
                    if ($arrayCompetition['Points'] == '4-2-1-0') {
                        $pdf->Cell(7, 4, $row['F'], 'B', 0, 'C');
                    }
                    $pdf->Cell(8, 4, $row['Plus'], 'B', 0, 'C');
                    $pdf->Cell(8, 4, $row['Moins'], 'B', 0, 'C');
                    $pdf->Cell(8, 4, $row['Diff'], 'B', 1, 'C');
                }
            }
        }

        $pdf->SetFont('Arial', 'I', 8);
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetXY(165, 263);
        } else {
            $pdf->SetXY(165, 270);
        }
        if ($lang == $langue['en']) {
            $pdf->Write(4, date('Y-m-d H:i', strtotime($_SESSION['tzOffset'])));
        } else {
            $pdf->Write(4, date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'])));
        }
        $pdf->Output('Classement par phase ' . $codeCompet . '.pdf', 'I');
    }

}

$page = new FeuilleCltNiveauPhase();
