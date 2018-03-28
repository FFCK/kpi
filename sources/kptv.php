<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Tv
	
class Tv extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeEvt = utyGetSession('codeEvt', 0);
		$codeEvt = utyGetPost('codeEvt', $codeEvt);
        $_SESSION['codeEvt'] = $codeEvt;
        $this->m_tpl->assign('codeEvt', $codeEvt);
		
		$filtreCompet = utyGetSession('filtreCompet', '');
		$filtreCompet = utyGetPost('filtreCompet', $filtreCompet);
        $_SESSION['filtreCompet'] = $filtreCompet;
        $this->m_tpl->assign('filtreCompet', $filtreCompet);
		
		$filtreChannel = utyGetSession('filtreChannel', '');
		$filtreChannel = utyGetPost('filtreChannel', $filtreChannel);
        $_SESSION['filtreChannel'] = $filtreChannel;
        $this->m_tpl->assign('filtreChannel', $filtreChannel);
		
		$filtrePres = utyGetSession('filtrePres', '');
		$filtrePres = utyGetPost('filtrePres', $filtrePres);
        $_SESSION['filtrePres'] = $filtrePres;
        $this->m_tpl->assign('filtrePres', $filtrePres);
        
		$jour = utyGetSession('jour', 0);
		$jour = utyGetPost('jour', $jour);
        $_SESSION['jour'] = $jour;
        $this->m_tpl->assign('jour', $jour);
        
        $sql  = "SELECT * "
            . "FROM gickp_Evenement "
            . "WHERE Publication = 'O' "
            . "ORDER BY Date_debut ";
        $arrayEvts = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
            if( $row['Id'] == $codeEvt ) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            array_push($arrayEvts, $row);
        }
        $this->m_tpl->assign('arrayEvts', $arrayEvts);
        
        $sql  = "SELECT m.*, j.Code_competition, ce1.Libelle equipeA, ce2.Libelle equipeB "
                . "FROM gickp_Evenement_Journees evt, gickp_Journees j, gickp_Matchs m "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON m.Id_equipeA = ce1.Id "
                . "LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON m.Id_equipeB = ce2.Id "
                . "WHERE evt.Id_evenement = $codeEvt "
                . "AND evt.Id_journee = m.Id_journee "
                . "AND j.Id = m.Id_journee "
                . "ORDER BY Date_match, Heure_match, Numero_ordre ";
        $arrayMatchs = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
            $arrayCompet[] = $row['Code_competition'];
            $arrayJours[] = $row['Date_match'];
//            echo $row['Code_competition'] . ' ' . $row['Date_match'] . ' ' . $row['Heure_match'] . ' #' . $row['Numero_ordre'] . ' ' . $row['equipeA'] . ' ' . $row['equipeB'] . '<br>';
            if( ($row['Date_match'] == $jour || $jour == 0)
                    && ($row['Code_competition'] == $filtreCompet || $filtreCompet == '') ) {
                array_push($arrayMatchs, $row);
            }
            
        }
        $arrayCompet = array_keys(array_flip($arrayCompet));
        $arrayJours = array_keys(array_flip($arrayJours));
        $this->m_tpl->assign('arrayEvts', $arrayEvts);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('arrayCompet', $arrayCompet);
        $this->m_tpl->assign('arrayJours', $arrayJours);
        
	}
	
    
	// Tv 		
	function Tv()
	{			
        MyPage::MyPage();
		
		$this->SetTemplate("Tv", "Matchs", true);
		$this->Load();
//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNewWide('kptv');
	}
}		  	

$page = new Tv();
