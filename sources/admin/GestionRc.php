<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des RC

class GestionRc extends MyPageSecure	 
{	
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetSession('lang') == 'en') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }
		
		$filtreCompet = utyGetPost('Code_competition', '');
		$filtreCompet = utyGetGet('Compet', $filtreCompet);
		$filtreCompet == '' ? $codeCompet = utyGetSession('codeCompet', -1) : $codeCompet = $filtreCompet;
		$codeSaison = $myBdd->GetActiveSaison();
        
        $idRc = (int) utyGetSession('idRc', -1);
		
		// Chargement des Saisons ...
		$sql  = "SELECT Code 
			FROM kp_saison 
            WHERE Code > '1900' 
			ORDER BY Code DESC ";	 
		$arraySaison = array();
		foreach ($myBdd->pdo->query($sql) as $row) {
			if ($row['Code'] == $codeSaison) {
                $row['Selected'] = 'selected';
			}
			array_push($arraySaison, $row);
		}
        $this->m_tpl->assign('arraySaison', $arraySaison);
        $this->m_tpl->assign('codeSaison', $codeSaison);
        $this->m_tpl->assign('filtreCompet', $filtreCompet);
        
		//Filtre affichage niveau
		$_SESSION['AfficheNiveau'] = utyGetSession('AfficheNiveau','');
		$_SESSION['AfficheNiveau'] = utyGetPost('AfficheNiveau',$_SESSION['AfficheNiveau']);
		$this->m_tpl->assign('AfficheNiveau', $_SESSION['AfficheNiveau']);

		//Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

		// Chargement des Compétitions ...
        $arrayCompetitions = [];
        $arrayCompetitionList = [];
		$sqlAfficheCompet = '';
		$arrayAfficheCompet = [];
		if ($AfficheCompet == 'N') {
			$sqlAfficheCompet = " AND c.Code LIKE 'N%' ";
		} elseif ($AfficheCompet == 'CF') {
			$sqlAfficheCompet = " AND c.Code LIKE 'CF%' ";
		} elseif ($AfficheCompet == 'M') {
			$sqlAfficheCompet = " AND c.Code_ref = 'M' ";
		} elseif ($AfficheCompet > 0) {
			$sqlAfficheCompet = " AND g.section = ? ";
			$arrayAfficheCompet = [$AfficheCompet];
		}
		$sql = "SELECT c.Code 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_saison = ?  
			AND c.Code_niveau LIKE ? 
			AND c.Code_ref = g.Groupe 
			$sqlAfficheCompet 
			ORDER BY c.Code_saison, g.section, g.ordre, 
				COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array_merge(
			[$codeSaison], 
			[utyGetSession('AfficheNiveau').'%'], 
			$arrayAfficheCompet
		));
		while ($row = $result->fetch()) { 
			array_push($arrayCompetitions, $row);
			array_push($arrayCompetitionList, $row['Code']);
		}
        $this->m_tpl->assign('arrayCompetitions', $arrayCompetitions);

		// Chargement des Rcs
		$arrayRc = array();
		$Identite = '';
		$sql = "SELECT rc.Id, rc.Code_competition, rc.Code_saison, rc.Ordre, rc.Matric, 
            lc.Nom, lc.Prenom, lc.Numero_club, u.Mail  
			FROM kp_rc rc 
            LEFT OUTER JOIN kp_licence lc ON (rc.Matric = lc.Matric) 
            LEFT OUTER JOIN kp_user u ON (rc.Matric = u.Code) 
            WHERE rc.Code_saison = $codeSaison 
			ORDER BY rc.Code_saison DESC, rc.Code_competition, rc.Ordre ";
		foreach ($myBdd->pdo->query($sql) as $row) {
			if ($idRc == $row['Id']) {
                $row['Selected'] = 'Selected';
                $Identite = $row['Prenom'] . ' ' . $row['Nom'];
			}
			if (in_array($row['Code_competition'], $arrayCompetitionList) || $row['Code_competition'] == '- CNA -') {
				array_push($arrayRc, $row);
			}
		}
		$this->m_tpl->assign('arrayRc', $arrayRc);
        $this->m_tpl->assign('idRc', $idRc);

		if ($idRc != -1) {
			$this->m_tpl->assign('selectSaison', $_SESSION['selectSaison']);
			$this->m_tpl->assign('selectCompetition', $_SESSION['selectCompetition']);
			$this->m_tpl->assign('selectMatric', $_SESSION['selectMatric']);
			$this->m_tpl->assign('selectOrdre', $_SESSION['selectOrdre']);
			$this->m_tpl->assign('Identite', $Identite);
		} else {
			$this->m_tpl->assign('selectSaison', $codeSaison);
			$this->m_tpl->assign('selectCompetition', $codeCompet);
			$this->m_tpl->assign('selectMatric', 0);
			$this->m_tpl->assign('selectOrdre', 1);
			$this->m_tpl->assign('Identite', '');
		}

	}
	
	function SetSessionSaison()
	{
		$codeSaison = utyGetPost('ParamCmd', '');
		if (strlen($codeSaison) == 0)
			return;
		
		$_SESSION['Saison'] = $codeSaison;
	}

	function Add()
	{
		$Code_saison = utyGetPost('Code_saison');
		$Code_competition = utyGetPost('Code_competition');
		$Matric = utyGetPost('Matric');
		$Ordre = utyGetPost('Ordre');

		$myBdd = $this->myBdd;

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql  = "INSERT INTO kp_rc (Code_saison, Code_competition, Matric, Ordre) 
				VALUES (?,?,?,?) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($Code_saison, $Code_competition, $Matric, $Ordre));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Ajout Rc, $Matric" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Ajout Rc', '', '', null, null, null, $Matric);
		return;
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
		
		$myBdd = $this->myBdd;
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM kp_rc 
				WHERE Id IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression Rc, $ParamCmd" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Suppression Rc', '', '', $ParamCmd);
	}
		
	function RazRc()
	{
			unset($_SESSION['selectSaison']);
			unset($_SESSION['selectCompetition']);
			unset($_SESSION['selectMatric']);
			unset($_SESSION['selectOrdre']);
			unset($_SESSION['idRc']);
	}

	function ParamRc()
	{
		$idRc = utyGetPost('ParamCmd', -1);
		$_SESSION['idRc'] = $idRc;
		
		$myBdd = $this->myBdd;

		$sql  = "SELECT * 
			FROM kp_rc 
			WHERE Id = ? ";		
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idRc));
		if ($row = $result->fetch()) {
			$_SESSION['idRc'] = $idRc;
			$_SESSION['selectSaison'] = $row['Code_saison'];
			$_SESSION['selectCompetition'] = $row['Code_competition'];
			$_SESSION['selectMatric'] = $row['Matric'];
			$_SESSION['selectOrdre'] = $row['Ordre'];
		}
	}

	function UpdateRc()
	{
		$idRc = utyGetPost('idRc');
		$Code_saison = utyGetPost('Code_saison');
		$Code_competition = utyGetPost('Code_competition');
		$Matric = utyGetPost('Matric');
		$Ordre = utyGetPost('Ordre');
		
		$myBdd = $this->myBdd;

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "UPDATE kp_rc 
				SET Code_saison = ?, Code_competition = ?, Matric = ?, Ordre = ?
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($Code_saison, $Code_competition, $Matric, $Ordre, $idRc));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Modif Rc, $idRc" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$this->RazRc();
		$myBdd->utyJournal('Modif Rc', '', '', $idRc);
	}

	function __construct()
	{
	  	parent::__construct(4);
		
		$this->myBdd = new MyBdd();

		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ParamRc')
				($_SESSION['Profile'] <= 2) ? $this->ParamRc() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'UpdateRc')
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->UpdateRc() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'RazRc')
				($_SESSION['Profile'] <= 2) ? $this->RazRc() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

            if ($Cmd == 'SessionSaison')
				($_SESSION['Profile'] <= 2) ? $this->SetSessionSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
            if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_RC", "Competitions", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionRc');
	}
}		  	

$page = new GestionRc();
