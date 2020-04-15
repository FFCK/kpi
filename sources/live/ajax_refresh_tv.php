<?php
include_once('../commun/MyBdd.php');

$voie = (int) $_GET['voie'];

$myBdd = new MyBdd();

$sql = "SELECT `Url` 
	FROM gickp_Tv 
	WHERE Voie = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($voie));
$row = $result->fetch();
if (isset($row['Url'])) {
	echo $row['Url'];
} else {
	echo '';
}
