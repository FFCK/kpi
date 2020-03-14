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
        if (isset($_POST['theLimit']))
            $theLimit = $_POST['theLimit'];
        $_SESSION['theLimit'] = $theLimit;
        $this->m_tpl->assign('theLimit', $theLimit);

        $theUser = utyGetSession('theUser', '');
        if (isset($_POST['theUser']))
            $theUser = $_POST['theUser'];
        $_SESSION['theUser'] = $theUser;
        $this->m_tpl->assign('theUser', $theUser);

        $theAction = utyGetSession('theAction', '');
        if (isset($_POST['theAction']))
            $theAction = $_POST['theAction'];
        $_SESSION['theAction'] = $theAction;
        $this->m_tpl->assign('theAction', $theAction);

        $theSaison = utyGetSession('theSaison', '');
        if (isset($_POST['theSaison']))
            $theSaison = $_POST['theSaison'];
        $_SESSION['theSaison'] = $theSaison;
        $this->m_tpl->assign('theSaison', $theSaison);

        $theCompet = utyGetSession('theCompet', '');
        if (isset($_POST['theCompet']))
            $theCompet = $_POST['theCompet'];
        $_SESSION['theCompet'] = $theCompet;
        $this->m_tpl->assign('theCompet', $theCompet);

        // Chargement des Evenements
        $myBdd = new MyBdd();
        $sql  = "Select j.Id, j.Dates, j.Users, j.Actions, j.Saisons, j.Competitions, j.Evenements, j.Journees, j.Matchs, j.Journal, ";
        $sql .= "u.Identite, u.Fonction ";
        $sql .= "From gickp_Journal j, gickp_Utilisateur u ";
        $sql .= "Where u.Code = j.Users ";
        $sql .= "And j.Users Like '".$theUser."%' ";
        $sql .= "And j.Actions Like '".$theAction."%' ";
        $sql .= "And j.Saisons Like '".$theSaison."%' ";
        $sql .= "And j.Competitions Like '".$theCompet."%' ";
        $sql .= "Order By j.Dates Desc ";	 
        $sql .= "Limit $theLimit ";	 

        $arrayJournal = array();
        $result = $myBdd->Query($sql);
        while($row = $myBdd->FetchArray($result)) {
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
        $sql  = "Select Distinct j.Users, u.Identite, u.Fonction ";
        $sql .= "From gickp_Journal j, gickp_Utilisateur u ";
        $sql .= "Where u.Code = j.Users ";
        $sql .= "Order By u.Identite ";	 

        $arrayUsers = array();
        $result = $myBdd->Query($sql);
        while($row = $myBdd->FetchArray($result)) {
            array_push($arrayUsers, array( 'Code' => $row['Users'], 
                        'Identite' => $row['Identite'],  
                        'Fonction' => $row['Fonction']));
        }
        $this->m_tpl->assign('arrayUsers', $arrayUsers);

        // Chargement des actions
        $sql  = "SELECT Distinct j.Actions "
                . "FROM gickp_Journal j "
                . "ORDER BY j.Actions ";	 

        $arrayActions = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result)) {
            array_push($arrayActions, array('Action' => $row['Actions']));
        }
        $this->m_tpl->assign('arrayActions', $arrayActions);

	}
	
	function __construct()
	{			
        MyPageSecure::MyPageSecure(2);
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];

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
