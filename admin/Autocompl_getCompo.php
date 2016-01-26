<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

	$myBdd = new MyBdd();
	
	// Chargement
		$codeSaison = utyGetGet('s');
		$codeSaison2 = $codeSaison - 1;
		$q = utyGetGet('q');

		$sql  = "SELECT ce.Code_saison, ce.Code_compet ";
		$sql .= "FROM `gickp_Competitions_Equipes` ce, `gickp_Competitions_Equipes_Joueurs` cej ";
		$sql .= "WHERE ce.Id = cej.Id_equipe ";
		$sql .= "AND (ce.Code_saison = $codeSaison OR ce.Code_saison = $codeSaison2) ";
		$sql .= "AND ce.Numero = ".$q." ";
		$sql .= "GROUP BY ce.Code_compet, ce.Code_saison ";
		$sql .= "ORDER BY ce.Code_saison DESC, ce.Code_compet ";
	
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_getCompo : ".$sql);
	//$num_results = mysql_num_rows($result);
	//header('Content-Type: application/json; charset=ISO-8859-1');
	//$response = array();
	echo "<br><b>Reprise des feuilles de présence précédentes :</b><br /><input type='radio' name='checkCompo' value='' checked /><i>Aucune reprise</i><br>";
	while ($row = mysql_fetch_assoc($result)) {
		$Code_saison = $row['Code_saison'];
		$Code_compet = $row['Code_compet'];
		echo "<input type='radio' name='checkCompo' value='$Code_saison-$Code_compet'/>$Code_saison - $Code_compet<br />";
		//$response[] = array("$code - $libelle", $code, $libelle);
		//echo "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|<br>\n";
	}
/*	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);

	$response = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$code = $row['Code'];
		$libelle = $row['Libelle'];
		$response .= "$code - $libelle|$code|$libelle\n";
		//$response[] = array($i++, "$code - $libelle|$code|$libelle\n");
		//$response[] = array($i++, $row);
	}
	echo $response;
*/
?>