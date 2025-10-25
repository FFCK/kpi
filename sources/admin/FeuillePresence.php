<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
require_once('../commun/MyPDF.php');

// Pieds de page
class PDF extends MyPDF
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
        $this->Cell(135, 10, date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'])), 0, 0, 'R');
    }
}

// liste des présents par équipe
class FeuillePresence extends MyPage
{
    function __construct()
    {
        parent::__construct();

        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        $codeSaison = $myBdd->GetActiveSaison();
        $equipe = utyGetGet('equipe', '%');

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
            $result->execute(array($codeCompet, $codeSaison, $equipe));
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
        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $titreCompet = ($arrayCompetition['Titre_actif'] == 'O')
            ? $arrayCompetition['Libelle']
            : $arrayCompetition['Soustitre'];
        if ($arrayCompetition['Soustitre2'] != '') {
            $titreCompet .= ' - ' . $arrayCompetition['Soustitre2'];
        }

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Création PDF avec MyPDF (mPDF wrapper)
        $pdf = new MyPDF('L');
        $pdf->SetTitle("Feuille de présence");
        $pdf->SetAuthor("Kayak-polo.info");
        $pdf->SetCreator("Kayak-polo.info avec mPDF");

        $yStart = 10;

        foreach ($resultarray as $row) {
            $pdf->SetTopMargin($yStart);
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(false);

            // Bandeau/logo/sponsor
            if (($arrayCompetition['Bandeau_actif'] ?? '') == 'O' && isset($visuels['bandeau'])) {
                $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O' && ($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
                $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O') {
                $pdf->Image('../img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            } elseif (($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
                $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }

            // Correction : placer le sponsor EN BAS de la page courante, pas sur une nouvelle page
            if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
                $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
                // Place sponsor at bottom of current page (y=267 for A4 landscape)
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
            $pdf->Cell(137, 8, $titreCompet, 0, 0, 'L');
            $pdf->Cell(136, 8, 'Saison ' . $codeSaison, 0, 1, 'R');
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(273, 8, "Feuille de présence - " . $row['Libelle'], 0, 1, 'C');
            $pdf->Ln(4);

            $idEquipe = $row['Id'];

            $pdf->SetFont('Arial', 'BI', 10);
            $pdf->Cell(25, 9, '', '', 0, 'C');
            $pdf->Cell(16, 9, 'Num', 'B', 0, 'C');
            $pdf->Cell(8, 9, 'Cap', 'B', 0, 'C');
            $pdf->Cell(25, 9, 'Licence', 'B', 0, 'C');
            $pdf->Cell(45, 9, 'Nom', 'B', 0, 'C');
            $pdf->Cell(45, 9, 'Prenom', 'B', 0, 'C');
            $pdf->Cell(16, 9, 'Categ', 'B', 0, 'C');
            $pdf->Cell(16, 9, 'Pag. EC', 'B', 0, 'C');
            $pdf->Cell(23, 9, 'Certif. comp.', 'B', 0, 'C');
            $pdf->Cell(16, 9, 'Club', 'B', 0, 'C');
            $pdf->Cell(16, 9, 'Arb', 'B', 1, 'C');
            $pdf->SetFont('Arial', '', 10);

            $nbJoueurs = $arrayJoueur[$idEquipe][0]['nbJoueurs'];

            for ($j = 0; $j < $nbJoueurs; $j++) {
                if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {
                    if ($arrayJoueur[$idEquipe][$j]['Matric'] >= 2000000) {
                        if ($arrayJoueur[$idEquipe][$j]['Reserve'] == '0') {
                            $arrayJoueur[$idEquipe][$j]['Matric'] = '';
                        } else {
                            $arrayJoueur[$idEquipe][$j]['Matric'] = $arrayJoueur[$idEquipe][$j]['Reserve'];
                        }
                    }
                    $pdf->Cell(25, 9, '', '', 0, 'C');
                    $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(8, 9, $arrayJoueur[$idEquipe][$j]['Capitaine'], 'B', 0, 'C');
                    $pdf->Cell(25, 9, $arrayJoueur[$idEquipe][$j]['Matric'] . $arrayJoueur[$idEquipe][$j]['Saison'], 'B', 0, 'C');
                    $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Categ'], 'B', 0, 'C');
                    $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Pagaie'], 'B', 0, 'C');
                    $pdf->Cell(23, 9, $arrayJoueur[$idEquipe][$j]['CertifCK'], 'B', 0, 'C');
                    $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Numero_club'], 'B', 0, 'C');
                    $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Arbitre'], 'B', 1, 'C');
                }
            }
        }

        // Footer HTML pour numéro de page à gauche et date/heure à droite
        $footerHTML = '<table width="100%" style="font-family:Arial;font-size:8pt;font-style:italic;margin-top:2mm;"><tr>'
            . '<td align="left" width="50%">Page {PAGENO}</td>'
            . '<td align="right" width="50%">' . date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')) . '</td>'
            . '</tr></table>';
        $pdf->SetHTMLFooter($footerHTML);

        $pdf->Output('FeuillePresence.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuillePresence();
