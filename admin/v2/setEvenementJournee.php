<?php 
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

	session_start();

	$myBdd = new MyBdd();
	$Id_Evenement = $_POST['Id_Evenement'];
	$Id_Journee = $_POST['Id_Journee'];
	$Valeur = $_POST['Valeur'];
/*	// SECURITY HOLE ***************************************************************
	$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
	$json_invalid = json_encode($a_json_invalid);
	// allow space, any unicode letter and digit, underscore and dash
	if(preg_match("/[^\040\pL\pN_-]/u", $value)) {
	  print $json_invalid;
	  exit;
	}
	// *****************************************************************************
*/

	if($Valeur == 'true'){
		$sql  = "Replace Into gickp_Evenement_Journees (Id_Evenement, Id_Journee) Values ($Id_Evenement, $Id_Journee)";
	}elseif($Valeur == 'false'){
		$sql  = "Delete From gickp_Evenement_Journees Where Id_Evenement = $Id_Evenement And Id_Journee = $Id_Journee ";
	}
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
	echo 'OK';
	//echo $Id_Evenement.' - '.$Id_Journee.' - '.$Valeur.'<br />'.$sql;
?>