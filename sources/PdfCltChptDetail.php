<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');
// QRcode class is now autoloaded via Composer

// Gestion de la Feuille de Classement avec Détails par Équipe - Migré vers mPDF
class FeuilleCltNiveau extends MyPage
{
	function __construct()
	{
		parent::__construct();
		$myBdd = new MyBdd();

		$codeCompet = utyGetSession('codeCompet', '');
		//Saison
		$codeSaison = $myBdd->GetActiveSaison();
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

		$visuels = utyGetVisuels($arrayCompetition, FALSE);

		// Langue
		$langue = parse_ini_file("commun/MyLang.ini", true);
		// PHP 8 fix: Initialize En_actif to avoid undefined array key
		$arrayCompetition['En_actif'] = '';
		if (utyGetGet('lang') == 'en') {
			$arrayCompetition['En_actif'] = 'O';
		}

		if ($arrayCompetition['En_actif'] == 'O') {
			$lang = $langue['en'];
		} else {
			$lang = $langue['fr'];
		}

		//Création avec MyPDF (wrapper mPDF compatible FPDF)
		$pdf = new MyPDF('P');
		$pdf->SetTitle("Detail par equipe");
		$pdf->SetAuthor("kayak-polo.info");
		$pdf->SetCreator("kayak-polo.info avec mPDF");

		// Construire le header HTML pour affichage sur toutes les pages
		$headerHTML = '<div style="text-align: center;">';

		// Bandeau
		if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
			$img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
			$headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
		} elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
			// KPI + Logo
			$img = redimImage($visuels['logo'], 210, 10, 16, 'R');
			$headerHTML .= '<table width="100%"><tr>';
			$headerHTML .= '<td width="33%" align="left"><img src="img/CNAKPI_small.jpg" style="height: 16mm;" /></td>';
			$headerHTML .= '<td width="34%"></td>';
			$headerHTML .= '<td width="33%" align="right"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></td>';
			$headerHTML .= '</tr></table>';
		} elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
			// KPI seul
			$headerHTML .= '<img src="img/CNAKPI_small.jpg" style="height: 16mm;" />';
		} elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
			// Logo seul
			$img = redimImage($visuels['logo'], 210, 10, 16, 'C');
			$headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
		}

		$headerHTML .= '</div>';
		$pdf->SetHTMLHeader($headerHTML);

		// Construire le footer HTML pour affichage sur toutes les pages
		if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
			$img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
			$footerHTML = '<div style="text-align: center;"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></div>';
			$pdf->SetHTMLFooter($footerHTML);
		}

		// Configurer les marges pour éviter chevauchement avec header/footer
		$pdf->SetTopMargin(30);  // Marge haute pour laisser place au bandeau/logo

		$pdf->AddPage();

		// Pattern 8: Désactiver AutoPageBreak temporairement pour QRCode
		$pdf->SetAutoPageBreak(false);

		// QRCode en bas à droite - displayFPDF fonctionne avec MyPDF !
		$qrcode = new QRcode('https://www.kayak-polo.info/Classements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L');
		$qrcode->displayFPDF($pdf, 177, 240, 24);

		// Pattern 8: Réactiver AutoPageBreak avec marges appropriées
		if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
			$pdf->SetAutoPageBreak(true, 30);  // Marge basse pour footer sponsor
		} else {
			$pdf->SetAutoPageBreak(true, 15);
		}

		// titre - le curseur est déjà positionné par TopMargin
		$pdf->Ln(2);

		$pdf->SetFont('Arial', 'B', 14);
		if ($arrayCompetition['Titre_actif'] == 'O') {
			$pdf->Cell(190, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
		} else {
			$pdf->Cell(190, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
		}
		if ($arrayCompetition['Soustitre2'] != '') {
			$pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
		}
		$pdf->Ln(4);
		$pdf->SetFont('Arial', 'BI', 10);
		$pdf->Cell(190, 5, $lang['DETAIL_PAR_EQUIPE'], 0, 0, 'C');
		$pdf->Ln(10);

		//données
		$sql = "SELECT Id, Libelle, Code_club, Clt_publi, Pts_publi, J_publi,
			G_publi, N_publi, P_publi, F_publi, Plus_publi, Moins_publi, Diff_publi,
			PtsNiveau_publi, CltNiveau_publi
			FROM kp_competition_equipe
			WHERE Code_compet = ?
			AND Code_saison = ?
			AND CltNiveau_publi != 0
			ORDER BY Clt_publi ASC, Diff_publi DESC ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));

		while ($row = $result->fetch()) {
			$idEquipe = $row['Id'];
			$pdf->SetFont('Arial', 'B', 12);
			$pdf->Cell(55, 6, '', 0, '0', 'L');

			// médailles - Pattern 5: Sauvegarder position
			if ($row['Clt_publi'] <= 3 && $row['Clt_publi'] != 0 && $arrayCompetition['Code_tour'] == 'F') {
				$savedY = $pdf->y;
				$savedX = $pdf->x;
				$pdf->image('img/medal' . $row['Clt_publi'] . '.gif', $pdf->x, $pdf->y + 1, 3, 3);
				$pdf->SetY($savedY);
				$pdf->SetX($savedX);
			}
			$pdf->Cell(30, 6, $row['Clt_publi'] . '.', 0, '0', 'C');

			// drapeaux - Pattern 5: Sauvegarder position
			if ($arrayCompetition['Code_niveau'] == 'INT') {
				$pays = substr($row['Code_club'], 0, 3);
				if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
					$pays = 'FRA';
				}
				$savedY = $pdf->y;
				$savedX = $pdf->x;
				$pdf->image('img/Pays/' . $pays . '.png', $pdf->x, $pdf->y + 1, 7, 4);
				$pdf->SetY($savedY);
				$pdf->SetX($savedX);
				$pdf->Cell(10, 6, '', 0, '0', 'C'); //Pays
			} else {
				$pdf->Cell(10, 6, '', 0, '0', 'C');
			}

			$pdf->Cell(60, 6, $row['Libelle'], 0, 1, 'L');

			//Détail des matchs
			$sql2 = "SELECT a.Id_equipeA, a.ScoreA, c.Libelle LibelleA,
				a.Id_equipeB, a.ScoreB, d.Libelle LibelleB, a.Id, a.Id_journee,
				a.Validation, b.Date_debut, b.Lieu
				FROM kp_journee b, kp_match a
				LEFT OUTER JOIN kp_competition_equipe c ON (c.Id = a.Id_equipeA)
				LEFT OUTER JOIN kp_competition_equipe d ON (d.Id = a.Id_equipeB)
				WHERE a.Id_journee = b.Id
				AND b.Code_competition = ?
				AND b.Code_saison = ?
				AND (a.Id_equipeA = ? OR a.Id_equipeB = ?)
				AND a.Publication = 'O'
				ORDER BY b.Date_debut, b.Lieu ";
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute(array($codeCompet, $codeSaison, $idEquipe, $idEquipe));

			$oldId_journee = '';
			$pdf->SetFont('Arial', 'B', 10);

			while ($row2 = $result2->fetch()) {
				if (($row2['ScoreA'] == '') || ($row2['ScoreA'] == '?')) {
					continue;
				} // Score non valide ...
				if (($row2['ScoreB'] == '') || ($row2['ScoreB'] == '?')) {
					continue;
				} // Score non valide ...
				$Id_journee = $row2['Id_journee'];
				if ($Id_journee != $oldId_journee) {
					// Rupture journée ...
					$oldId_journee = $Id_journee;
					$pdf->Ln(2);
					$pdf->SetFont('Arial', 'BI', 10);
					$pdf->Cell(190, 5, utyDateUsToFr($row2['Date_debut']) . ' - ' . $row2['Lieu'], 0, 1, 'C');
				}
				if ($row2['Validation'] != 'O') {
					$pdf->SetFont('Arial', '', 9);
					$pdf->Cell(89, 4, $row2['LibelleA'], 0, 0, 'R');
					$pdf->Cell(5, 4, '', 0, 0, 'C');
					$pdf->Cell(2, 4, '-', 0, 0, 'C');
					$pdf->Cell(5, 4, '', 0, 0, 'C');
					$pdf->Cell(89, 4, $row2['LibelleB'], 0, 1, 'L');
				} else {
					if ($row2['ScoreA'] > $row2['ScoreB']) {
						$pdf->SetFont('Arial', 'B', 9);
					} else {
						$pdf->SetFont('Arial', '', 9);
					}
					$pdf->Cell(89, 4, $row2['LibelleA'], 0, 0, 'R');
					$pdf->Cell(5, 4, $row2['ScoreA'], 0, 0, 'C');
					$pdf->SetFont('Arial', '', 9);
					$pdf->Cell(2, 4, '-', 0, 0, 'C');
					if ($row2['ScoreA'] < $row2['ScoreB']) {
						$pdf->SetFont('Arial', 'B', 9);
					} else {
						$pdf->SetFont('Arial', '', 9);
					}
					$pdf->Cell(5, 4, $row2['ScoreB'], 0, 0, 'C');
					$pdf->Cell(89, 4, $row2['LibelleB'], 0, 1, 'L');
				}
			}
			$pdf->Ln(8);
		}

		$pdf->Output('Détail par équipe ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
	}
}

$page = new FeuilleCltNiveau();
