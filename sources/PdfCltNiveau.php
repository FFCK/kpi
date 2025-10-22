<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');

require_once('lib/qrcode/qrcode.class.php');

// Gestion de la Feuille de Classement

class FeuilleCltNiveau extends MyPage
{
	function __construct()
	{
		parent::__construct();

		$myBdd = new MyBdd();

		$codeCompet = utyGetSession('codeCompet', '');
		$codeCompet = utyGetGet('codeCompet', $codeCompet);
		//Saison
		$codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetGet('S', $codeSaison);
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

		$qualif = (int)($arrayCompetition['Qualifies'] ?? 0);
		$elim = (int)($arrayCompetition['Elimines'] ?? 0);

		$visuels = utyGetVisuels($arrayCompetition, FALSE);

		// Langue
		$langue = parse_ini_file("commun/MyLang.ini", true);
		if (utyGetGet('lang') == 'en') {
			$arrayCompetition['En_actif'] = 'O';
		} elseif (utyGetGet('lang') == 'fr') {
			$arrayCompetition['En_actif'] = '';
		}

		if (($arrayCompetition['En_actif'] ?? '') == 'O') {
			$lang = $langue['en'];
		} else {
			$lang = $langue['fr'];
		}

		//Création
		$pdf = new MyPDF('P');
		$pdf->SetTitle("Classement");

		$pdf->SetAuthor("kayak-polo.info");
		$pdf->SetCreator("kayak-polo.info");
		$pdf->AddPage();

		// Pattern 8: Images d'arrière-plan - Définir la position de départ du contenu
		$yStart = 22;

		// Pattern 8: DESACTIVER AutoPageBreak avant les images
		$pdf->SetAutoPageBreak(false);

		// Bandeau
		if (($arrayCompetition['Bandeau_actif'] ?? '') == 'O' && isset($visuels['bandeau'])) {
			$img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
			$pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
			// KPI + Logo
		} elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O' && ($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
			$pdf->Image('img/CNAKPI_small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
			$img = redimImage($visuels['logo'], 210, 10, 16, 'R');
			$pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
			// KPI
		} elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O') {
			$pdf->Image('img/CNAKPI_small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
			// Logo
		} elseif (($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
			$img = redimImage($visuels['logo'], 210, 10, 16, 'C');
			$pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
		}
		// Sponsor
		if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
			$img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
			$pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
		}

		// QRCode
		$qrcode = new QRcode('https://www.kayak-polo.info/kpclassements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L'); // error level : L, M, Q, H
		//$qrcode->displayFPDF($fpdf, $x, $y, $s, $background, $color);
		$qrcode->displayFPDF($pdf, 177, 238, 24);

		// Pattern 8: REACTIVER AutoPageBreak après les images
		if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
			$pdf->SetAutoPageBreak(true, 30);
		} else {
			$pdf->SetAutoPageBreak(true, 15);
		}

		// Pattern 8: FORCER le curseur à la position de départ du contenu
		$pdf->SetY($yStart);
		$pdf->SetLeftMargin(10);
		$pdf->SetRightMargin(10);
		$pdf->SetX(10);

		// titre
		$pdf->Ln(22);
		$pdf->SetFont('Arial', 'B', 14);
		if (($arrayCompetition['Titre_actif'] ?? '') == 'O') {
			$pdf->Cell(190, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
		} else {
			$pdf->Cell(190, 5, $arrayCompetition['Soustitre'] ?? '', 0, 1, 'C');
		}
		//		$pdf->Ln(4);
		if (($arrayCompetition['Soustitre2'] ?? '') != '') {
			$pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
		}
		$pdf->Ln(4);
		$pdf->SetFont('Arial', 'BI', 10);
		$pdf->Cell(190, 5, $lang['CLASSEMENT_GENERAL'], 0, 0, 'C');
		$pdf->Ln(10);

		//données

		$sql = "SELECT Id, Libelle, Code_club, Clt_publi, Pts_publi, J_publi, 
			G_publi, N_publi, P_publi, F_publi, Plus_publi, Moins_publi, Diff_publi, 
			PtsNiveau_publi, CltNiveau_publi 
			FROM kp_competition_equipe 
			WHERE Code_compet = ?
			AND Code_saison = ? 
			AND CltNiveau_publi != 0 
			ORDER BY CltNiveau_publi ASC, Diff_publi DESC ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		$num_results = $result->rowCount();

		// recalcul des éliminés
		$elim = $num_results - $elim;

		$pdf->SetFont('Arial', 'B', 13);
		$pdf->Cell(55, 6, '', '', 0, 'L');
		$pdf->Cell(30, 6, '#', 0, 0, 'C');
		$pdf->Cell(10, 5, '', 0, '0', 'C'); //Pays
		$pdf->Cell(60, 6, $lang['Equipe'], 0, 1, 'L');
		$pdf->Ln(4);

		$i = 0;
		while ($row = $result->fetch()) {
			$separation = 0;
			//Séparation qualifiés
			if (($i + 1) > $qualif && $qualif != 0) {
				$pdf->Ln(2);
				$qualif = 0;
				$separation = 1;
			}
			//Séparation éliminés
			if (($i + 1) > $elim && $elim != 0) {
				if ($separation != 1) {
					$pdf->Ln(2);
				}
				$elim = 0;
			}

			$pdf->SetFont('Arial', 'B', 12);

			$pdf->Cell(55, 6, '', 0, '0', 'L');
			// médailles
			if ($row['CltNiveau_publi'] <= 3 && $row['CltNiveau_publi'] != 0 && $arrayCompetition['Code_tour'] == 'F') {
				// Pattern 5: Sauvegarder position avant image
				$savedY = $pdf->y;
				$savedX = $pdf->x;
				$pdf->image('./img/medal' . $row['CltNiveau_publi'] . '.gif', $pdf->x, $pdf->y + 1, 3, 3);
				// Pattern 5: Restaurer position après image
				$pdf->SetY($savedY);
				$pdf->SetX($savedX);
			}

			$pdf->Cell(30, 6, $row['CltNiveau_publi'], 0, '0', 'C');

			// drapeaux
			if ($arrayCompetition['Code_niveau'] == 'INT') {
				$pays = substr($row['Code_club'], 0, 3);
				if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
					$pays = 'FRA';
				}
				// Pattern 5: Sauvegarder position avant image
				$savedY = $pdf->y;
				$savedX = $pdf->x;
				$pdf->image('./img/Pays/' . $pays . '.png', $pdf->x, $pdf->y + 1, 7, 4);
				// Pattern 5: Restaurer position après image
				$pdf->SetY($savedY);
				$pdf->SetX($savedX);
				$pdf->Cell(10, 6, '', 0, '0', 'C'); //Pays
			} else {
				$pdf->Cell(10, 6, '', 0, '0', 'C');
			}

			$pdf->Cell(60, 6, $row['Libelle'], 0, 1, 'L');
			$i++;
		}

		// Désactiver AutoPageBreak pour écrire la date en bas de page (comme pour les images Pattern 8)
		$pdf->SetAutoPageBreak(false);

		$pdf->SetFont('Arial', 'I', 8);
		if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
			$pdf->SetXY(165, 285);  // Positionner en dessous du sponsor (qui est à Y=267 + hauteur ~16mm)
		} else {
			$pdf->SetXY(165, 270);
		}
		if ($lang == $langue['en']) {
			$pdf->Write(4, date('Y-m-d H:i', strtotime($_SESSION['tzOffset'] ?? '')));
		} else {
			$pdf->Write(4, date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')));
		}

		$pdf->Output('Classement ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
	}
}

$page = new FeuilleCltNiveau();
