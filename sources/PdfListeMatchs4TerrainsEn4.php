<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');
// QRcode class is now autoloaded via Composer

// Liste des Matchs d'une Journee ou d'un Evenement 
class PdfListeMatchs extends MyPage
{
  function __construct()
  {
    parent::__construct();
    // Chargement des Matchs des journées ...
    $filtreJour = utyGetSession('filtreJour', '');
    $filtreJour = utyGetPost('filtreJour', $filtreJour);
    $filtreJour = utyGetGet('filtreJour', $filtreJour);

    $filtreTerrain = utyGetSession('filtreTerrain', '');
    $filtreTerrain = utyGetPost('filtreTerrain', $filtreTerrain);
    $filtreTerrain = utyGetGet('filtreTerrain', $filtreTerrain);

    $filtreTour = utyGetSession('filtreTour', '');
    $filtreTour = utyGetPost('filtreTour', $filtreTour);
    $filtreTour = utyGetGet('filtreTour', $filtreTour);

    $filtreMatchsNonVerrouilles = utyGetSession('filtreMatchsNonVerrouilles', '');

    $myBdd = new MyBdd();
    $lstJournee = utyGetSession('lstJournee', 0);

    // Filtre Journée/Phase/Poule : si une journée spécifique est sélectionnée, utiliser celle-ci
    $idSelJournee = utyGetSession('idSelJournee', '*');
    if ($idSelJournee != '*' && $idSelJournee != '' && $idSelJournee > 0) {
      $lstJournee = $idSelJournee;
    }

    $idEvenement = utyGetSession('idEvenement', -1);
    $idEvenement = utyGetGet('idEvenement', $idEvenement);
    if (utyGetGet('idEvenement', 0) > 0) {
      $lstJournee = [];
      $sql = "SELECT Id_journee 
                FROM kp_evenement_journee 
                WHERE Id_evenement = ? ";
      $result = $myBdd->pdo->prepare($sql);
      $result->execute(array($idEvenement));
      while ($row = $result->fetch()) {
        $lstJournee[] = $row['Id_journee'];
      }
    } else {
      $lstJournee = explode(',', $lstJournee);
    }
    $codeSaison = $myBdd->GetActiveSaison();
    $codeSaison = utyGetGet('S', $codeSaison);
    $orderMatchs = 'ORDER BY a.Date_match, d.Lieu, a.Heure_match, a.Terrain';
    $laCompet = utyGetSession('codeCompet', 0);
    $laCompet = utyGetGet('Compet', $laCompet);
    // Ne vider $lstJournee que si $laCompet est une VRAIE compétition
    // Pas *, pas 0, pas vide
    if ($laCompet != 0 && $laCompet != '*' && $laCompet != '') {
      $idEvenement = -1;
    }
    $codeCompet = $laCompet;
    if ($lstJournee != []) {
      $in  = str_repeat('?,', count($lstJournee) - 1) . '?';
      $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, 
                a.Validation, d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, 
                cp.Soustitre2 
                FROM kp_competition cp, kp_journee d, kp_match a 
                LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND d.Code_competition = cp.Code 
                AND d.Code_saison = cp.Code_saison 
                AND a.Id_journee IN ($in) ";
      $merge = $lstJournee;
      if ($filtreJour != '') {
        $sql .= "AND a.Date_match = ? ";
        $merge = array_merge($merge, [$filtreJour]);
      }
      if ($filtreTerrain != '') {
        $sql .= "AND a.Terrain = ? ";
        $merge = array_merge($merge, [$filtreTerrain]);
      }
      if ($filtreTour != '') {
        $sql .= "AND d.Etape = ? ";
        $merge = array_merge($merge, [$filtreTour]);
      }
      if ($filtreMatchsNonVerrouilles == 'on') {
        $sql .= "AND a.Validation = 'N' ";
      }
      $sql .= $orderMatchs;
      $result = $myBdd->pdo->prepare($sql);
      $result->execute($merge);
    } else {
      $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, 
                a.Validation, d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, 
                cp.Soustitre2 
                FROM kp_competition cp, kp_journee d, kp_match a 
                LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND d.Code_competition = cp.Code 
                AND d.Code_saison = cp.Code_saison 
                AND d.Code_competition = ? 
                AND d.Code_saison = ? ";
      $merge = array($laCompet, $codeSaison);
      if ($filtreJour != '') {
        $sql .= "AND a.Date_match = ? ";
        $merge = array_merge($merge, [$filtreJour]);
      }
      if ($filtreTerrain != '') {
        $sql .= "AND a.Terrain = ? ";
        $merge = array_merge($merge, [$filtreTerrain]);
      }
      if ($filtreTour != '') {
        $sql .= "AND d.Etape = ? ";
        $merge = array_merge($merge, [$filtreTour]);
      }
      if ($filtreMatchsNonVerrouilles == 'on') {
        $sql .= "AND a.Validation = 'N' ";
      }
      $sql .= $orderMatchs;
      $result = $myBdd->pdo->prepare($sql);
      $result->execute($merge);
    }

    $resultarray = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultarray as $key => $row1) {
      $lastCompetEvt = $row1['Code_competition'];
    }
    $Oldrupture = "";
    // Chargement des infos de l'évènement ou de la compétition
    $titreEvenementCompet = '';
    if ($idEvenement != -1) {
      $libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
      $titreEvenementCompet = 'Evénement : ' . $libelleEvenement;
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
    }

    $visuels = utyGetVisuels($arrayCompetition, FALSE);


    // Entête PDF ...	  
        $pdf = new MyPDF('L');
    $pdf->SetTitle("Game table");
    $pdf->SetAuthor("Kayak-polo.info");
    $pdf->SetCreator("Kayak-polo.info with FPDF");

    // Construire le header HTML pour affichage sur toutes les pages
    $headerHTML = '<div style="text-align: center;">';

    // Bandeau
    if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
      $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
      $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
    } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
      // KPI + Logo
      $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
      $headerHTML .= '<table width="100%"><tr>';
      $headerHTML .= '<td width="33%" align="left"><img src="img/CNAKPI_small.jpg" style="height: 20mm;" /></td>';
      $headerHTML .= '<td width="34%"></td>';
      $headerHTML .= '<td width="33%" align="right"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></td>';
      $headerHTML .= '</tr></table>';
    } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
      // KPI seul
      $headerHTML .= '<img src="img/CNAKPI_small.jpg" style="height: 20mm;" />';
    } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
      // Logo seul
      $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
      $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
    }

    $headerHTML .= '</div>';
    $pdf->SetHTMLHeader($headerHTML);

    // Construire le footer HTML pour affichage sur toutes les pages
    $footerHTML = '';

    // Sponsor d'abord (en haut du footer)
    if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
      $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
      $footerHTML .= '<div style="text-align: center;"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></div>';
    }

    // Page number et date en dessous (plus près du bord bas)
    $footerHTML .= '<table width="100%" style="font-family: Arial; font-size: 8pt; font-style: italic;"><tr>';
    $footerHTML .= '<td width="50%" align="left">Page {PAGENO}</td>';
    $footerHTML .= '<td width="50%" align="right">Print: ' . date("Y-m-d H:i") . '</td>';
    $footerHTML .= '</tr></table>';

    $pdf->SetHTMLFooter($footerHTML);

    if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
      $pdf->SetAutoPageBreak(true, 30);  // Marge basse pour footer sponsor + page/date
    } else {
      $pdf->SetAutoPageBreak(true, 20);  // Marge basse pour footer simple (page/date uniquement)
    }

    // Configurer les marges pour éviter chevauchement avec header/footer
    $pdf->SetTopMargin(30);  // Marge haute pour laisser place au bandeau/logo

    // QRCode (optionnel - commenté dans l'original)
    // $qrcode = new QRcode('https://www.kayak-polo.info/Journee.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L');
    // $qrcode->displayFPDF($pdf, 265, 9, 21);

    $titreDate = "Season " . $codeSaison;
    // titre
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(137, 5, $titreEvenementCompet, 0, 0, 'L');
    $pdf->Cell(136, 5, $titreDate, 0, 1, 'R');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(273, 6, "Game list", 0, 1, 'C');
    $pdf->Ln(3);

    // Initialiser $tab pour éviter "Undefined variable" si aucun résultat
    $tab = [];

    foreach ($resultarray as $key => $row) {
      if ($row['Soustitre2'] != '') {
        $row['Code_competition'] = $row['Soustitre2'];
      }
      if ($row['Libelle'] != '') {
        $libelle = explode(']', $row['Libelle']);
        if ($libelle[1] != '') {
          $row['Phase'] .= "  |  " . $libelle[1];
        }
      }

      $datematch = $row['Date_match'];
      $heure = $row['Heure_match'];
      $terrain = $row['Terrain'];

      $tab[$datematch][$heure][$terrain][] = $row;
    }

    foreach ($tab as $date => $tab_heure) {
      $pdf->AddPage();

      $pdf->SetFillColor(220, 220, 220);
      $pdf->SetFont('Arial', 'B', 7);
      $date = date_create($date);
      $date = date_format($date, 'l Y-m-d');
      $pdf->Cell(30, 5, $date, 0, 1, 'L');

      $pdf->Cell(10, 5, '', 0, 0, 'L');
      $pdf->Cell(67, 5, 'Pitch 5', 1, 0, 'C', 1);
      $pdf->Cell(67, 5, 'Pitch 6', 1, 0, 'C', 1);
      $pdf->Cell(67, 5, 'Pitch 7', 1, 0, 'C', 1);
      $pdf->Cell(67, 5, 'Pitch 8', 1, 1, 'C', 1);

      $pdf->Cell(10, 5, 'Time', 1, 0, 'C', 1);

      $pdf->Cell(7, 5, '#', 1, 0, 'C', 1);
      $pdf->Cell(14, 5, 'Cat.', 1, 0, 'C', 1);
      $pdf->Cell(46, 5, 'Phase', 1, 0, 'C', 1);
      //            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

      $pdf->Cell(7, 5, '#', 1, 0, 'C', 1);
      $pdf->Cell(14, 5, 'Cat.', 1, 0, 'C', 1);
      $pdf->Cell(46, 5, 'Phase', 1, 0, 'C', 1);
      //            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

      $pdf->Cell(7, 5, '#', 1, 0, 'C', 1);
      $pdf->Cell(14, 5, 'Cat.', 1, 0, 'C', 1);
      $pdf->Cell(46, 5, 'Phase', 1, 0, 'C', 1);
      //            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

      $pdf->Cell(7, 5, '#', 1, 0, 'C', 1);
      $pdf->Cell(14, 5, 'Cat.', 1, 0, 'C', 1);
      $pdf->Cell(46, 5, 'Phase', 1, 1, 'C', 1);
      //            $pdf->Cell(17,5, 'Arbitre',1,1,'C');

      foreach ($tab_heure as $heure => $tab_terrain) {

        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(10, 5, $heure, 1, '0', 'C');

        for ($i = 5; $i <= 8; $i++) {
          if ($i == 8) {
            $findeligne = 1;
          } else {
            $findeligne = 0;
          }
          //                    echo '<pre>' . var_dump($tab_terrain) . '</pre>';
          if (isset($tab_terrain[$i]) && $tab_terrain[$i][0]['Phase'] !== 'Break' && $tab_terrain[$i][0]['Phase'] !== 'Pause') {
            $pdf->Cell(7, 5, $tab_terrain[$i][0]['Numero_ordre'], 1, 0, 'C', 1);
            $pdf->Cell(14, 5, $tab_terrain[$i][0]['Code_competition'], 1, 0, 'C');
            $pdf->Cell(46, 5, $tab_terrain[$i][0]['Phase'], 1, $findeligne, 'C');
          } else {
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(67, 5, 'Break', 1, $findeligne, 'C', 1);
          }
        }
      }
    }



    $pdf->Cell(271, 3, '', 'T', '1', 'C');
    $pdf->Output('GameTable-Phases.pdf', \Mpdf\Output\Destination::INLINE);
  }
}

$page = new PdfListeMatchs();
