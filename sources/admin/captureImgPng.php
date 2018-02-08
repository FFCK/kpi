<?php
$debutTraitement = time();

include_once('../commun/MyBdd.php');
$myBdd = new MyBdd();

$msg = '';
$code = 'SAVOIE';
$saison = 1987;
$logoLink = "https://www.kayak-polo.info/wordpress/wp-content/gallery/logos/savoie1987.gif";
$folder = "../images/";
$logoLink2 = $myBdd->captureImg($logoLink, 'L', $code, $saison, $folder);
$tempsIntermediaire = time() - $debutTraitement;
$msg .= "Conversion : $logoLink => $folder $logoLink2<br>";
$msg .= "Traitement terminé en " . $tempsIntermediaire . "s.";
echo $msg;
exit;