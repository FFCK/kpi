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

if (strlen($term) < 2){
    echo 'Trop court...';
    return;
}

$a_json = array();
$jRow = array();

$matric = (int) $term;
if ($matric > 0) {
	$sql = "SELECT lc.*, c.Libelle, s.Date date_surclassement 
	FROM gickp_Club c, gickp_Liste_Coureur lc 
	LEFT OUTER JOIN gickp_Surclassements s 
		ON (lc.Matric = s.Matric AND s.Saison = ?) 
	WHERE (lc.Matric = ? 
		OR lc.Reserve = ? ) 
		AND lc.Numero_club = c.Code 
		ORDER BY lc.Nom, lc.Prenom ";
	$arrayQuery = array($codeSaison, $matric, $matric);
} else {
	$sql = "SELECT lc.*, c.Libelle, s.Date date_surclassement 
		FROM gickp_Club c, gickp_Liste_Coureur lc 
		LEFT OUTER JOIN gickp_Surclassements s 
			ON (lc.Matric = s.Matric AND s.Saison = ?) 
		WHERE (UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER(?) 
			OR UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER(?) ) 
		AND lc.Numero_club = c.Code 
		ORDER BY lc.Nom, lc.Prenom ";
	$arrayQuery = array($codeSaison, $term.'%', $term.'%');
}
$result = $myBdd->pdo->prepare($sql);
$result->execute($arrayQuery);
while ($row = $result->fetch()) {
    $jRow["club"] = $row['Numero_club'];
    $jRow["libelle"] = $row['Libelle'];
    $jRow["matric"] = $row['Matric'];
	$jRow["nom"] = mb_strtoupper($row['Nom']);
	$jRow["prenom"] = mb_convert_case($row['Prenom'], MB_CASE_TITLE, "UTF-8");
    if (strlen($row['Arb']) > 1) {
        $jRow["arb"] = ' ' . $row['Arb'] . '-' . $row['niveau'];
    } else {
        $jRow["arb"] = '';
    }
	$jRow["nom2"] = mb_strtoupper($row['Nom']);
	$jRow["prenom2"] = mb_convert_case($row['Prenom'], MB_CASE_TITLE, "UTF-8");
    $jRow["naissance"] = $row['Naissance'];
    $jRow["sexe"] = $row['Sexe'];
    $jRow["label"] = $jRow["matric"] . ' - ' . $jRow["nom2"] . ' ' . $jRow["prenom2"] . ' (' . $jRow["club"] . '-' . $jRow["libelle"] . ')' . $jRow["arb"];
    $jRow["value"] = $jRow["nom2"] . ' ' . $jRow["prenom2"] . ' (' . $jRow["matric"] . ')' . $jRow["arb"];
    $jRow["category"] = $row['Libelle'];
    array_push($a_json, $jRow);
}

$json = json_encode($a_json);
header('Content-Type: application/json');
print $json;
