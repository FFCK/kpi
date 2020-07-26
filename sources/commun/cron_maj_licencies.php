<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

header ('Content-type:text/html; charset=utf-8');
$time1 = time();
echo '<pre>';
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();
$myBdd->ImportPCE2();
// trigger_error("Erreur test 2", E_USER_ERROR);
// print_r($myBdd->m_arrayinfo);
// echo $myBdd->m_arrayinfo[9] . " " . $myBdd->m_arrayinfo[10];
// $time2 = time() - $time1;
// echo "<br /><br />Traitement terminé en ". $time2 . " secondes.";
$msg = date('Y-m-d H:s') . " - " 
	. $myBdd->m_arrayinfo[11] . " : "
	. $myBdd->m_arrayinfo[2] . " " 
	. $myBdd->m_arrayinfo[3] . " " 
	. $myBdd->m_arrayinfo[4];
print_r($myBdd->m_arrayinfo);
$fp = fopen("log_cron.txt","a");
fputs($fp, "\n"); // on va a la ligne
fputs($fp, $msg); // on ecrit la ligne
fclose($fp);
// Envoi du mail
echo '</pre>';
$headers = 'From: KPI <contact@kayak-polo.info>' . "\r\n";
mail('contact@kayak-polo.info', '[KPI-CRON]', $msg, $headers);
