<?php
/**
 * Génération de tableau ODS des matchs avec OpenSpout
 */

require_once('../vendor/autoload.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

use OpenSpout\Writer\ODS\Writer;
use OpenSpout\Common\Entity\Row;

if(!isset($_SESSION)) {
	session_start();
}

// Chargement des langues
$langue = parse_ini_file("../commun/MyLang.ini", true);
if (utyGetSession('lang') == 'en') {
	$lang = $langue['en'];
} else {
	$lang = $langue['fr'];
}

$myBdd = new MyBdd();
$listMatch = utyGetSession('listMatch', '');
$listMatch = utyGetGet('listMatch', $listMatch);

// Si pas de listMatch, résoudre les IDs depuis les filtres Compet/idEvenement/S
if (empty($listMatch)) {
	$laCompet = utyGetGet('Compet', '');
	$idEvenement = utyGetGet('idEvenement', '');
	$codeSaison = utyGetGet('S', '');
	if ($laCompet != '' || $idEvenement != '' || $codeSaison != '') {
		if ($codeSaison == '') {
			$codeSaison = $myBdd->GetActiveSaison();
		}
		$sqlIds = "SELECT m.Id FROM kp_match m
			INNER JOIN kp_journee j ON m.Id_journee = j.Id
			WHERE j.Code_saison = ? ";
		$paramsIds = [$codeSaison];
		if ($idEvenement != '' && $idEvenement > 0) {
			$sqlIds .= "AND m.Id_journee IN (SELECT Id_journee FROM kp_evenement_journee WHERE Id_evenement = ?) ";
			$paramsIds[] = (int) $idEvenement;
		} elseif ($laCompet != '' && $laCompet != '*' && $laCompet != '0') {
			$sqlIds .= "AND j.Code_competition = ? ";
			$paramsIds[] = $laCompet;
		}
		$sqlIds .= "ORDER BY m.Date_match, m.Heure_match, m.Terrain";
		$stmtIds = $myBdd->pdo->prepare($sqlIds);
		$stmtIds->execute($paramsIds);
		$ids = [];
		while ($r = $stmtIds->fetch()) {
			$ids[] = $r['Id'];
		}
		$listMatch = implode(',', $ids);
	}
}

if (empty($listMatch)) {
	exit("Erreur : Aucun match sélectionné.");
}

$listMatch = explode(',', $listMatch);
$arrayMatchs = array();
$in = str_repeat('?,', count($listMatch) - 1) . '?';
$sql = "SELECT a.*, b.Libelle EquipeA, c.Libelle EquipeB, d.Code_competition,
			d.Phase, d.Niveau, d.Lieu, d.Nom LibelleJournee
			FROM kp_journee d, kp_match a
			LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id)
			LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id)
			WHERE a.Id IN ($in)
			AND a.Id_journee = d.Id
			ORDER BY a.Date_match, a.Heure_match, a.Terrain";

$result = $myBdd->pdo->prepare($sql);
$result->execute($listMatch);

while ($aRow = $result->fetch()) {
	$aRow['Date_match'] = utyDateUsToFr($aRow['Date_match']);
	if ($aRow['Libelle'] != '') {
		$EquipesAffectAuto = utyEquipesAffectAutoFR($aRow['Libelle']);
	} else {
		$EquipesAffectAuto = array();
	}
	if (($aRow['EquipeA'] == '') && isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '')
		$aRow['EquipeA'] = $EquipesAffectAuto[0];
	if ($aRow['EquipeB'] == '' && isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '')
		$aRow['EquipeB'] = $EquipesAffectAuto[1];
	if ($aRow['Arbitre_principal'] != '' && $aRow['Arbitre_principal'] != '-1')
		$aRow['Arbitre_principal'] = utyArbSansNiveau($aRow['Arbitre_principal']);
	elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '')
		$aRow['Arbitre_principal'] = $EquipesAffectAuto[2];
	if ($aRow['Arbitre_secondaire'] != '' && $aRow['Arbitre_secondaire'] != '-1')
		$aRow['Arbitre_secondaire'] = utyArbSansNiveau($aRow['Arbitre_secondaire']);
	elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '')
		$aRow['Arbitre_secondaire'] = $EquipesAffectAuto[3];
	array_push($arrayMatchs, $aRow);
}

if (empty($arrayMatchs)) {
	exit("Erreur : Aucune donnée trouvée.");
}

$file_name = 'Matchs_' . date('d-m-Y') . '.ods';
$temp_file = '/tmp/' . $file_name;

try {
	$writer = new Writer();
	$writer->openToFile($temp_file);
	$headerRow = Row::fromValues([
		$lang['Num'] ?? 'N°',
		$lang['Journee'] ?? 'Journée',
		$lang['Competition'] ?? 'Compétition',
		$lang['Phase'] ?? 'Phase',
		$lang['Lieu'] ?? 'Lieu',
		$lang['Date'] ?? 'Date',
		$lang['Heure'] ?? 'Heure',
		$lang['Terrain'] ?? 'Terrain',
		$lang['Equipe_A'] ?? 'Équipe A',
		$lang['Equipe_B'] ?? 'Équipe B',
		$lang['Score'] . ' A',
		$lang['Score'] . ' B',
		$lang['Arbitre_1'] ?? 'Arbitre principal',
		$lang['Arbitre_2'] ?? 'Arbitre secondaire',
		$lang['Ligne'] ?? 'Juge de ligne',
		$lang['Ligne'] ?? 'Juge de ligne',
		$lang['Secretaire'] ?? 'Secrétaire',
		$lang['Chronometre'] ?? 'Chronomètre',
		$lang['Time_shoot2'] ?? 'Shotclock',
		$lang['Commentaires'] ?? 'Commentaires'
	]);
	$writer->addRow($headerRow);
	foreach ($arrayMatchs as $match) {
		$dataRow = Row::fromValues([
			$match['Numero_ordre'] ?? '',
			$match['LibelleJournee'] ?? '',
			$match['Code_competition'] ?? '',
			$match['Phase'] ?? '',
			$match['Lieu'] ?? '',
			$match['Date_match'] ?? '',
			$match['Heure_match'] ?? '',
			$match['Terrain'] ?? '',
			$match['EquipeA'] ?? '',
			$match['EquipeB'] ?? '',
			$match['ScoreA'] ?? '',
			$match['ScoreB'] ?? '',
			$match['Arbitre_principal'] ?? '',
			$match['Arbitre_secondaire'] ?? '',
			$match['Ligne1'] ?? '',
			$match['Ligne2'] ?? '',
			$match['Secretaire'] ?? '',
			$match['Chronometre'] ?? '',
			$match['Timeshoot'] ?? '',
			$match['Commentaires_officiels'] ?? ''
		]);
		$writer->addRow($dataRow);
	}
	$writer->close();
	if (!file_exists($temp_file)) {
		exit("Erreur : Fichier non créé.");
	}
	header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
	header('Content-Disposition: attachment; filename="' . $file_name . '"');
	header('Content-Length: ' . filesize($temp_file));
	readfile($temp_file);
	unlink($temp_file);
	exit;
} catch (Exception $e) {
	exit("Erreur : " . $e->getMessage());
}
