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
$result = $myBdd->Query($sql);
$num_results = $myBdd->NumRows($result);
$json = array();
for ($i=1;$i<=$num_results;$i++)
{
	$row = $myBdd->FetchArray($result);
	array_push($json, array('value' => ucwords($row['Nom']).' '.ucwords($row['Prenom']).' ('.$row['Matric'].')', 'label' => ucwords($row['Nom']).' '.ucwords($row['Prenom'])));
}

print json_encode($json);
