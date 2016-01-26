<?php

$ftp_server="***";
$ftp_user_name="***";
$ftp_user_pass="***";

// set up basic connection
$idFTP = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($idFTP, $ftp_user_name, $ftp_user_pass);
			
// Vérification de la connexion
if ((!$idFTP) || (!$login_result)) {
	die("Echec de la connexion FTP !");
}
			
ftp_chdir($idFTP, "live/cache");

$fileName = "1_terrain.json";
$fp = fopen($_SERVER['DOCUMENT_ROOT']."/live/cache/$fileName", 'r');
if ($fp == false)
		echo "FOPENNNNNNNNNNNNNNNNNNNN";
else
	echo "FOPEN OK";

if (!ftp_fput($idFTP, $fileName, $fp, FTP_BINARY)) 
{
	echo "There was a problem while uploading $fileName\n";
	exit;
}

fclose($fp);
	
