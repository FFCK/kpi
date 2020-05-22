<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();	
// Chargement
$term = trim(utyGetGet('term'));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
// supprime les 0 devant les numéros de licence
$term = preg_replace('`^[0]*`','',$term);
$idMatch = (int)trim($_GET['idMatch']);
$term1 = '%' . $term . '%';

$jRow = array();
$a_json = array();
$idEquipes = [];
//Equipes
$sql = "SELECT j.Id, j.Code_saison, j.Code_competition, ce.Libelle, 
	ce.Poule, ce.Tirage, ce.Id idEquipe 
	FROM gickp_Journees j, gickp_Matchs m, gickp_Competitions_Equipes ce 
	WHERE m.Id_journee = j.Id 
	AND m.Id = ? 
	AND j.Code_saison = ce.Code_saison 
	AND j.Code_competition = ce.Code_compet 
	GROUP BY ce.Libelle 
	ORDER BY ce.Poule, ce.Tirage, ce.Libelle ";	 
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idMatch));
while ($row = $result->fetch()) {
	$codeCompet = $row['Code_competition'];
	$codeSaison = $row['Code_saison'];
	$codeJournee = $row['Id'];
	$pos = stripos(strtoupper($row["Libelle"]), strtoupper($term));
	if($pos !== false) {
		$jRow["label"] = $row["Libelle"];
		$jRow["value"] = $row["Libelle"];
		$jRow["category"] = 'Equipes';
		array_push($a_json, $jRow);
	}
	//toutes les équipes de la compétition
	$idEquipes[] = $row["idEquipe"];
}
//Joueurs
$in  = str_repeat('?,', count($idEquipes) - 1) . '?';
$sql = "SELECT DISTINCT a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau, 
	(c.Arb IS NULL) AS sortCol 
	FROM gickp_Competitions_Equipes b, gickp_Competitions_Equipes_Joueurs a 
	LEFT OUTER JOIN gickp_Arbitre c ON a.Matric = c.Matric 
	WHERE a.Id_equipe = b.Id 
	AND a.Capitaine <> 'X' 
	AND b.Id IN ($in) 
	AND (a.Matric Like ? 
		OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER(?) 
		OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER(?) 
		OR UPPER(b.Libelle) LIKE UPPER(?) 
	) 
	ORDER BY b.Libelle, sortCol, c.Arb, a.Nom, a.Prenom ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array_merge($idEquipes, [$term1], [$term1], [$term1], [$term1]));
while ($row = $result->fetch()) {
	$arb = strtoupper($row['Arb']);
	$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
	$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
	$jRow["label"] = $row["Libelle"].' - '.$nom.' '.$prenom.' ('.$arb.' '.$row["niveau"].')';
	$jRow["value"] = $nom.' '.$prenom.' ('.$row["Libelle"].') | '.$row["Matric"];
	$jRow["matric"] = $row["Matric"];
	$jRow["category"] = 'Joueurs';
	array_push($a_json, $jRow);
}
//Pool
$sql = "SELECT a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau 
	FROM gickp_Competitions_Equipes b, gickp_Competitions_Equipes_Joueurs a 
	LEFT OUTER JOIN gickp_Arbitre c ON a.Matric = c.Matric 
	WHERE a.Id_equipe = b.Id 
	AND b.Code_compet = 'POOL' 
	AND (a.Matric LIKE :term1 
		OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER(:term1) 
		OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER(:term1) 
		OR UPPER(b.Libelle) LIKE UPPER(:term1) 
	) 
	ORDER BY a.Nom, a.Prenom ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(':term1' => $term1));
while ($row = $result->fetch()) {
	$libelle = substr($row['Libelle'],0,3);
	$libelle = str_replace('Poo', 'Pool', $libelle);
	$arb = strtoupper($row['Arb']);
	if ($row['niveau'] != '') {
		$arb .= '-'.$row['niveau'];
	}
	$matric = $row['Matric'];
	$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
	$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
	$jRow["label"] = $nom.' '.$prenom.' ('.$libelle.') - '.$arb;
	$jRow["value"] = $nom.' '.$prenom.' ('.$libelle.') | '.$row["Matric"];
	$jRow["matric"] = $row["Matric"];
	$jRow["category"] = 'Pool arbitres';
	array_push($a_json, $jRow);
}
//Autres arbitres
$sql = "SELECT lc.*, c.Libelle, b.Arb, b.niveau 
	FROM gickp_Liste_Coureur lc, gickp_Arbitre b, gickp_Club c 
	WHERE lc.Matric = b.Matric 
	AND (lc.Matric LIKE :term1 
		OR UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER(:term1) 
		OR UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER(:term1) 
	) 
	AND lc.Numero_club = c.Code 
	ORDER BY lc.Nom, lc.Prenom ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(':term1' => $term1));
while ($row = $result->fetch()) {
	$libelle = mb_convert_case(strtolower($row['Libelle']), MB_CASE_TITLE, "UTF-8");
	$arb = strtoupper($row['Arb']);
	if($row['niveau'] != '') {
		$arb .= '-'.$row['niveau'];
	}
	$nom  = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
	$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
	$jRow["label"] = $nom .' '.$prenom.' ('.$arb.') - '.$libelle;
	$jRow["value"] = $nom .' '.$prenom.' ('.$arb.') | '.$row["Matric"];
	$jRow["matric"] = $row["Matric"];
	$jRow["category"] = 'Autres arbitres';
	array_push($a_json, $jRow);
}

$json = json_encode($a_json);
header('Content-Type: application/json');
print $json;
