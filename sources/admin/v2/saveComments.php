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
	$idMatch = (int)utyGetPost('idMatch');
	$value2 = $myBdd->RealEscapeString(trim(utyGetPost('value')));
	if(isset($_POST['heure_fin_match'])){
		$heure_fin_match = $myBdd->RealEscapeString(trim($_POST['heure_fin_match']));
		$heure_fin_match = '00:'.substr($heure_fin_match,-5,2).':'.substr($heure_fin_match,-2);
	}else{
		$heure_fin_match = '';
	}
	// Contrôle autorisation journée
	$sql  = "SELECT Id_journee, Validation FROM gickp_Matchs WHERE Id = ".$idMatch;
	$result = $myBdd->Query($sql);
	$row = $myBdd->FetchArray($result);
	if (!utyIsAutorisationJournee($row['Id_journee'])) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Vous n'avez pas l'autorisation de modifier les matchs de cette journée !";
        exit;
    }
	if ($row['Validation']=='O') {
        header('HTTP/1.0 401 Unauthorized');
        echo "Ce match est verrouillé !";
        exit;
    }
	
	$sql  = "UPDATE gickp_Matchs SET Commentaires_officiels = '".$value2."'";
	if($heure_fin_match != '')
		$sql .= ", Heure_fin = '".$heure_fin_match."' ";
	$sql .= "WHERE Id = ".$idMatch;
	$myBdd->Query($sql);
	echo utyGetPost('value'); 

