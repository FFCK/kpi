<?php
	header ('Content-type:text/html; charset=utf-8');
	$time1 = time();
	include_once('../commun/MyBdd.php');
	
	$myBdd = new MyBdd();
	$myBdd->ImportPCE2();
	// print_r($myBdd->m_arrayinfo);
	// echo $myBdd->m_arrayinfo[9] . " " . $myBdd->m_arrayinfo[10];
	// $time2 = time() - $time1;
	// echo "<br /><br />Traitement terminé en ". $time2 . " secondes.";
    $msg = date('Y-m-d H:s') . " - " . $myBdd->m_arrayinfo[2] . " " . $myBdd->m_arrayinfo[3] . " " . $myBdd->m_arrayinfo[4] . " " . $myBdd->m_arrayinfo[11];
	$fp = fopen("log_cron.txt","a");
	fputs($fp, "\n"); // on va a la ligne
	fputs($fp, $msg); // on ecrit la ligne
	fclose($fp);
    // Envoi du mail
    $headers = 'From: KPI <laurent@kayak-polo.info>' . "\r\n";
    mail('lgarrigue@gmail.com', '[KPI-CRON]', $msg, $headers);