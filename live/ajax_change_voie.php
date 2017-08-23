<?php
include_once('base.php');

$voie = utyGetInt($_GET, 'voie', 0);
$url = utyGetString($_GET, 'url');

$url = str_replace("|QU|", "?", $url);
$url = str_replace("|AM|", "&", $url);

$db = new MyBase();
$cmd = "Update gickp_Tv Set Url = '$url' Where Voie = $voie ";
$db->Query($cmd);

echo "OK Voie $voie : ".$url;
?>