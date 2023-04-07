<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Evenements

class GestionJournal extends MyPageSecure	 
{	
	function Load()
	{
        $theLimit = utyGetSession('theLimit', 50);
        $theLimit = (int) utyGetPost('theLimit', $theLimit);
        $_SESSION['theLimit'] = $theLimit;
        $this->m_tpl->assign('theLimit', $theLimit);

        $theUser = utyGetSession('theUser', '');
        $theUser = utyGetPost('theUser', $theUser);
        $_SESSION['theUser'] = $theUser;
        $this->m_tpl->assign('theUser', $theUser);

        $theAction = utyGetSession('theAction', '');
        $theAction = utyGetPost('theAction', $theAction);
        $_SESSION['theAction'] = $theAction;
        $this->m_tpl->assign('theAction', $theAction);

        $theSaison = utyGetSession('theSaison', '');
        $theSaison = utyGetPost('theSaison', $theSaison);
        $_SESSION['theSaison'] = $theSaison;
        $this->m_tpl->assign('theSaison', $theSaison);

        $theCompet = utyGetSession('theCompet', '');
        $theCompet = utyGetPost('theCompet', $theCompet);
        $_SESSION['theCompet'] = $theCompet;
        $this->m_tpl->assign('theCompet', $theCompet);

        // Chargement des Evenements
        $myBdd = new MyBdd();
        $sql = "SELECT j.Id, j.Dates, j.Users, j.Actions, j.Saisons, j.Competitions, 
            j.Evenements, j.Journees, j.Matchs, j.Journal, u.Identite, u.Fonction 
            FROM kp_journal j, kp_user u 
            WHERE u.Code = j.Users 
            AND j.Users LIKE ? 
            AND j.Actions LIKE ? 
            AND j.Saisons LIKE ? 
            AND j.Competitions LIKE ? 
            ORDER BY j.Dates DESC 
            LIMIT $theLimit ";	 
        $arrayJournal = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($theUser.'%', $theAction.'%', $theSaison.'%', $theCompet.'%'));
        while ($row = $result->fetch()) {
            array_push($arrayJournal, array( 'Id' => $row['Id'], 
                        'Dates' => $row['Dates'],  
                        'Identite' => $row['Identite'],  
                        'Fonction' => $row['Fonction'],  
                        'Actions' => $row['Actions'],  
                        'Saisons' => $row['Saisons'],  
                        'Competitions' => $row['Competitions'],  
                        'Evenements' => $row['Evenements'],  
                        'Journees' => $row['Journees'],  
                        'Matchs' => $row['Matchs'],  
                        'Journal' => $row['Journal']));
        }
        $this->m_tpl->assign('arrayJournal', $arrayJournal);

        // Chargement des Utilisateurs
        $sql = "SELECT DISTINCT j.Users, u.Identite, u.Fonction 
            FROM kp_journal j, kp_user u 
            WHERE u.Code = j.Users 
            ORDER BY u.Identite ";	 
        $arrayUsers = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
        while ($row = $result->fetch()) {
            array_push($arrayUsers, array( 
                'Code' => $row['Users'], 
                'Identite' => $row['Identite'],  
                'Fonction' => $row['Fonction']
            ));
        }
        $this->m_tpl->assign('arrayUsers', $arrayUsers);

        // Chargement des actions
        $sql = "SELECT DISTINCT j.Actions 
            FROM kp_journal j 
            ORDER BY j.Actions ";	 
        $arrayActions = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
        while ($row = $result->fetch()) {
            array_push($arrayActions, array('Action' => $row['Actions']));
        }
        $this->m_tpl->assign('arrayActions', $arrayActions);

	}
	
	function __construct()
	{			
        parent::__construct(2);
		
		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0)
		{
			// if ($Cmd == 'Add')
			// 	($_SESSION['Profile'] <= 2) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Journal", "Utilisateurs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionJournal');
	}
}		  	

$page = new GestionJournal();
