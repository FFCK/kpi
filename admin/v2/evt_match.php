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
	$idMatch = (int)utyGetPost('idMatch', 0);
	// M1-00:00-V-A-186002-5
	$ligne = $myBdd->RealEscapeString(trim(utyGetPost('ligne')));
	$ligne = explode('-', $ligne);
	$ancienneLigne = $myBdd->RealEscapeString(trim(utyGetPost('ancienneLigne')));
	$ancienneLigne = explode('-', $ancienneLigne);
	$type = $myBdd->RealEscapeString(trim(utyGetPost('type')));
	$idLigne = $myBdd->RealEscapeString(trim(utyGetPost('idLigne')));
	$idLigne = explode('_', $idLigne);
	
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
	// Contrôle autorisation journée
	$sql  = "Select Id_journee, Validation from gickp_Matchs where Id = ".$idMatch;
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
	$row = mysql_fetch_array($result);
	if (!utyIsAutorisationJournee($row['Id_journee']))
		die ("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
	if ($row['Validation']=='O')
		die ("Ce match est verrouillé !");

	if($type == 'insert'){
		$sql  = "INSERT INTO gickp_Matchs_Detail SET Id_match = ".$idMatch.", Periode = '".$ligne[0]."', ";
		$sql .= "Temps = '00:".$ligne[1]."', Id_evt_match = '".$ligne[2]."', Competiteur = '".$ligne[4]."', ";
		$sql .= "Numero = '".$ligne[5]."', Equipe_A_B = '".$ligne[3]."' ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur INSERT<br />".$sql);
		echo mysql_insert_id();
	}elseif($type == 'update'){
		$sql  = "UPDATE gickp_Matchs_Detail SET Id_match = ".$idMatch.", Periode = '".$ligne[0]."', ";
		$sql .= "Temps = '00:".$ligne[1]."', Id_evt_match = '".$ligne[2]."', Competiteur = '".$ligne[4]."', ";
		$sql .= "Numero = '".$ligne[5]."', Equipe_A_B = '".$ligne[3]."' ";
		$sql .= "WHERE Id = ".$idLigne[1];
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
		echo 'OK';
	}elseif($type == 'delete'){
		$sql  = "DELETE FROM gickp_Matchs_Detail  ";
		$sql .= "WHERE Id = ".$idLigne[1];
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur DELETE<br />".$sql);
		echo 'OK';
	}
?>
