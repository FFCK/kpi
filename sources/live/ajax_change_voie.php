<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$voie = utyGetInt($_POST, 'voie', 0);
$url = utyGetString($_POST, 'url');

$url = str_replace("|QU|", "?", $url);
$url = str_replace("|AM|", "&", $url);

$myBdd = new MyBdd();

$sql = "UPDATE gickp_Tv 
    SET `Url` = ? 
    WHERE Voie = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($url, $voie));

echo "OK Voie $voie : ".$url;
