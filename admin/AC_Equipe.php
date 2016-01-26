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
//include_once('../commun/MyTools.php');
	
	// Chargement
	$term = trim($_GET['term']);
	// replace multiple spaces with one
	$term = preg_replace('/\s+/', ' ', $term);
	// supprime les 0 devant les numéros de licence
	$term = preg_replace('`^[0]*`','',$term);
 
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
	$myBdd = new MyBdd();
	$sql  = "Select e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub ";
	$sql .= "From gickp_Equipe e, gickp_Club c ";
	$sql .= "Where (UPPER(e.Libelle) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(e.Code_club) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(c.Libelle) LIKE UPPER('%".$term."%')) ";
	$sql .= "And e.Code_club = c.Code ";
	$sql .= "Order by e.Libelle ";
	
	$result = $myBdd->Query($sql);
	while($row = $myBdd->FetchAssoc($result)) {
		$jRow["label"] = $row["Code_club"].' - '.$row["Libelle"].' ('.$row["nomClub"].')';
		$jRow["value"] = $row["Libelle"];
		$jRow["Numero"] = $row["Numero"];
		// $jRow["category"] = $row['Libelle'];
		array_push($a_json, $jRow);
	}
	$json = json_encode($a_json);
	print $json;
?>