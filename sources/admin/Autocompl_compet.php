<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
	$user_error = 'Access denied - not an AJAX request...';
	trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$sql = "SELECT p1.* 
	FROM kp_competition p1
	INNER JOIN
	(
			SELECT MAX(Code_saison) maxCodeSaison, Code
			FROM kp_competition
			WHERE Code LIKE :code
			OR Libelle LIKE :code
			GROUP BY Code
	) p2
		ON p1.Code = p2.Code
		AND p1.Code_saison = p2.maxCodeSaison
	WHERE p1.Code LIKE :code
			OR p1.Libelle LIKE :code
	ORDER BY p1.Code_saison desc
	LIMIT 30 ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(':code' => '%' . $q . '%'));
$resultGlobal = '';

while ($row = $result->fetch()) {
	$code = $row['Code'];
	$libelle = $row['Libelle'];
	$Code_niveau = $row['Code_niveau'];
	$Code_ref = $row['Code_ref'];
	$GroupOrder = $row['GroupOrder'];
	$Code_typeclt = $row['Code_typeclt'];
	$Code_tour = $row['Code_tour'];
	$Qualifies = $row['Qualifies'];
	$Elimines = $row['Elimines'];
	$Points = $row['Points'];
	$Soustitre = $row['Soustitre'];
	$Soustitre2 = $row['Soustitre2'];
	$Web = $row['Web'];
	$BandeauLink = $row['BandeauLink'];
	$LogoLink = $row['LogoLink'];
	$SponsorLink = $row['SponsorLink'];
	$ToutGroup = $row['ToutGroup'];
	$TouteSaisons = $row['TouteSaisons'];
	$Titre_actif = $row['Titre_actif'];
	$Bandeau_actif = $row['Bandeau_actif'];
	$Logo_actif = $row['Logo_actif'];
	$Sponsor_actif = $row['Sponsor_actif'];
	$Kpi_ffck_actif = $row['Kpi_ffck_actif'];
	$En_actif = $row['En_actif'];
	$goalaverage = $row['goalaverage'];
	$resultGlobal .= "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|$Code_typeclt|$Code_tour|$Qualifies|$Elimines|$Points|$Soustitre|$Web|$LogoLink|$SponsorLink|$ToutGroup|$TouteSaisons|$GroupOrder|$Soustitre2|$Titre_actif|$Logo_actif|$Sponsor_actif|$Kpi_ffck_actif|$En_actif|$BandeauLink|$Bandeau_actif|$goalaverage\n";
}

echo $resultGlobal;
