<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require_once('../commun/MyPDF.php');

// liste des présents par équipe
class FeuillePresencePhoto extends MyPage
{

  function __construct()
  {
    parent::__construct();

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
                a.Capitaine, b.Origine, b.Numero_club, b.Pagaie_ECA, b.Pagaie_EVI, b.Pagaie_MER, 
                b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, 
                b.Naissance, b.Reserve, c.arbitre, c.niveau 
                FROM kp_competition_equipe_joueur a 
                LEFT OUTER JOIN kp_licence b ON (a.Matric = b.Matric) 
                LEFT OUTER JOIN kp_arbitre c ON (a.Matric = c.Matric) 
                WHERE Id_Equipe = ? 
                ORDER BY Field(IF(a.Capitaine='C', '-', IF(a.Capitaine='', '-', a.Capitaine)), '-', 'E', 'A', 'X'), 
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
            if ($row2['niveau'] != '') {
              $row2['arbitre'] .= '-' . $row2['niveau'];
            }

            $controlePagaie = controle_pagaie($row2['Pagaie_ECA'], $row2['Pagaie_EVI'], $row2['Pagaie_MER']);
            $pagaie = $controlePagaie['pagaie'];
            $PagaieValide = $controlePagaie['PagaieValide'];
            if ($PagaieValide > 1) {
              $pagaie = '(' . $pagaie . ')';
            }

            $capitaine = $row2['Capitaine'];
            if (strlen($capitaine) == 0) {
              $capitaine = '-';
            }
            if ($capitaine === 'E') {
              $capitaine = 'S';
            }

            if (is_null($row2['arbitre'])) {
              $row2['arbitre'] = '';
            }

            if ($row2['Origine'] != $codeSaison) {
              $row2['Origine'] = ' (' . $row2['Origine'] . ')';
            } else {
              $row2['Origine'] = '';
            }

            array_push($arrayJoueur[$idEquipe], array(
              'Matric' => $row2['Matric'], 'Nom' => mb_strtoupper($row2['Nom']), 'Prenom' => mb_convert_case(strtolower($row2['Prenom']), MB_CASE_TITLE, "UTF-8"),
              'Sexe' => $row2['Sexe'], 'Categ' => $row2['Categ'], 'Pagaie' => $pagaie, 'CertifCK' => $row2['CertifCK'],
              'CertifAPS' => $row2['CertifAPS'], 'Numero' => $numero, 'Capitaine' => $capitaine, 'Arbitre' => $row2['arbitre'],
              'Saison' => $row2['Origine'], 'Numero_club' => $row2['Numero_club'],
              'Naissance' => $row2['Naissance'], 'Reserve' => $row2['Reserve'],
              'nbJoueurs' => $num_results2
            ));
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
    if (($arrayCompetition['Titre_actif'] ?? '') == 'O') {
      $titreCompet = $arrayCompetition['Libelle'];
    } else {
      $titreCompet = $arrayCompetition['Soustitre'] ?? '';
    }
    if (($arrayCompetition['Soustitre2'] ?? '') != '') {
      $titreCompet .= ' - ' . $arrayCompetition['Soustitre2'];
    }

    $visuels = utyGetVisuels($arrayCompetition, TRUE);

    // Entête PDF avec MyPDF (mPDF wrapper)
    $pdf = new MyPDF('L');
    $pdf->SetTitle($codeCompet === 'POOL' ? "Referees" : "Team Roster");
    $pdf->SetAuthor("Kayak-polo.info");
    $pdf->SetCreator("Kayak-polo.info avec mPDF");

    $yStart = 10;

    foreach ($resultarray as $key => $row) {
      $pdf->SetTopMargin($yStart);
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(false);

      // Affichage - Bandeau/Logo/Sponsor
      if (($arrayCompetition['Bandeau_actif'] ?? '') == 'O' && isset($visuels['bandeau'])) {
        $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
        $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        // KPI + Logo
      } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O' && ($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
        $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
        $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
        $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        // KPI
      } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O') {
        $pdf->Image('../img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
        // Logo
      } elseif (($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
        $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
        $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
      }
      // Sponsor
      if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
        $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
        $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
      }

      // Réactiver AutoPageBreak avec marge basse adaptée
      if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
        $pdf->SetAutoPageBreak(true, 30);
      } else {
        $pdf->SetAutoPageBreak(true, 15);
      }
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->SetY($yStart);
      $pdf->SetX(10);

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
        $pdf->Cell(136, 8, $codeSaison, 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(273, 8, "Team roster - " . $row['Libelle'], 0, 1, 'C');
      }
      $pdf->Ln(5);

      $idEquipe = $row['Id'];

      $h = 8;

      $pdf->SetFont('Arial', 'BI', 10);
      if ($codeSaison === 1000) {
        $pdf->Cell(24, $h, '', 'B', 0, 'C');
      } else {
        $pdf->Cell(16, $h, '#', 'B', 0, 'C');
        $pdf->Cell(8, $h, 'Cap', 'B', 0, 'C');
      }
      $pdf->Cell(60, $h, 'Family name', 'B', 0, 'C');
      $pdf->Cell(60, $h, 'Given name', 'B', 0, 'C');
      $pdf->Cell(25, $h, 'Birthdate', 'B', 0, 'C');
      $pdf->Cell(60, $h, 'Photo', 'B', 0, 'C');
      $pdf->Cell(26, $h, 'ID', 'B', 0, 'C');
      $pdf->Cell(18, $h, 'Check', 'B', 1, 'C');
      $pdf->SetFont('Arial', '', 10);

      $h = 22;

      // Mini 12 lignes par équipe
      if (isset($arrayJoueur[$idEquipe][0]) && $arrayJoueur[$idEquipe][0]['nbJoueurs'] > 10) {
        $nbJoueurs = $arrayJoueur[$idEquipe][0]['nbJoueurs'] + 2;
      } else {
        $nbJoueurs = 12;
      }

      for ($j = 0; $j < $nbJoueurs; $j++) {
        if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {
          if ($codeSaison === 1000) {
            $pdf->Cell(24, $h, '', 'B', 0, 'C');
          } else {
            $pdf->Cell(16, $h, $arrayJoueur[$idEquipe][$j]['Numero'], 'B', 0, 'C');
            $pdf->Cell(8, $h, $arrayJoueur[$idEquipe][$j]['Capitaine'], 'B', 0, 'C');
          }
          $pdf->Cell(60, $h, $arrayJoueur[$idEquipe][$j]['Nom'], 'B', 0, 'C');
          $pdf->Cell(60, $h, $arrayJoueur[$idEquipe][$j]['Prenom'], 'B', 0, 'C');
          $pdf->Cell(23, $h, $arrayJoueur[$idEquipe][$j]['Naissance'], 'B', 0, 'C');

          // Photo joueur - Pattern 5: sauvegarder/restaurer position
          if (is_file('../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png')) {
            $savedY = $pdf->y;
            $savedX = $pdf->x;
            $x = ($j % 2 === 0) ? $pdf->x + 5 : $pdf->x + 35;
            $pdf->Image('../img/KIP/players/' . $arrayJoueur[$idEquipe][$j]['Matric'] . '.png', $x, $pdf->y - 5, 0, $h + 5);
            $pdf->SetY($savedY);
            $pdf->SetX($savedX);
          }

          $pdf->Cell(60, $h, '', 'B', 0, 'C');
          $pdf->Cell(26, $h, $arrayJoueur[$idEquipe][$j]['Matric'], 'B', 0, 'C');
          $pdf->Cell(20, $h, '[_]', 'B', 1, 'C');
        }
      }
    }
    $pdf->Output('Team_roster.pdf', \Mpdf\Output\Destination::INLINE);
  }
}

$page = new FeuillePresencePhoto();
