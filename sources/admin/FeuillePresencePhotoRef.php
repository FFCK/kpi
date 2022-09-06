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
                AND a.Capitaine IN ('A')
                ORDER BY Numero, Nom, Prenom ";
      $result2 = $myBdd->pdo->prepare($sql2);

      $arrayJoueur['ref'] = array();
      foreach ($resultarray as $key => $row) {
        $idEquipe = $row['Id'];

        // Chargement des Coureurs ...
        if ($idEquipe != '') {
          $result2->execute(array($idEquipe));
          $num_results2 = $result2->rowCount();

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

            $arrayJoueur['ref'][] = array(
              'Matric' => $row2['Matric'], 'Nom' => mb_strtoupper($row2['Nom']),
              'Prenom' => mb_convert_case(strtolower($row2['Prenom']), MB_CASE_TITLE, "UTF-8"),
              'Numero' => $numero, 'Capitaine' => $capitaine, 'Arbitre' => $row2['arbitre'],
              'Club' => $row['Libelle'],
              'nbJoueurs' => $num_results2
            );
          }
        }
      }
    } else {
      die('Aucune compétition sélectionnée');
    }

    // Tri du tableau :
    array_multisort(array_column($arrayJoueur['ref'], 'Nom'), SORT_ASC, $arrayJoueur['ref']);

    // Chargement des infos de la compétition

    $arrayCompetition = $myBdd->GetCompetition($codeCompet === 'POOL' ? 'CMH' : $codeCompet, $codeSaison === 1000 ? 2022 : $codeSaison);
    if ($arrayCompetition['Titre_actif'] == 'O') {
      $titreCompet = $arrayCompetition['Libelle'];
    } else {
      $titreCompet = $arrayCompetition['Soustitre'];
    }

    $visuels = utyGetVisuels($arrayCompetition, TRUE);

    // Entête PDF ...	  
    $pdf = new PDF('L');
    $pdf->Open();
    $pdf->SetTitle($codeCompet === 'POOL' ? "Referees" : "Team Roster");

    $pdf->SetAuthor("Kayak-polo.info");
    $pdf->SetCreator("Kayak-polo.info avec FPDF");
    if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
      $pdf->SetAutoPageBreak(true, 25);
    } else {
      $pdf->SetAutoPageBreak(true, 15);
    }

    // for ($i = 0; $i < as $key => $row) {
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
      $pdf->Cell(137, 8, $titreCompet, 0, 0, 'L');
      $pdf->Cell(136, 8, 'Referees', 0, 1, 'R');
      $pdf->SetFont('Arial', 'B', 14);
    }
    // $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 9);

    $idEquipe = 'ref';

    $h = 38;
    // $l = 55;
    $l = 68;

    $arrayRefNames = [];
    $k = 0;

    // echo '<pre>';
    // var_dump($resultarray);
    // var_dump($arrayJoueur['ref']);

    for ($j = 1; $j < count($arrayJoueur[$idEquipe]) + 1; $j++) {
      if ($j > 1 && ($j - 1) % 12 === 0) {
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
        $pdf->Ln(25);
      }
      $return = 0;
      if ($j % 4 === 0 || $j === count($arrayJoueur[$idEquipe])) {
        $return = 1;
      }
      if (isset($arrayJoueur[$idEquipe][$j - 1]['Matric']) && $arrayJoueur[$idEquipe][$j - 1]['Matric'] != '') {

        // Photo joueur
        $file = '../img/KIP/players/' . $arrayJoueur[$idEquipe][$j - 1]['Matric'] . '.png';
        if (is_file($file)) {
          $size = getimagesize($file);
          $w = $size[0] / $size[1] * $h;
          $x = $pdf->GetX() + ($l - $w) / 2;
          $pdf->Image($file, $x, $pdf->GetY(), 0, $h);
        }
        $pdf->Cell($l, $h, '', 0, $return, 'C');

        $player = $arrayJoueur[$idEquipe][$j - 1]['Nom']
          . ' ' . $arrayJoueur[$idEquipe][$j - 1]['Prenom']
          . ' (' . $arrayJoueur[$idEquipe][$j - 1]['Club'] . ')';
        $arrayRefNames[$k] = $player;

        if ($return) {
          for ($m = 0; $m < 4; $m++) {
            $pdf->Cell($l, 8, $arrayRefNames[$m], 0, ($m === 3) ? 1 : 0, 'C');
          }
          $k = 0;
          $arrayRefNames = [];
          $pdf->ln(3);
        } else {
          $k++;
        }
      }
    }

    $pdf->Output('Referees.pdf', 'I');
  }
}

$page = new FeuillePresencePhoto();
