<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require_once('../lib/fpdf-1.7/fpdf.php');

// Pieds de page
class PDF extends FPDF
{

  function Footer()
  {
    //Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    //Police Arial italique 8
    $this->SetFont('Arial', 'I', 8);
    //Numéro de page à gauche
    $this->Cell(135, 10, 'Page ' . $this->PageNo(), 0, 0, 'L');
    //Date à droite
    $this->Cell(135, 10, date('Y-m-d  H:i', strtotime($_SESSION['tzOffset'])), 0, 0, 'R');
  }

  function Cell($w, $h = 0, $txt = "", $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
  {
    if (!empty($txt)) {
      if (mb_detect_encoding($txt, 'UTF-8', false)) {
        $txt = iconv('UTF-8', 'ISO-8859-1', $txt);
      }
    }
    parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
  }
}

// liste des présents par équipe
class FeuillePresencePhoto extends MyPage
{

  function __construct()
  {
    define('FPDF_FONTPATH', 'font/');

    MyPage::MyPage();

    $myBdd = new MyBdd();

    $codeCompet = utyGetSession('codeCompet');
    $codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
    $codeEquipe = utyGetGet('equipe', '%');

    // Chargement des équipes ...
    $arrayEquipe = array();
    $arrayJoueur = array();
    $arrayCompetition = array();

    if (strlen($codeCompet) > 0) {
      $sql = "SELECT Id, Libelle, Code_club, Numero 
                FROM kp_competition_equipe 
                WHERE Code_compet = ? 
                AND Code_saison = ? 
                AND Id LIKE ?
                ORDER BY Libelle, Id ";
      $result = $myBdd->pdo->prepare($sql);
      $result->execute(array($codeCompet, $codeSaison, $codeEquipe));
      $num_results = $result->rowCount();
      if ($num_results == 0) {
        die('Aucune équipe dans cette compétition');
      }
      $resultarray = $result->fetchAll(PDO::FETCH_ASSOC);

      $sql2 = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, 
                a.Capitaine, b.Origine, b.Numero_club,
                b.Naissance, b.Reserve, c.arbitre, c.niveau 
                FROM kp_competition_equipe_joueur a 
                LEFT OUTER JOIN kp_licence b ON (a.Matric = b.Matric) 
                LEFT OUTER JOIN kp_arbitre c ON (a.Matric = c.Matric) 
                WHERE a.Id_Equipe = ?
                AND a.Capitaine NOT IN ('A', 'X')
                ORDER BY Field(IF(a.Capitaine='C', '-', IF(a.Capitaine='', '-', a.Capitaine)), '-', 'E'), 
                Numero, Nom, Prenom ";
      $result2 = $myBdd->pdo->prepare($sql2);

      foreach ($resultarray as $key => $row) {
        $idEquipe = $row['Id'];

        // Chargement des Coureurs ...
        if ($idEquipe != '') {
          $result2->execute(array($idEquipe));
          $num_results2 = $result2->rowCount();
          $arrayJoueur[$idEquipe] = array();

          while ($row2 = $result2->fetch()) {
            $numero = $row2['Numero'];
            if (strlen($numero) == 0) {
              $numero = 0;
            }
            $capitaine = $row2['Capitaine'];
            if (strlen($capitaine) == 0) {
              $capitaine = '-';
            }
            if ($capitaine === 'E') {
              $capitaine = 'S';
            }

            $arrayJoueur[$idEquipe][$numero] = array(
              'Matric' => $row2['Matric'], 'Nom' => mb_strtoupper($row2['Nom']), 'Prenom' => mb_convert_case(strtolower($row2['Prenom']), MB_CASE_TITLE, "UTF-8"),
              'Numero' => $numero, 'Capitaine' => $capitaine, 'Arbitre' => $row2['arbitre'],
              'nbJoueurs' => $num_results2
            );
          }
          array_push($arrayEquipe, array(
            'Id' => $row['Id'], 'Libelle' => $row['Libelle'],
            'Code_club' => $row['Code_club'], 'Numero' => $row['Numero']
          ));
        }
      }
    } else {
      die('Aucune compétition sélectionnée');
    }

    // Chargement des infos de la compétition

    $arrayCompetition = $myBdd->GetCompetition($codeCompet === 'POOL' ? 'CMH' : $codeCompet, $codeSaison);
    if ($arrayCompetition['Titre_actif'] == 'O') {
      $titreCompet = $arrayCompetition['Libelle'];
    } else {
      $titreCompet = $arrayCompetition['Soustitre'];
    }
    if ($arrayCompetition['Soustitre2'] != '') {
      $titreCompet .= ' - ' . $arrayCompetition['Soustitre2'];
    }

    $visuels = utyGetVisuels($arrayCompetition, TRUE);

    // echo '<pre>';
    // var_dump($arrayJoueur);

    // Entête PDF ...	  
    $pdf = new PDF('L');
    $pdf->Open();
    $pdf->SetTitle($codeCompet === 'POOL' ? "Referees" : "Team Roster");

    $pdf->SetAuthor("Kayak-polo.info");
    $pdf->SetCreator("Kayak-polo.info avec FPDF");
    if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
      $pdf->SetAutoPageBreak(true, 30);
    } else {
      $pdf->SetAutoPageBreak(true, 15);
    }

    foreach ($resultarray as $key => $row) {
      $pdf->AddPage();
      // Affichage
      // Bandeau
      if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
        $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
        $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        // KPI + Logo    
      } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
        $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
        $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
        $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        // KPI
      } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
        $pdf->Image('../img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
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

      // titre
      $pdf->Ln(20);
      $pdf->SetFont('Arial', 'BI', 12);
      if ($codeSaison === 1000) {
        $pdf->Cell(137, 8, 'Referees', 0, 0, 'L');
        $pdf->Cell(136, 8, '', 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(273, 8, $row['Libelle'], 0, 1, 'C');
      } else {
        $pdf->Cell(137, 8, $titreCompet, 0, 0, 'L');
        $pdf->Cell(136, 8, $row['Libelle'], 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);
      }
      $pdf->Ln(5);

      $idEquipe = $row['Id'];

      $pdf->SetFont('Arial', '', 9);

      $h = 38;

      $pdf->Cell(2, $h, '', 0, 0, 'C');
      for ($j = 1; $j <= 5; $j++) {
        $return = ($j % 5 === 0) ? 1 : 0;
        if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {

          // Photo joueur
          $file = '../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png';
          if (is_file($file)) {
            $size = getimagesize($file);
            $w = $size[0] / $size[1] * $h;
            $x = $pdf->GetX() + (54 - $w) / 2;
            $pdf->Image('../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png', $x, $pdf->GetY(), 0, $h);
          }
          $pdf->Cell(54, $h, '', 0, $return, 'C');
        } else {
          $pdf->Cell(54, $h, '', 0, $return, 'C');
        }
      }
      $pdf->Cell(2, 8, '', 0, 0, 'C');
      for ($j = 1; $j <= 5; $j++) {
        $return = ($j % 5 === 0) ? 1 : 0;
        if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {
          $player = $arrayJoueur[$idEquipe][$j]['Numero']
            . ' - ' . $arrayJoueur[$idEquipe][$j]['Nom']
            . ' ' . $arrayJoueur[$idEquipe][$j]['Prenom'];
          $player .= ($arrayJoueur[$idEquipe][$j]['Capitaine'] === 'C') ? ' (C)' : '';

          $pdf->Cell(54, 8, $player, 0, $return, 'C');
        } else {
          $pdf->Cell(54, 8, '', 0, $return, 'C');
        }
      }

      $pdf->ln(5);

      $pdf->Cell(2, $h, '', 0, 0, 'C');
      for ($j = 6; $j <= 10; $j++) {
        $return = ($j % 5 === 0) ? 1 : 0;
        if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {

          // Photo joueur
          if (is_file('../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png')) {
            $x = $pdf->GetX() + 8;
            $pdf->Image('../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png', $x, $pdf->GetY(), 0, $h);
          }
          $pdf->Cell(54, $h, '', 0, $return, 'C');
        } else {
          $pdf->Cell(54, $h, '', 0, $return, 'C');
        }
      }
      $pdf->Cell(2, 8, '', 0, 0, 'C');
      for ($j = 6; $j <= 10; $j++) {
        $return = ($j % 5 === 0) ? 1 : 0;
        if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {
          $player = $arrayJoueur[$idEquipe][$j]['Numero']
            . ' - ' . $arrayJoueur[$idEquipe][$j]['Nom']
            . ' ' . $arrayJoueur[$idEquipe][$j]['Prenom'];
          $player .= ($arrayJoueur[$idEquipe][$j]['Capitaine'] === 'C') ? ' (C)' : '';

          $pdf->Cell(54, 8, $player, 0, $return, 'C');
        } else {
          $pdf->Cell(54, 8, '', 0, $return, 'C');
        }
      }

      $pdf->ln(5);
      $pdf->SetFont('Arial', '', 10);

      for ($j = 0; $j <= 20; $j++) {
        if (
          isset($arrayJoueur[$idEquipe][$j]['Matric'])
          && $arrayJoueur[$idEquipe][$j]['Matric'] != ''
          && $arrayJoueur[$idEquipe][$j]['Capitaine'] === 'S'
        ) {
          $staff = $arrayJoueur[$idEquipe][$j]['Nom']
            . ' ' . $arrayJoueur[$idEquipe][$j]['Prenom']
            . ' (Team staff)';
          $pdf->Cell(273, 8, $staff, 0, 1, 'C');
        }
      }
    }
    $pdf->Output('Team_roster.pdf', 'I');
  }
}

$page = new FeuillePresencePhoto();
