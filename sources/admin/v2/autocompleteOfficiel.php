<?php 
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

session_start();

$myBdd = new MyBdd();
$q = trim(utyGetGet('term'));
$q1 = '%'.$q.'%';
$q2 = $q.'%';
$sql = "SELECT Nom, Prenom, Matric 
	FROM kp_licence 
	WHERE Nom LIKE :q1 
	OR Prenom LIKE :q1 
	OR Matric LIKE :q2 ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(':q1' => $q1, ':q2' => $q2));
$json = array();
while ($row = $result->fetch()) {	
	array_push($json, array(
		'value' => ucwords($row['Nom']).' '.ucwords($row['Prenom']).' ('.$row['Matric'].')', 
		'label' => ucwords($row['Nom']).' '.ucwords($row['Prenom'])));
}

header('Content-Type: application/json');
print json_encode($json);
