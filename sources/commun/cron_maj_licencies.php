<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

header ('Content-type:text/html; charset=utf-8');
$time1 = time();
echo '<pre>';
include_once('MyBdd.php');
include_once('MyTools.php');

$myBdd = new MyBdd();
$myBdd->ImportPCE2();
$msg = date('Y-m-d H:s') . " - " 
	. $myBdd->m_arrayinfo[11] . " : "
	. $myBdd->m_arrayinfo[2] . " " 
	. $myBdd->m_arrayinfo[3] . " " 
	. $myBdd->m_arrayinfo[4];
print_r($myBdd->m_arrayinfo);
// Log de l'exécution
error_log($msg);
// Envoi du mail
echo '</pre>';
$headers = 'From: KPI <contact@kayak-polo.info>' . "\r\n";
mail('contact@kayak-polo.info', '[KPI-CRON]', $msg, $headers);
