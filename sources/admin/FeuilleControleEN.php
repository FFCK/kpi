<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require_once('../lib/fpdf/fpdf.php');

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
        $this->Cell(135, 10, date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'])), 0, 0, 'R');
    }
}

// liste des présents par équipe
class FeuilleControle extends MyPage
{

    function __construct()
    {
        parent::__construct();

        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet');
        $codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
        $equipe = utyGetGet('equipe', '%');

        // Chargement des équipes ...
        $arrayEquipe = array();
        $arrayJoueur = array();
        $arrayCompetition = array();

        $controlStatus = [
            1 => 'OK',
            2 => 'Cosmétique',
            3 => 'Securité',
            4 => 'Technique'
        ];

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

            $sql2 = "SELECT a.Matric, a.Nom, a.Prenom, a.Categ, a.Numero, 
                a.Capitaine, b.Origine, b.Reserve,
                s.kayak_status Kayak, s.vest_status Gilet, s.helmet_status Casque, s.paddle_count Pagaies
                FROM kp_competition_equipe_joueur a 
                LEFT OUTER JOIN kp_licence b ON (a.Matric = b.Matric) 
                LEFT OUTER JOIN kp_scrutineering s ON (a.Matric = s.Matric AND a.Id_Equipe = s.id_equipe) 
                WHERE a.Id_Equipe = ? 
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

                        $capitaine = $row2['Capitaine'];
                        if (strlen($capitaine) == 0) {
                            $capitaine = '-';
                        }

                        if ($row2['Origine'] != $codeSaison) {
                            $row2['Origine'] = ' (' . $row2['Origine'] . ')';
                        } else {
                            $row2['Origine'] = '';
                        }

                        array_push($arrayJoueur[$idEquipe], array(
                            'Matric' => $row2['Matric'], 'Nom' => mb_strtoupper($row2['Nom']), 'Prenom' => mb_convert_case(strtolower($row2['Prenom']), MB_CASE_TITLE, "UTF-8"),
                            'Categ' => $row2['Categ'], 'Numero' => $numero, 'Capitaine' => $capitaine, 
                            'Saison' => $row2['Origine'], 'Numero_club' => $row2['Numero_club'], 'Reserve' => $row2['Reserve'],
                            'Kayak' => $row2['Kayak'], 'Gilet' => $row2['Gilet'], 'Casque' => $row2['Casque'], 'Pagaies' => $row2['Pagaies'],
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
        if ($arrayCompetition['Titre_actif'] == 'O') {
            $titreCompet = $arrayCompetition['Libelle'];
        } else {
            $titreCompet = $arrayCompetition['Soustitre'];
        }
        if ($arrayCompetition['Soustitre2'] != '') {
            $titreCompet .= ' - ' . $arrayCompetition['Soustitre2'];
        }

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Entête PDF ...	  
        $pdf = new PDF('L');
        $pdf->Open();
        $pdf->SetTitle("Control Form " . $arrayCompetition['Soustitre2']);

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
            $pdf->Cell(137, 8, $titreCompet, 0, 0, 'L');
            $pdf->Cell(136, 8, $codeSaison, 0, 1, 'R');
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(273, 8, "Control Form - " . $row['Libelle'], 0, 1, 'C');
            $pdf->Ln(2);

            $idEquipe = $row['Id'];

            $pdf->SetFont('Arial', 'BI', 10);
            $pdf->Cell(15, 9, '', '', 0, 'C');
            $pdf->Cell(16, 9, 'Num', 'B', 0, 'C');
            $pdf->Cell(8, 9, 'Cap', 'B', 0, 'C');
            $pdf->Cell(25, 9, 'ID', 'B', 0, 'C');
            $pdf->Cell(45, 9, 'Family Name', 'B', 0, 'C');
            $pdf->Cell(45, 9, 'Given Name', 'B', 0, 'C');
            $pdf->Cell(25, 9, 'Boat', 'B', 0, 'C');
            $pdf->Cell(25, 9, 'Vest', 'B', 0, 'C');
            $pdf->Cell(25, 9, 'Helmet', 'B', 0, 'C');
            $pdf->Cell(32, 9, 'Paddle count', 'B', 1, 'C');
            $pdf->SetFont('Arial', '', 10);

            // Mini 12 lignes par équipe
            if (isset($arrayJoueur[$idEquipe][0]) && $arrayJoueur[$idEquipe][0]['nbJoueurs'] > 10) {
                $nbJoueurs = $arrayJoueur[$idEquipe][0]['nbJoueurs'] + 2;
            } else {
                $nbJoueurs = 12;
            }

            for ($j = 0; $j < $nbJoueurs; $j++) {
                if (isset($arrayJoueur[$idEquipe][$j]['Matric']) && $arrayJoueur[$idEquipe][$j]['Matric'] != '') {
                    if ($arrayJoueur[$idEquipe][$j]['Matric'] >= 2000000) {
                        if ($arrayJoueur[$idEquipe][$j]['Reserve'] == '0') {
                            $arrayJoueur[$idEquipe][$j]['Matric'] = '';
                        } else {
                            $arrayJoueur[$idEquipe][$j]['Matric'] = $arrayJoueur[$idEquipe][$j]['Reserve'];
                        }
                    }
                    if ($arrayJoueur[$idEquipe][$j]['Numero'] == 11) {
                        $pdf->Cell(15, 9, '', '', 0, 'C');
                        $pdf->Cell(139, 9, 'Team Staff', 'B', 1, 'L');
                    }
                    $pdf->Cell(15, 9, '', '', 0, 'C');
                    if ($arrayJoueur[$idEquipe][$j]['Capitaine'] == 'E') {
                        $pdf->Cell(16, 9, '', 'B', 0, 'C');
                        $pdf->Cell(8, 9, '', 'B', 0, 'C');
                        $pdf->Cell(25, 9, $arrayJoueur[$idEquipe][$j]['Matric'], 'B', 0, 'C');
                        $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Nom'], 'B', 0, 'C');
                        $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Prenom'], 'B', 1, 'C');
                    } else {
                        $pdf->Cell(16, 9, $arrayJoueur[$idEquipe][$j]['Numero'], 'B', 0, 'C');
                        $pdf->Cell(8, 9, $arrayJoueur[$idEquipe][$j]['Capitaine'], 'B', 0, 'C');
                        $pdf->Cell(25, 9, $arrayJoueur[$idEquipe][$j]['Matric'], 'B', 0, 'C');
                        $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Nom'], 'B', 0, 'C');
                        $pdf->Cell(45, 9, $arrayJoueur[$idEquipe][$j]['Prenom'], 'B', 0, 'C');
                        $pdf->Cell(25, 9, $controlStatus[$arrayJoueur[$idEquipe][$j]['Kayak']], 'B', 0, 'C');
                        $pdf->Cell(25, 9, $controlStatus[$arrayJoueur[$idEquipe][$j]['Gilet']], 'B', 0, 'C');
                        $pdf->Cell(25, 9, $controlStatus[$arrayJoueur[$idEquipe][$j]['Casque']], 'B', 0, 'C');
                        $pdf->Cell(32, 9, $arrayJoueur[$idEquipe][$j]['Pagaies'], 'B', 1, 'C');
                    }
                }
            }
        }
        $pdf->Output('Control_Form_' . $arrayCompetition['Soustitre2'] . '.pdf', 'I');
    }
}

$page = new FeuilleControle();
