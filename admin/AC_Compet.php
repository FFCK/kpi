<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
	$myBdd = new MyBdd();
	// Chargement
	$term = $myBdd->RealEscapeString(trim(utyGetGet('term')));
	// replace multiple spaces with one
	$term = preg_replace('/\s+/', ' ', $term);
	// supprime les 0 devant les numéros de licence
	// $term = preg_replace('`^[0]*`','',$term);
 
	$a_json = array();
	$jRow = array();
/*	// SECURITY HOLE ***************************************************************
	$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
	$json_invalid = json_encode($a_json_invalid);
	// allow space, any unicode letter and digit, underscore and dash
	if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
	  print $json_invalid;
	  exit;
	}
	// *****************************************************************************
 */
	$sql  = "SELECT *, MAX(Code_saison) last_Saison "
            . "FROM gickp_Competitions "
            . "WHERE Code != 'POOL' "
            . "AND (Code Like '%".$term."%' "
            . "OR Libelle Like '%".$term."%') "
            . "GROUP BY Code, Libelle "
            . "ORDER BY Code_niveau, Code, Libelle ";
	$result = $myBdd->Query($sql);
	while($row = $myBdd->FetchAssoc($result)) {
		$jRow['code'] = $row['Code'];
		$jRow['saison'] = $row['last_Saison'];
		//$libelle = __encode($row['Libelle']);
		$jRow['libelle'] = $row['Libelle'];
		$jRow['Code_niveau'] = $row['Code_niveau'];
		$jRow['Code_ref'] = $row['Code_ref'];
		$jRow['GroupOrder'] = $row['GroupOrder'];
		$jRow['Code_typeclt'] = $row['Code_typeclt'];
		$jRow['Code_tour'] = $row['Code_tour'];
		$jRow['Qualifies'] = $row['Qualifies'];
		$jRow['Elimines'] = $row['Elimines'];
		$jRow['Points'] = $row['Points'];
		//$Soustitre = __encode($row['Soustitre']);
		//$Soustitre2 = __encode($row['Soustitre2']);
		$jRow['Soustitre'] = $row['Soustitre'];
		$jRow['Soustitre2'] = $row['Soustitre2'];
		$jRow['Web'] = $row['Web'];
		$jRow['LogoLink'] = $row['LogoLink'];
		$jRow['SponsorLink'] = $row['SponsorLink'];
		$jRow['ToutGroup'] = $row['ToutGroup'];
		$jRow['TouteSaisons'] = $row['TouteSaisons'];
		$jRow['Titre_actif'] = $row['Titre_actif'];
		$jRow['Logo_actif'] = $row['Logo_actif'];
		$jRow['Sponsor_actif'] = $row['Sponsor_actif'];
		$jRow['Kpi_ffck_actif'] = $row['Kpi_ffck_actif'];
		$jRow['En_actif'] = $row['En_actif'];
		$jRow['resultGlobal'] = "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|$Code_typeclt|$Code_tour|$Qualifies|$Elimines|$Points|$Soustitre|$Web|$LogoLink|$SponsorLink|$ToutGroup|$TouteSaisons|$GroupOrder|$Soustitre2|$Titre_actif|$Logo_actif|$Sponsor_actif|$Kpi_ffck_actif|$En_actif\n";
		$jRow["label"] = $jRow["code"].' - '.$jRow["libelle"].' ('.$jRow["saison"].')';
		$jRow["value"] = $jRow["code"];
		$jRow["category"] = $row['Code_niveau'];
		array_push($a_json, $jRow);
	}
	$json = json_encode($a_json);
	print $json;
