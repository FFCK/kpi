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
	$idJournee = (int)$_POST['Id_Journee'];
	$Valeur = $myBdd->RealEscapeString(trim($_POST['Valeur']));
	$TypeUpdate = $myBdd->RealEscapeString(trim($_POST['TypeUpdate']));
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
	$sql  = "SELECT Id, Validation "
            . "FROM gickp_Journees "
            . "WHERE Id = ".$idJournee;
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
	$row = mysql_fetch_array($result);
	if (!utyIsAutorisationJournee($row['Id_journee'])) {
        die("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
    }
    if ($TypeUpdate != 'Validation' && $TypeUpdate != 'Publication' && $row['Validation'] == 'O') {
        die("Ce match est verrouillé !");
    }

    $sql  = "UPDATE gickp_Journees "
            . "SET ".$TypeUpdate." = '".$Valeur."' "
            . "WHERE Id = ".$idJournee;
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
	echo 'OK';
