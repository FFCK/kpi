<?php

/**/
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyConfig.php');
include_once('../connector/replace_evenement.php');

include('pclzip.lib.php');

// Importation du Fichier PCE ...
class ImportPCE extends MyPageSecure	 
{	
	var $m_arrayinfo;
	
	function __construct()
	{		
		MyPageSecure::MyPageSecure(6);
		$myBdd = new MyBdd();
		
		$jsondata = '';
		if (isset($_POST['json_data']))
			$jsondata = stripcslashes($_POST['json_data']);
		
		$this->SetTemplate("MAJ Licenciés / Mode Local", "Import", false);
		
		if (isset($_POST['importPCE']))
		{
			$myBdd->ImportPCE("licences.pce");	
			$this->m_tpl->assign('arrayinfo', $myBdd->m_arrayinfo);
		}
		elseif (isset($_POST['Control']) && $_POST['Control'] == 'importPCE2')
		{
			$myBdd->ImportPCE2();	
			$this->m_tpl->assign('arrayinfo', $myBdd->m_arrayinfo);
		}
		elseif (isset($_POST['importCalendrier']) && $_SESSION['Profile'] <= 2)
		{
			$myBdd->ImportCalendrier("calendrier.csv");	
			$this->m_tpl->assign('arrayinfo', $myBdd->m_arrayinfo);
		}
		elseif (isset($_POST['uploadLicenceZip']))
		{
			$this->m_arrayinfo = array();
			array_push($this->m_arrayinfo, 'Upload du fichier ...');
			$this->uploadLicenceZip();
			$this->m_tpl->assign('arrayinfo', $this->m_arrayinfo);
		}
		elseif (isset($_POST['uploadCalendrierCsv']))
		{
			$this->m_arrayinfo = array();
			array_push($this->m_arrayinfo, 'Upload du fichier ...');
			$this->uploadCalendrierCsv();
			$this->m_tpl->assign('arrayinfo', $this->m_arrayinfo);
		}
		elseif (isset($_POST['testPDF']))
		{
			header('Location: http://'.$_SERVER['HTTP_HOST'].MAIN_DIRECTORY.'/fpdf/marque1.php');	
			exit;	
		}
		elseif (isset($_POST['DupliJournee']))
		{
			$myBdd->DupliJournee('N3H1', 'ESSAI2');
			$this->m_tpl->assign('arrayinfo', $myBdd->m_arrayinfo);
		}
		elseif (isset($_POST['Template_YUI']))
		{
			$_SESSION['TPL_EXTENSION'] = '_YUI.tpl';
		}
		elseif (isset($_POST['Template_STD']))
		{
			$_SESSION['TPL_EXTENSION'] = '.tpl';
		}
		
		$msg = '';
		if (strlen($jsondata) > 0)
		{
			$jsondata = str_replace("\\\"", "\"", $jsondata);
			if (strstr($_SERVER['DOCUMENT_ROOT'],'wamp') == false)
				$msg .= "*** IMPORT VERS KPI SERVEUR POLOWEB4 *** <br>";
			else
				$msg .= "*** EXPORT VERS MODE LOCAL **** <br>";
			$msg .= Replace_Evenement($jsondata);
		}
		$this->m_tpl->assign('msg_json', $msg);
		
		if (PRODUCTION)
			$this->m_tpl->assign('production', 'P');
		else
			$this->m_tpl->assign('production', 'W');
		
		$arrayGroupes = array();
		$sql = "SELECT * FROM gickp_Competitions_Groupes ORDER BY id ";
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			array_push($arrayGroupes, $row);
		}		
		$this->m_tpl->assign('arrayGroupes', $arrayGroupes);

		$this->DisplayTemplate('importPCE');
	}
	
	function uploadLicenceZip()
	{			  	
		//Variables
		$dossier = $_SERVER['DOCUMENT_ROOT'].'/PCE/';
		$fichier = basename($_FILES['licencies']['name']);
		$taille_maxi = 2000000; //en octets
		$taille = filesize($_FILES['licencies']['tmp_name']);
		$extensions = array('.zip');
		$extension = strrchr($_FILES['licencies']['name'], '.'); 
		
		//Vérifications de sécurité...
		if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
		{
			$erreur = 'Erreur 1 !';
		}
		if($taille>$taille_maxi) // fichier trop gros
		{
			$erreur = 'Erreur  2 !';
		}
		if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
		{
			if(move_uploaded_file($_FILES['licencies']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
			{
				array_push($this->m_arrayinfo, 'Upload effectué avec succès !');
			}
			else //Sinon (la fonction renvoie FALSE).
			{
				die('Echec de l\'upload ! '.$_FILES['licencies']['tmp_name']);
			}
		}
		else
		{
			die($erreur);
		}
		
		// FONCTION DEZIP
		$zip = new PclZip($dossier . $fichier);
		
		// contrôle du contenu de l'archive
		if (($list = $zip->listContent()) == 0)
		{
			unlink($dossier.$fichier); //On nettoie
			die("Error : ".$zip->errorInfo(true));
		}
		// contrôle du nombre de fichiers contenus
		if(sizeof($list)>1) // plusieurs fichiers dans l'archive !
		{
			unlink($dossier.$fichier); //On nettoie
			die("Erreur 3 !"); 
		}
		// contrôle du nom de fichier
		$dezip_name=explode('.',$list[0]['filename']);
		if($dezip_name[1]<>"pce") // mauvaise extension
		{
			unlink($dossier.$fichier); //On nettoie
			die("Erreur 4 !");
		}
		if(!preg_match("/^licences20/",$dezip_name[0])) // mauvais nom de fichier
		{
			unlink($dossier.$fichier); //On nettoie
			die("Erreur 5 !");
		}
		
		// liste les paramètres des fichiers contenus dans l'archive (inutile à priori)
/*		for ($i=0; $i<sizeof($list); $i++) {
			for(reset($list[$i]); $key = key($list[$i]); next($list[$i])) {
				array_push($this->m_arrayinfo, "File $i / [$key] = ".$list[$i][$key]);
				if($i=0) array_push($this->m_arrayinfo, "ok");
				if($i>0) array_push($this->m_arrayinfo, "pb");
			}
		}
*/		
		// dezip dans le même dossier
		if ($zip->extract(PCLZIP_OPT_PATH, $dossier) == 0) {
			unlink($dossier.$fichier); //On nettoie
			die("Error : ".$zip->errorInfo(true));
		}
		else 
		{
			unlink($dossier.$fichier); //On nettoie (le fichier zip est devenu inutile)
			array_push($this->m_arrayinfo, 'Dezip effectué avec succès !');
		}
		
		rename($dossier.$list[0]['filename'], $dossier."licences.pce"); // on renomme pour traitement suivant
		$myBdd = new MyBdd();
		$myBdd->ImportPCE("licences.pce");	
		
		$this->m_arrayinfo = array_merge($this->m_arrayinfo, $myBdd->m_arrayinfo);
	}
	
	function uploadCalendrierCsv()
	{			  	
		//Variables
		$dossier = $_SERVER['DOCUMENT_ROOT'].'/PCE/';
		$fichier = basename($_FILES['calendrier']['name']);
		$taille_maxi = 200000; //en octets
		$taille = filesize($_FILES['calendrier']['tmp_name']);
		$extensions = array('.csv');
		$extension = strrchr($_FILES['calendrier']['name'], '.'); 
		
		//Vérifications de sécurité...
		if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
		{
			$erreur = 'Erreur 1 !';
		}
		if($taille>$taille_maxi) // fichier trop gros
		{
			$erreur = 'Erreur  2 !';
		}
		if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
		{
			if(move_uploaded_file($_FILES['calendrier']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
			{
				array_push($this->m_arrayinfo, 'Upload effectué avec succès !');
				$myBdd = new MyBdd();
				$myBdd->ImportCalendrier("calendrier.csv");	
				$this->m_arrayinfo = array_merge($this->m_arrayinfo, $myBdd->m_arrayinfo);
			}
			else //Sinon (la fonction renvoie FALSE).
			{
				die('Echec de l\'upload !'.$_FILES['calendrier']['tmp_name']);
			}
		}
		else
		{
			die($erreur);
		}
	}
}		  	

$page = new ImportPCE();


