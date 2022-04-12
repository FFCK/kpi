<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require('lib/fpdf/fpdf.php');

require_once('lib/qrcode/qrcode.class.php');

// Pieds de page
class PDF extends FPDF
{

  function Footer()
  {
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
class PdfQrCodeApp extends MyPage
{

  function __construct()
  {
    MyPage::MyPage();
    // Chargement des titre ...
    $myBdd = new MyBdd();
    $idEvenement = utyGetSession('idEvenement', -1);
    $idEvenement = utyGetGet('Evt', $idEvenement);
    $libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
    $titreEvenementCompet = 'Evénement (Event) : ' . $libelleEvenement;
    $codeSaison = $myBdd->GetActiveSaison();

    // Entête PDF ...	  
    $pdf = new PDF('L');
    $pdf->Open();
    $pdf->SetTitle("QR Codes");
    $pdf->SetAuthor("Kayak-polo.info");
    $pdf->SetCreator("Kayak-polo.info avec FPDF");
    $pdf->SetTopMargin(30);
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 15);

    $titreDate = "Saison (Season) " . $codeSaison;
    // titre
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(137, 5, $titreEvenementCompet, 0, 0, 'L');
    $pdf->Cell(136, 5, $titreDate, 0, 1, 'R');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(273, 25, "", 0, 1, 'C');
    $pdf->Cell(273, 6, "Application", 0, 1, 'C');
    $pdf->Ln(20);

    $logo = 'img/CNAKPI_small.jpg';
    $pdf->Image($logo, 118, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");

    $logoApp = 'img/logo.gif';
    $logo1 = imagecreatefromstring(file_get_contents($logoApp));
    $logo_width = imagesx($logo1);
    $logo_height = imagesy($logo1);
    $height = ($logo_height / $logo_width * 16);
    $y = (50 - $height) / 2;

    // QRCode Matchs
    $qrcode = new QRcode('https://www.kayak-polo.info/app/#/event/' . $idEvenement, 'Q'); // error level : L, M, Q, H
    $qrcode->displayFPDF($pdf, 115, 85, 62);
    $pdf->Image($logoApp, 138, $y + 89, 16, $height, 'gif', "https://www.kayak-polo.info/app/#/event/" . $idEvenement);

    $pdf->Cell(273, 65, "", 0, 1, 'C');
    $pdf->Cell(273, 6, "https://www.kayak-polo.info/app/#/event/" . $idEvenement, 0, 1, 'C');

    $pdf->Output('Links.pdf', 'I');
  }
}

$page = new PdfQrCodeApp();
