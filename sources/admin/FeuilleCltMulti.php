<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
require_once('../commun/MyPDF.php');

// Gestion de la Feuille de Classement - Type MULTI

class FeuilleCltMulti extends MyPage
{
    function __construct()
    {
        parent::__construct();

        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        //Saison
        $codeSaison = $myBdd->GetActiveSaison();
        $titreDate = "Saison " . $codeSaison;

        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

        // PHP 8 fix: Type casting pour éviter "Unsupported operand types: int - string"
        $qualif = (int)($arrayCompetition['Qualifies'] ?? 0);
        $elim = (int)($arrayCompetition['Elimines'] ?? 0);

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        $arrayCompetition['En_actif'] = '';
        if (utyGetGet('lang') == 'en') {
            $arrayCompetition['En_actif'] = 'O';
        }
        if (($arrayCompetition['En_actif'] ?? '') == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        // Création PDF avec MyPDF (mPDF wrapper)
        $pdf = new MyPDF('L');
        $pdf->SetTitle("Classement Multi-Competition");
        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info avec mPDF");

        // Header HTML pour toutes les pages
        $headerHTML = '<div style="text-align: center;">';
        if (($arrayCompetition['Bandeau_actif'] ?? '') == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 265, 10, 20, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O' && ($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 265, 10, 20, 'R');
            $headerHTML .= '<table width="100%"><tr>';
            $headerHTML .= '<td width="33%" align="left"><img src="../img/CNAKPI_small.jpg" style="height: 20mm;" /></td>';
            $headerHTML .= '<td width="34%"></td>';
            $headerHTML .= '<td width="33%" align="right"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></td>';
            $headerHTML .= '</tr></table>';
        } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O') {
            $headerHTML .= '<img src="../img/CNAKPI_small.jpg" style="height: 20mm;" />';
        } elseif (($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 265, 10, 20, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        }
        $headerHTML .= '</div>';
        $pdf->SetHTMLHeader($headerHTML);

        // Footer HTML pour sponsor + date/heure en dessous
        if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
            $footerHTML = '<div style="text-align: center;">'
                . '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /><br/>'
                . '<span style="font-family:Arial;font-size:8pt;font-style:italic;">'
                . (($lang == $langue['en'])
                    ? date('Y-m-d H:i', strtotime($_SESSION['tzOffset'] ?? ''))
                    : date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')))
                . '</span></div>';
            $pdf->SetHTMLFooter($footerHTML);
        } else {
            // Footer HTML simple avec date/heure seule
            $footerHTML = '<div style="text-align:center;font-family:Arial;font-size:8pt;font-style:italic;margin-top:2mm;">'
                . (($lang == $langue['en'])
                    ? date('Y-m-d H:i', strtotime($_SESSION['tzOffset'] ?? ''))
                    : date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')))
                . '</div>';
            $pdf->SetHTMLFooter($footerHTML);
        }

        // Configurer les marges pour éviter chevauchement avec header/footer
        $pdf->SetTopMargin(35);

        $pdf->AddPage();

        // titre
        $pdf->Ln(22);

        $pdf->SetFont('Arial', 'B', 14);
        if (($arrayCompetition['Titre_actif'] ?? '') == 'O') {
            $pdf->Cell(273, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(273, 5, $arrayCompetition['Soustitre'] ?? '', 0, 1, 'C');
        }

        $pdf->Ln(4);
        if (($arrayCompetition['Soustitre2'] ?? '') != '') {
            $pdf->Cell(273, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(273, 5, $lang['CLASSEMENT_PROVISOIRE'] ?? 'Classement provisoire', 0, 0, 'C');
        $pdf->Ln(10);

        //données - Classement MULTI : seulement Clt, Equipe, Pts, J
        $sql = "SELECT Id, Libelle, Code_club, Clt, Pts, J
            FROM kp_competition_equipe
            WHERE Code_compet = ?
            AND Code_saison = ?
            ORDER BY Clt, Pts DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
        $num_results = $result->rowCount();

        // recalcul des éliminés
        $elim = $num_results - $elim;

        $pdf->SetFont('Arial', 'BI', 11);

        // Colonnes réduites pour MULTI : Clt, Equipe, Pts, J
        $pdf->Cell(50, 5, '', 0, 0);
        $pdf->Cell(30, 5, $lang['Clt'] ?? 'Rang', 'B', 0, 'C');
        $pdf->Cell(100, 5, $lang['Equipe'] ?? 'Equipe', 'B', 0, 'L');
        $pdf->Cell(30, 5, $lang['Pts'] ?? 'Points', 'B', 0, 'C');
        $pdf->Cell(30, 5, $lang['J'] ?? 'Joués', 'B', 1, 'C');

        $i = 0;
        while ($row = $result->fetch()) {
            $separation = 0;
            //Séparation qualifiés
            if (($i + 1) > $qualif && $qualif != 0) {
                $pdf->Cell(50, 5, '', 0, 0);
                $pdf->Cell(190, 1, '', 0, 1);
                $qualif = 0;
                $separation = 1;
            }
            //Séparation éliminés
            if (($i + 1) > $elim && $elim != 0) {
                if ($separation != 1) {
                    $pdf->Cell(50, 5, '', 0, 0);
                    $pdf->Cell(190, 1, '', 0, 1);
                }
                $elim = 0;
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

            $pdf->Cell(50, 6, '', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(30, 6, $row['Clt'] . '.', 'B', 0, 'C');
            $pdf->Cell(100, 6, $row['Libelle'], 'B', 0, 'L');
            $pdf->Cell(30, 6, $pts, 'B', 0, 'C');
            $pdf->Cell(30, 6, $row['J'], 'B', 1, 'C');
            $i++;
        }

        $pdf->Output('Classement ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleCltMulti();
