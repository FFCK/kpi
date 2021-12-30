<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../lib/fpdf/fpdf.php');

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
		$this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
	}
}

// Gestion de la Feuille Instances
class FeuilleInstances extends MyPage
{
	function __construct()
	{
		MyPage::MyPage();
		$myBdd = new MyBdd();
		$idJournee = utyGetGet('idJournee', 0);
		$idJournee = utyGetPost('idJournee', $idJournee);
		//Chargement infos journées
		$sql = "SELECT j.Id, j.Code_competition, j.Code_saison, j.Type, j.Phase, j.Niveau, 
			j.Date_debut, j.Date_fin, j.Nom, j.Libelle, j.Lieu, j.Plan_eau, j.Departement, 
			j.Responsable_insc, j.Responsable_R1, j.Organisateur, j.Delegue, j.ChefArbitre, 
			j.Publication 
			FROM kp_journee j
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		$row = $result->fetch();
		//Saison
		$codeSaison = $row['Code_saison'];
		$titreDate = "Saison " . $codeSaison;
		$codeCompet = $row['Code_competition'];
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$titreCompet = 'Compétition : ' . $arrayCompetition['Libelle'] . ' (' . $codeCompet . ')';
		$qualif = $arrayCompetition['Qualifies'];
		$elim = $arrayCompetition['Elimines'];


		if ($arrayCompetition['BandeauLink'] != '' && strpos($arrayCompetition['BandeauLink'], 'http') === FALSE) {
			$arrayCompetition['BandeauLink'] = '../img/logo/' . $arrayCompetition['BandeauLink'];
			if (is_file($arrayCompetition['BandeauLink'])) {
				$bandeau = $arrayCompetition['BandeauLink'];
			}
		} elseif ($arrayCompetition['BandeauLink'] != '') {
			$bandeau = $arrayCompetition['BandeauLink'];
		}
		if ($arrayCompetition['LogoLink'] != '' && strpos($arrayCompetition['LogoLink'], 'http') === FALSE) {
			$arrayCompetition['LogoLink'] = '../img/logo/' . $arrayCompetition['LogoLink'];
			if (is_file($arrayCompetition['LogoLink'])) {
				$logo = $arrayCompetition['LogoLink'];
			}
		} elseif ($arrayCompetition['LogoLink'] != '') {
			$logo = $arrayCompetition['LogoLink'];
		}

		if ($arrayCompetition['SponsorLink'] != '' && strpos($arrayCompetition['SponsorLink'], 'http') === FALSE) {
			$arrayCompetition['SponsorLink'] = '../img/logo/' . $arrayCompetition['SponsorLink'];
			if (is_file($arrayCompetition['SponsorLink'])) {
				$sponsor = $arrayCompetition['SponsorLink'];
			}
		} elseif ($arrayCompetition['SponsorLink'] != '') {
			$sponsor = $arrayCompetition['SponsorLink'];
		}

		// Langue
		$langue = parse_ini_file("../commun/MyLang.ini", true);
		if (utyGetGet('lang') == 'en') {
			$arrayCompetition['En_actif'] = 'O';
		} elseif (utyGetGet('lang') == 'fr') {
			$arrayCompetition['En_actif'] = '';
		}

		if ($arrayCompetition['En_actif'] == 'O') {
			$lang = $langue['en'];
		} else {
			$lang = $langue['fr'];
		}

		//Création
		$pdf = new FPDF('P');
		$pdf->Open();
		$pdf->SetTitle("Instances");

		$pdf->SetAuthor("Poloweb.org");
		$pdf->SetCreator("Poloweb.org avec FPDF");
		$pdf->AddPage();
		if ($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '') {
			$pdf->SetAutoPageBreak(true, 40);
		} else {
			$pdf->SetAutoPageBreak(true, 15);
		}

		// logo
		if ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
			$pdf->Image('../img/CNAKPI_small.jpg', 84, 10, 0, 20, 'jpg', "http://www.ffck.org");
		}

		if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($bandeau)) {
			$size = getimagesize($bandeau);
			$largeur = $size[0];
			$hauteur = $size[1];
			$ratio = 20 / $hauteur;	//hauteur imposée de 20mm
			$newlargeur = $largeur * $ratio;
			$posi = 105 - ($newlargeur / 2);	//210mm = largeur de page
			$pdf->image($bandeau, $posi, 8, 0, 20);
		} elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($logo)) {
			$size = getimagesize($logo);
			$largeur = $size[0];
			$hauteur = $size[1];
			$ratio = 20 / $hauteur;	//hauteur imposée de 20mm
			$newlargeur = $largeur * $ratio;
			$posi = 105 - ($newlargeur / 2);	//210mm = largeur de page
			$pdf->image($logo, $posi, 8, 0, 20);
		}

		if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor)) {
			$size = getimagesize($sponsor);
			$largeur = $size[0];
			$hauteur = $size[1];
			$ratio = 16 / $hauteur;	//hauteur imposée de 16mm
			$newlargeur = $largeur * $ratio;
			$posi = 105 - ($newlargeur / 2);	//210mm = largeur de page
			$pdf->image($sponsor, $posi, 267, 0, 16);
		}

		// titre
		$pdf->Ln(22);
		$pdf->SetFont('Arial', 'B', 14);
		if ($arrayCompetition['Titre_actif'] == 'O') {
			$pdf->Cell(188, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
		} else {
			$pdf->Cell(188, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
		}
		if ($arrayCompetition['Soustitre2'] != '') {
			$pdf->Cell(188, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
		} else {
			$pdf->Cell(188, 5, '(' . $codeCompet . ')', 0, 1, 'C');
		}
		$pdf->Ln(12);
		// Contenu
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(94, 7, $row['Nom'], 0, 0, 'L');
		$pdf->Cell(94, 7, utyDateUsToFr($row['Date_debut']) . ' - ' . utyDateUsToFr($row['Date_fin']), 0, 1, 'R');
		$pdf->Cell(94, 7, 'Responsable de compétition: ' . utyGetNomPrenom($row['Responsable_insc']), 0, 0, 'L');
		$pdf->Cell(94, 7, $row['Lieu'] . ' (' . $row['Departement'] . ')', 0, 1, 'R');
		$pdf->Ln(12);
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(188, 12, 'Comité de compétition', 0, 1, 'C');
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(94, 10, 'Responsable de l\'organisation (R1): ', 0, 0, 'C');
		$pdf->Cell(94, 10, utyGetNomPrenom($row['Responsable_R1']), 0, 1, 'C');
		$pdf->Cell(94, 10, 'Délégué Commission Nationale d\'Activité: ', 0, 0, 'C');
		$pdf->Cell(94, 10, utyGetNomPrenom($row['Delegue']), 0, 1, 'C');
		$pdf->Cell(94, 10, 'Chef des arbitres: ', 0, 0, 'C');
		$pdf->Cell(94, 10, utyGetNomPrenom($row['ChefArbitre']), 0, 1, 'C');
		$pdf->Ln(20);
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(188, 12, 'Jury d\'Appel', 0, 1, 'C');
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(94, 10, 'Délégué C.N.A (président du Jury): ', 0, 0, 'C');
		$pdf->Cell(94, 10, utyGetNomPrenom($row['Delegue']), 0, 1, 'C');
		$pdf->Cell(94, 10, 'Responsable de l\'organisation (R1): ', 0, 0, 'C');
		$pdf->Cell(94, 10, utyGetNomPrenom($row['Responsable_R1']), 0, 1, 'C');
		$pdf->Cell(94, 10, 'Représentant des compétiteurs: ', 0, 0, 'C');
		$pdf->Cell(94, 10, '', 0, 1, 'C');

		$pdf->Output('Instances ' . $codeCompet . '.pdf', 'I');
	}
}

$page = new FeuilleInstances();
