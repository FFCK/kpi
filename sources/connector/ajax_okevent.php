<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyConfig.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');

session_start();
$user = $_SESSION['User'];

$lstEvenement = '-1';
if (isset($_GET['lst']))
	$lstEvenement = $_GET['lst'];

// Connexion BDD
$myBdd = new MyBdd();

$sql  = "Select * From gickp_Utilisateur ";
$sql .= "Where code = '$user'";

$result = mysql_query($sql, $myBdd-> m_link)or die("SQL-Error :".$sql);
$num_results = mysql_num_rows($result);

if ($num_results != 1)
{
	echo 'KO : Incorrect Login !';
	return;
}
	
$row = mysql_fetch_array($result);	 
$userEvenement = $row['Id_Evenement'];
$UserIdentite = $row["Identite"];
		
$arraySrc = explode(',', $lstEvenement);
$arrayUser = explode('|', $userEvenement);
		
for ($i=0;$i<count($arraySrc);$i++)
{
	$bKo = true;
	for ($j=0;$j<count($arrayUser);$j++)
	{
		if ($arraySrc[$i] == $arrayUser[$j])
		{
			$bKo = false;
			break;
		}
	}
	if ($bKo)
	{
		echo 'KO : Evenement incorrect !';
		return;
	}
}

$myBdd->EvtExport($user, $lstEvenement, 'Import', $UserIdentite, '');

echo 'OK';
?>
