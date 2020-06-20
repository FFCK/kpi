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

    function __construct() {
        MyPage::MyPage();
        // Chargement des titre ...
        $myBdd = new MyBdd();
        $lstJournee = utyGetSession('lstJournee', 0);
        $idEvenement = utyGetSession('idEvenement', -1);
        $idEvenement = utyGetGet('Evt', $idEvenement);
        if (isset($_GET['Evt'])) {
			$lstJournee = [];
            $sql = "SELECT Id_journee 
                FROM gickp_Evenement_Journees 
                WHERE Id_evenement = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($_GET['Evt']));
            while ($row = $result->fetch()) {
                $lstJournee[] = $row['Id_journee'];
            }
        } else {
            $lstJournee = explode(',', $lstJournee);
        }
		$codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetGet('S', $codeSaison);
        $orderMatchs = utyGetSession('orderMatchs', 'ORDER BY a.Date_match, d.Lieu, a.Heure_match, a.Terrain');
        $laCompet = utyGetSession('codeCompet', 0);
        $laCompet = utyGetGet('Compet', $laCompet);
        if ($laCompet != 0) {
            $lstJournee = [];
            $idEvenement = -1;
        }
        $codeCompet = $laCompet;
        if ($lstJournee != 0) {
            $in  = str_repeat('?,', count($lstJournee) - 1) . '?';
            $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, a.Validation, 
                d.Code_competition, d.Code_saison, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee 
                FROM gickp_Journees d, gickp_Matchs a 
                LEFT OUTER JOIN gickp_Competitions_Equipes b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN gickp_Competitions_Equipes c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND a.Publication = 'O' 
                AND a.Id_journee In ($in) 
                $orderMatchs ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute($lstJournee);
        } else {
            $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, a.Validation, 
                d.Code_competition, d.Code_saison, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee 
                FROM gickp_Journees d, gickp_Matchs a 
                LEFT OUTER JOIN gickp_Competitions_Equipes b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN gickp_Competitions_Equipes c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND a.Publication = 'O' 
                AND d.Code_competition = ?
                AND d.Code_saison = ?  
                $orderMatchs ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeCompet, $codeSaison));
        }

        while($row1 = $result->fetch()) {
            $lastCompetEvt = $row1['Code_competition'];
        }
        // Chargement des infos de l'évènement ou de la compétition
        $titreEvenementCompet = '';
        if ($idEvenement != -1) {
            $libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
            $titreEvenementCompet = 'Evénement (Event) : ' . $libelleEvenement;
            $arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $codeSaison);
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
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            // Bandeau
            $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            if ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
                $logo = $img['image'];
            } else {
                $logo = 'img/CNAKPI_small.jpg';
            }
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // KPI + Logo    
            $pdf->Image('img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            $logo = $img['image'];
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            // KPI
            $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $logo = 'img/CNAKPI_small.jpg';
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // Logo
            $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            $logo = $img['image'];
        }
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            // Sponsor
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

        $pdf->Text(75, 80, 'Matchs - Games');
        // QRCode Matchs
        $qrcode = new QRcode('https://www.kayak-polo.info/kpmatchs.php?Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison . '&lang=en', 'Q'); // error level : L, M, Q, H
        // $qrcode = $qrcode->addLogo($qrcode, $logo, .3);
        $qrcode->displayFPDF($pdf, 70, 85, 50);
        $pdf->Image($logo, 83, 105, 0, 9, 'jpg', "https://www.kayak-polo.info");
        
        
        $pdf->Text(170, 80, 'Progression - Progress');
        // QRCode Progression
        $qrcode2 = new QRcode('https://www.kayak-polo.info/kpchart.php?Group=' . $arrayCompetition['Code_ref'] . '&Compet=' . $arrayCompetition['Code'] . '&Saison=' . $codeSaison . '&lang=en', 'Q'); // error level : L, M, Q, H
        $qrcode2->displayFPDF($pdf, 170, 85, 50);
        $pdf->Image($logo, 183, 105, 0, 9, 'jpg', "https://www.kayak-polo.info");

        $pdf->Output('Links.pdf', 'I');
    }

}

$page = new PdfQrCodes();
