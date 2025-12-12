<?php
/**
 * AJAX endpoint pour récupérer les compétitions d'une saison donnée
 * Retourne les compétitions groupées par section (comme comboCompet)
 */

// prevent direct access
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
	$user_error = 'Access denied - not an AJAX request...';
	trigger_error($user_error, E_USER_ERROR);
}

include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();

$saison = utyGetGet('saison', '');

if (empty($saison)) {
	header('Content-Type: application/json');
	echo json_encode(['error' => 'Saison non spécifiée']);
	exit;
}

// Chargement des compétitions groupées par section (comme comboCompet)
$label = $myBdd->getSections();

$sql = "SELECT DISTINCT c.GroupOrder, c.Code, c.Libelle, c.Soustitre, c.Soustitre2,
	c.Titre_actif, g.id, g.section, g.ordre
	FROM kp_competition c, kp_groupe g
	WHERE c.Code_saison = ?
	AND c.Code_ref = g.Groupe
	ORDER BY g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($saison));

$arrayCompetition = array();
$j = '';
$i = -1;

while ($row = $result->fetch()) {
	// Titre
	if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
		$Libelle = $row["Soustitre"];
	} else {
		$Libelle = $row["Libelle"];
	}
	if ($row["Soustitre2"] != '') {
		$Libelle .= ' - ' . $row["Soustitre2"];
	}

	if ($j != $row['section']) {
		$i++;
		$arrayCompetition[$i] = array(
			'label' => $label[$row['section']] ?? 'Autres',
			'options' => array()
		);
	}
	$j = $row['section'];
	$arrayCompetition[$i]['options'][] = array(
		'Code' => $row['Code'],
		'Libelle' => $Libelle
	);
}

header('Content-Type: application/json');
echo json_encode($arrayCompetition);
