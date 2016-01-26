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
	$idMatch = $_POST['idMatch'];
	$idJournee = $_POST['idJournee'];
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
	$data[] = array('Id' => 0, 'Libelle' => '( indéterminé )');
	$sql  = "SELECT ce.Id, ce.Libelle FROM gickp_Competitions_Equipes ce, gickp_Journees j WHERE ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison AND j.Id = ".$idJournee;
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
	while ($row = mysql_fetch_array($result)) {	
		$data[] = $row;
	}
	$encode_donnees = json_encode($data);
	echo $encode_donnees; 

?>