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
 
    if (strlen($term) < 2){
        echo 'Trop court...';
        return;
    }
        
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
	$sql  = "Select lc.*, c.Libelle ";
	$sql .= "From gickp_Liste_Coureur lc, gickp_Club c ";
	$sql .= "Where (lc.Matric Like '%".ltrim($term, '0')."%' ";
	$sql .= "Or UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER('%".$term."%') ";
	$sql .= ") And lc.Numero_club = c.Code ";
	$sql .= "Order by lc.Nom, lc.Prenom ";
	$sql .= "limit 0, 40 ";
	
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_joueur : ".$sql);
	while($row = mysql_fetch_assoc($result)) {
	  $jRow["club"] = $row['Numero_club'];
	  $jRow["libelle"] = $row['Libelle'];
	  $jRow["matric"] = $row['Matric'];
	  $jRow["nom"] = $row['Nom'];
	  $jRow["prenom"] = $row['Prenom'];
	  $jRow["nom2"] = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
	  $jRow["prenom2"] = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
	  $jRow["naissance"] = $row['Naissance'];
	  $jRow["sexe"] = $row['Sexe'];
	  $jRow["label"] = $jRow["matric"].' - '.$jRow["nom2"].' '.$jRow["prenom2"].' ('.$jRow["club"].'-'.$jRow["libelle"].')';
	  $jRow["value"] = $jRow["nom2"].' '.$jRow["prenom2"].' ('.$jRow["matric"].')';
	  $jRow["category"] = $row['Libelle'];
	  array_push($a_json, $jRow);
	}
	$json = json_encode($a_json);
	print $json;
?>