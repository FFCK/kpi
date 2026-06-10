<?php
/**
 * Génération de tableau ODS des matchs avec OpenSpout
 */

require_once('../vendor/autoload.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

use OpenSpout\Writer\ODS\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;

if(!isset($_SESSION)) {
	session_start();
}

// Chargement des langues
$langue = parse_ini_file("../commun/MyLang.ini", true);
$isEn = utyGetSession('lang') == 'en';
if ($isEn) {
	$lang = $langue['en'];
} else {
	$lang = $langue['fr'];
}

$myBdd = new MyBdd();

// Filtres explicites en URL (priment sur le listMatch en session)
$laCompet = utyGetGet('Compet', '');
$idEvenement = utyGetGet('idEvenement', '');
$codeSaison = utyGetGet('S', '');
$hasUrlFilters = ($laCompet != '' || ($idEvenement != '' && $idEvenement > 0) || $codeSaison != '');

// listMatch : URL prioritaire, puis session — sauf si des filtres URL sont fournis
$listMatchUrl = utyGetGet('listMatch', '');
if ($listMatchUrl !== '') {
	$listMatch = $listMatchUrl;
} elseif ($hasUrlFilters) {
	$listMatch = ''; // forcer la résolution depuis les filtres URL ci-dessous
} else {
	$listMatch = utyGetSession('listMatch', '');
}

// Si pas de listMatch, résoudre les IDs depuis les filtres Compet/idEvenement/S
if (empty($listMatch)) {
	if ($hasUrlFilters) {
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
			d.Phase, d.Niveau, d.Etape, d.Type TypeJournee, d.Lieu, d.Nom LibelleJournee
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
	// Couleurs pastel distinctes pour jusqu'à 8 compétitions
	$competitionColors = [
		'FFD6D6', // rose pâle
		'D6E8FF', // bleu ciel
		'D6FFD6', // vert menthe
		'FFF3D6', // jaune pâle
		'EDD6FF', // lavande
		'D6FFF3', // turquoise
		'FFE8D6', // pêche
		'F0F0D6', // jaune verdâtre
	];
	$competitionColorMap = [];
	$colorIndex = 0;

	// Pré-indexer les couleurs par Code_competition
	foreach ($arrayMatchs as $match) {
		$code = $match['Code_competition'] ?? '';
		if ($code !== '' && !isset($competitionColorMap[$code])) {
			$competitionColorMap[$code] = $competitionColors[$colorIndex % count($competitionColors)];
			$colorIndex++;
		}
	}

	$headerRow = Row::fromValues([
		$lang['Num'] ?? 'N°',
		$lang['Journee'] ?? 'Journée',
		$lang['Competition'] ?? 'Compétition',
		$lang['Phase'] ?? 'Phase',
		$isEn ? 'Stage' : 'Tour',
		'Type',
		$lang['Lieu'] ?? 'Lieu',
		$lang['Date'] ?? 'Date',
		$lang['Heure'] ?? 'Heure',
		$lang['Terrain'] ?? 'Terrain',
		'Code',
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
		$code = $match['Code_competition'] ?? '';
		$rowStyle = null;
		if ($code !== '' && isset($competitionColorMap[$code])) {
			$rowStyle = (new Style())->setBackgroundColor($competitionColorMap[$code]);
		}
		$dataRow = Row::fromValues([
			$match['Numero_ordre'] ?? '',
			$match['LibelleJournee'] ?? '',
			$match['Code_competition'] ?? '',
			$match['Phase'] ?? '',
			$match['Etape'] ?? '',
			($match['TypeJournee'] ?? '') === 'C' ? 'Classification' : ($isEn ? 'Elimination' : 'Élimination'),
			$match['Lieu'] ?? '',
			$match['Date_match'] ?? '',
			$match['Heure_match'] ?? '',
			$match['Terrain'] ?? '',
			$match['Libelle'] ?? '',
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
		], $rowStyle);
		$writer->addRow($dataRow);
	}

	// ─────────────────────────────────────────────────────────────────────
	// Deuxième feuille : grille de planification (drag & drop des matchs)
	//   - À gauche : colonne horaires + 6 colonnes terrains (vides, pour y
	//     glisser les matchs lors de la planification)
	//   - À droite : un bloc par Code_competition (chaque match dans une
	//     cellule, colorée comme la première feuille) à faire glisser
	// ─────────────────────────────────────────────────────────────────────
	$planSheet = $writer->addNewSheetAndMakeItCurrent();
	$planSheet->setName($isEn ? 'Planning' : 'Planification');

	$nbTerrains = 6;
	$gapCols = 1; // colonne vide entre la grille et les blocs matchs

	// Regrouper les matchs par compétition (en conservant l'ordre Numero_ordre)
	$matchsParCompet = [];
	foreach ($arrayMatchs as $match) {
		$code = $match['Code_competition'] ?? '';
		$matchsParCompet[$code][] = $match;
	}

	// Créneaux distincts (date + heure) triés chronologiquement
	$creneaux = []; // tableau de ['date' => ..., 'heure' => ...]
	$creneauxSeen = [];
	foreach ($arrayMatchs as $match) {
		$d = $match['Date_match'] ?? ''; // déjà converti en FR (dd/mm/yyyy)
		$h = $match['Heure_match'] ?? '';
		if ($h === '') continue;
		$key = $d . '|' . $h;
		if (!isset($creneauxSeen[$key])) {
			$creneauxSeen[$key] = true;
			// Reconvertir en YYYY-MM-DD pour le tri, puis stocker tel quel
			$dateSort = $d !== ''
				? implode('-', array_reverse(explode('/', $d)))
				: '0000-00-00';
			$creneaux[] = ['date' => $d, 'heure' => $h, 'sort' => $dateSort . ' ' . $h];
		}
	}
	usort($creneaux, fn($a, $b) => strcmp($a['sort'], $b['sort']));
	// Garder $horaires pour compatibilité onglet 2 (liste des heures dans l'ordre)
	$horaires = array_values(array_unique(array_column($creneaux, 'heure')));

	// Style d'en-tête (gras, fond gris clair)
	$planHeaderStyle = (new Style())->setFontBold()->setBackgroundColor('E0E0E0');

	// Pré-construire la liste des cellules-match par colonne de compétition
	$blocCols = []; // index 0..n -> tableau de cellules [valeur, code couleur]
	foreach ($matchsParCompet as $code => $matchs) {
		$col = [];
		foreach ($matchs as $match) {
			$txt = trim(
				($match['Numero_ordre'] ?? '') . ' - '
				. ($match['Code_competition'] ?? '') . ' '
				. ($match['Phase'] ?? '') . ' '
				. ($match['Libelle'] ?? '')
			);
			$col[] = ['text' => $txt, 'color' => $competitionColorMap[$code] ?? null];
		}
		$blocCols[] = $col;
	}

	// Ligne d'en-tête : Date | Heure | Terrain 1..6 | (vide) | (codes compétition)
	$headerCells = [$isEn ? 'Date' : 'Date', $isEn ? 'Time' : 'Heure'];
	for ($i = 1; $i <= $nbTerrains; $i++) {
		$headerCells[] = ($isEn ? 'Pitch ' : 'Terrain ') . $i;
	}
	for ($i = 0; $i < $gapCols; $i++) {
		$headerCells[] = '';
	}
	foreach (array_keys($matchsParCompet) as $code) {
		$headerCells[] = $code;
	}
	$writer->addRow(Row::fromValues($headerCells, $planHeaderStyle));

	// Nombre de lignes de données = max(créneaux, plus longue colonne de matchs)
	$maxBloc = 0;
	foreach ($blocCols as $col) {
		$maxBloc = max($maxBloc, count($col));
	}
	$nbLignes = max(count($creneaux), $maxBloc);

	for ($r = 0; $r < $nbLignes; $r++) {
		// Partie gauche : date + horaire + terrains vides
		$cells = [];
		$cells[] = $creneaux[$r]['date'] ?? '';
		$cells[] = $creneaux[$r]['heure'] ?? '';
		for ($i = 0; $i < $nbTerrains; $i++) {
			$cells[] = '';
		}
		for ($i = 0; $i < $gapCols; $i++) {
			$cells[] = '';
		}
		// Partie droite : une cellule-match par colonne de compétition
		// (style appliqué par cellule via Cell::fromValue)
		$rowCells = [];
		foreach ($cells as $v) {
			$rowCells[] = \OpenSpout\Common\Entity\Cell::fromValue($v);
		}
		foreach ($blocCols as $col) {
			if (isset($col[$r])) {
				$cellStyle = $col[$r]['color'] !== null
					? (new Style())->setBackgroundColor($col[$r]['color'])
					: null;
				$rowCells[] = \OpenSpout\Common\Entity\Cell::fromValue($col[$r]['text'], $cellStyle);
			} else {
				$rowCells[] = \OpenSpout\Common\Entity\Cell::fromValue('');
			}
		}
		$writer->addRow(new Row($rowCells, null));
	}

	// ─────────────────────────────────────────────────────────────────────
	// Troisième feuille : grille de planification avec matchs placés
	//   - Colonnes : Heure | Terrain 1..N (dynamique)
	//   - Chaque match est placé à la ligne de son Heure_match et dans la
	//     colonne de son Terrain (même texte et couleur que l'onglet 2)
	// ─────────────────────────────────────────────────────────────────────
	$schedSheet = $writer->addNewSheetAndMakeItCurrent();
	$schedSheet->setName($isEn ? 'Schedule' : 'Programme');

	// Terrains distincts triés
	$terrains = [];
	foreach ($arrayMatchs as $match) {
		$t = $match['Terrain'] ?? '';
		if ($t !== '' && !in_array($t, $terrains, true)) {
			$terrains[] = $t;
		}
	}
	sort($terrains, SORT_NATURAL);

	// Index par (date, heure, terrain) → [text, color]
	$schedGrid = [];
	foreach ($arrayMatchs as $match) {
		$d = $match['Date_match'] ?? '';
		$h = $match['Heure_match'] ?? '';
		$t = $match['Terrain'] ?? '';
		if ($h === '' || $t === '') continue;
		$code = $match['Code_competition'] ?? '';
		$txt = trim(
			($match['Numero_ordre'] ?? '') . ' - '
			. ($match['Code_competition'] ?? '') . ' '
			. ($match['Phase'] ?? '') . ' '
			. ($match['Libelle'] ?? '')
		);
		$schedGrid[$d][$h][$t] = ['text' => $txt, 'color' => $competitionColorMap[$code] ?? null];
	}

	// En-tête : Date | Heure | Terrain 1..N
	$schedHeaderCells = [$isEn ? 'Date' : 'Date', $isEn ? 'Time' : 'Heure'];
	foreach ($terrains as $t) {
		$schedHeaderCells[] = ($isEn ? 'Pitch ' : 'Terrain ') . $t;
	}
	$writer->addRow(Row::fromValues($schedHeaderCells, $planHeaderStyle));

	// Lignes de données — $creneaux est déjà trié par date puis heure
	foreach ($creneaux as $creneau) {
		$d = $creneau['date'];
		$h = $creneau['heure'];
		$rowCells = [
			\OpenSpout\Common\Entity\Cell::fromValue($d),
			\OpenSpout\Common\Entity\Cell::fromValue($h),
		];
		foreach ($terrains as $t) {
			if (isset($schedGrid[$d][$h][$t])) {
				$entry = $schedGrid[$d][$h][$t];
				$cellStyle = $entry['color'] !== null
					? (new Style())->setBackgroundColor($entry['color'])
					: null;
				$rowCells[] = \OpenSpout\Common\Entity\Cell::fromValue($entry['text'], $cellStyle);
			} else {
				$rowCells[] = \OpenSpout\Common\Entity\Cell::fromValue('');
			}
		}
		$writer->addRow(new Row($rowCells, null));
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
