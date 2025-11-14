<?php

/**/
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyConfig.php');

// Importation du Fichier PCE ...
class ImportPCE extends MyPageSecure
{
	var $m_arrayinfo;

	function __construct()
	{
		parent::__construct(6);
		$myBdd = new MyBdd();

		$this->SetTemplate("Import PCE", "Import", false);
		$arrayinfo = array();

		switch (true) {
			case isset($_POST['testPDF']):
				header('Location: http://' . $_SERVER['HTTP_HOST'] . MAIN_DIRECTORY . '/lib/fpdf/marque1.php');
				exit;
			case isset($_POST['DupliJournee']):
				$myBdd->DupliJournee('N3H1', 'ESSAI2');
				$arrayinfo = $myBdd->m_arrayinfo;
				break;
			case isset($_POST['Template_YUI']):
				$_SESSION['TPL_EXTENSION'] = '_YUI.tpl';
				break;
			case isset($_POST['Template_STD']):
				$_SESSION['TPL_EXTENSION'] = '.tpl';
				break;
		}
		$this->m_tpl->assign('arrayinfo', $arrayinfo);

		$arrayGroupes = array();
		$sql = "SELECT *
			FROM kp_groupe
			ORDER BY id ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			array_push($arrayGroupes, $row);
		}
		$this->m_tpl->assign('arrayGroupes', $arrayGroupes);

		// Redirect notice for moved features
		$this->m_tpl->assign('redirect_notice', 'Les fonctionnalités "Mise à jour Licenciés", "Mise à jour Calendrier fédéral" et "Import vers mode local" ont été déplacées vers la page <a href="GestionOperations.php">Gestion des Opérations</a>.');

		$this->DisplayTemplate('importPCE');
	}
}

$page = new ImportPCE();
