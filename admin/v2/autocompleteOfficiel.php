<?php 
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

	session_start();

	$myBdd = new MyBdd();
	$q = $myBdd->RealEscapeString(trim($_GET['term']));
	$sql  = "SELECT Nom, Prenom, Matric FROM gickp_Liste_Coureur "
            . "WHERE Nom like '%".$q."%' "
            . "OR Prenom like '%".$q."%' "
            . "OR Matric like '".$q."%' ";
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
	$num_results = mysql_num_rows($result);
	$json = array();
	for ($i=1;$i<=$num_results;$i++)
	{
		$row = mysql_fetch_array($result);
		array_push($json, array('value' => ucwords($row['Nom']).' '.ucwords($row['Prenom']).' ('.$row['Matric'].')', 'label' => ucwords($row['Nom']).' '.ucwords($row['Prenom'])));
	}
	//echo $_POST['value']; 
	//echo 'Bonjour'; 
	print json_encode($json);
?>