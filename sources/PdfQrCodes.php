<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require('fpdf/fpdf.php');

require_once('qrcode/qrcode.class.php');

// Pieds de page
class PDF extends FPDF {

    function Footer() {
        //Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        //Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        //Numéro de page centré
        $this->Cell(137, 10, '', 0, 0, 'L');
        $this->Cell(136, 5, '', 0, 1, 'R');
    }

}

// Liste des Matchs d'une Journee ou d'un Evenement 
class PdfQrCodes extends MyPage {

    function PdfQrCodes() {
        MyPage::MyPage();
        // Chargement des titre ...
        $myBdd = new MyBdd();
        $lstJournee = utyGetSession('lstJournee', 0);
        $idEvenement = utyGetSession('idEvenement', -1);
        $idEvenement = utyGetGet('Evt', $idEvenement);
        if (isset($_GET['Evt'])) {
            $lstJournee = '';
            $sql = "SELECT Id_journee FROM gickp_Evenement_Journees WHERE Id_evenement = " . $_GET['Evt'];
            $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load =>  " . $sql);
            $num_results = mysql_num_rows($result);
            for ($j = 0; $j < $num_results; $j++) {
                $row = mysql_fetch_array($result);
                if ($lstJournee != '') {
                    $lstJournee .= ',';
                }
                $lstJournee .= $row['Id_journee'];
            }
        }
        $codeSaison = utyGetSaison();
        $codeSaison = utyGetGet('S', $codeSaison);
        $orderMatchs = utyGetSession('orderMatchs', 'Order By a.Date_match, d.Lieu, a.Heure_match, a.Terrain');
        $laCompet = utyGetSession('codeCompet', 0);
        $laCompet = utyGetGet('Compet', $laCompet);
        if ($laCompet != 0) {
            $lstJournee = 0;
            $idEvenement = -1;
        }
        $codeCompet = $laCompet;
        $sql = "Select a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, a.Date_match, a.Heure_match, ";
        $sql .= "a.Libelle, a.Terrain, b.Libelle EquipeA, c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, ";
        $sql .= "a.Arbitre_principal, a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, a.Validation, ";
        $sql .= "d.Code_competition, d.Code_saison, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee ";
        $sql .= "From gickp_Matchs a ";
        $sql .= "Left Outer Join gickp_Competitions_Equipes b On (a.Id_equipeA = b.Id) ";
        $sql .= "Left Outer Join gickp_Competitions_Equipes c On (a.Id_equipeB = c.Id) ";
        $sql .= ", gickp_Journees d ";
        if ($lstJournee == 0) {
            $sql .= "Where d.Code_competition = '" . $laCompet . "' And d.Code_saison = $codeSaison ";
        } else {
            $sql .= "Where a.Id_journee In ($lstJournee) ";
        }
        $sql .= "And a.Id_journee = d.Id ";
        $sql .= "And a.Publication = 'O' ";
        $sql .= $orderMatchs;

        $orderMatchsKey1 = utyKeyOrder($orderMatchs, 0);
        $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load =>  " . $sql);
        $num_results = mysql_num_rows($result);
        $PhaseLibelle = 0;
        for ($j = 0; $j < $num_results; $j++) {
            $row1 = mysql_fetch_array($result);
            if ($row1['Phase'] != '' || $row1['Libelle'] != '') {
                $PhaseLibelle = 1;
            }
            $lastCompetEvt = $row1['Code_competition'];
            $lastSaisonEvt = $row1['Code_saison'];
        }
        $Oldrupture = "";
        // Chargement des infos de l'évènement ou de la compétition
        $titreEvenementCompet = '';
        if ($idEvenement != -1) {
            $libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
            $titreEvenementCompet = 'Evénement (Event) : ' . $libelleEvenement;
            $arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $codeSaison);
            $lienDirectPitch = 'idEvt=' . $idEvenement . '&S=' . $lastSaisonEvt;
        } else {
            $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
            if ($arrayCompetition['Titre_actif'] == 'O') {
                $titreEvenementCompet = $arrayCompetition['Libelle'];
            } else {
                $titreEvenementCompet = $arrayCompetition['Soustitre'];
            }
            if ($arrayCompetition['Soustitre2'] != '') {
                $titreEvenementCompet .= ' - ' . $arrayCompetition['Soustitre2'];
            }
            //$titreEvenementCompet = 'Compétition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';
            $lienDirectPitch = 'idCompet=' . $codeCompet . '&S=' . $codeSaison;
        }

        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Entête PDF ...	  
        $pdf = new PDF('L');
        $pdf->Open();
        $pdf->SetTitle("QR Codes");
        $pdf->SetAuthor("Kayak-polo.info");
        $pdf->SetCreator("Kayak-polo.info avec FPDF");
        $pdf->SetTopMargin(30);
        $pdf->AddPage();
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 28);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }
        // Affichage
        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/logoKPI-small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/logoKPI-small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
        }

        $titreDate = "Saison (Season) " . $codeSaison;
        // titre
        $pdf->SetFont('Arial', 'BI', 12);
        $pdf->Cell(137, 5, $titreEvenementCompet, 0, 0, 'L');
        $pdf->Cell(136, 5, $titreDate, 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(273, 15, "", 0, 1, 'C');
        $pdf->Cell(273, 6, "Liens - Links", 0, 1, 'C');
        $pdf->Ln(20);

        $pdf->Text(75, 85, 'Matchs - Games');
        // QRCode Matchs
        $qrcode = new QRcode('https://www.kayak-polo.info/kpmatchs.php?Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison . '&lang=en', 'L'); // error level : L, M, Q, H
        //$qrcode->displayFPDF($fpdf, $x, $y, $s, $background, $color);
        $qrcode->displayFPDF($pdf, 75, 90, 40);


        $pdf->Text(170, 85, 'Classement - Standing');
        // QRCode Classements
        $qrcode2 = new QRcode('https://www.kayak-polo.info/kpclassements.php?Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison . '&lang=en', 'L'); // error level : L, M, Q, H
        //$qrcode->displayFPDF($fpdf, $x, $y, $s, $background, $color);
        $qrcode2->displayFPDF($pdf, 175, 90, 40);

        $pdf->Output('Game list' . '.pdf', 'I');
    }

}

$page = new PdfQrCodes();
