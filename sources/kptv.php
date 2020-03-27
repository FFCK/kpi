<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Tv
	
class Tv extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeEvt = utyGetSession('codeEvt', 0);
		$codeEvt = utyGetPost('codeEvt', $codeEvt);
        $_SESSION['codeEvt'] = $codeEvt;
        $this->m_tpl->assign('codeEvt', $codeEvt);

		$filtrePres = utyGetSession('filtrePres', '');
		$filtrePres = utyGetPost('filtrePres', $filtrePres);
        $_SESSION['filtrePres'] = $filtrePres;
        $this->m_tpl->assign('filtrePres', $filtrePres);
        		
		$filtreCompet = utyGetSession('filtreCompet', '');
		$filtreCompet = utyGetPost('filtreCompet', $filtreCompet);
        $_SESSION['filtreCompet'] = $filtreCompet;
        $this->m_tpl->assign('filtreCompet', $filtreCompet);
		
		$filtreChannel = utyGetSession('filtreChannel', '');
		$filtreChannel = utyGetPost('filtreChannel', $filtreChannel);
        $_SESSION['filtreChannel'] = $filtreChannel;
        $this->m_tpl->assign('filtreChannel', $filtreChannel);
		
		$filtreMatch = utyGetSession('filtreMatch', '');
		$filtreMatch = utyGetPost('filtreMatch', $filtreMatch);
        $_SESSION['filtreMatch'] = $filtreMatch;
        $this->m_tpl->assign('filtreMatch', $filtreMatch);
		
		$filtrePres2 = utyGetSession('filtrePres2', '');
		$filtrePres2 = utyGetPost('filtrePres2', $filtrePres2);
        $_SESSION['filtrePres2'] = $filtrePres2;
        $this->m_tpl->assign('filtrePres2', $filtrePres2);
        
		$filtreCompet2 = utyGetSession('filtreCompet2', '');
		$filtreCompet2 = utyGetPost('filtreCompet2', $filtreCompet2);
        $_SESSION['filtreCompet2'] = $filtreCompet2;
        $this->m_tpl->assign('filtreCompet2', $filtreCompet2);
		
		$filtreChannel2 = utyGetSession('filtreChannel2', '');
		$filtreChannel2 = utyGetPost('filtreChannel2', $filtreChannel2);
        $_SESSION['filtreChannel2'] = $filtreChannel2;
        $this->m_tpl->assign('filtreChannel2', $filtreChannel2);
		
		$filtreMatch2 = utyGetSession('filtreMatch2', '');
		$filtreMatch2 = utyGetPost('filtreMatch2', $filtreMatch2);
        $_SESSION['filtreMatch2'] = $filtreMatch2;
        $this->m_tpl->assign('filtreMatch2', $filtreMatch2);
		
		$filtrePres3 = utyGetSession('filtrePres3', '');
		$filtrePres3 = utyGetPost('filtrePres3', $filtrePres3);
        $_SESSION['filtrePres3'] = $filtrePres3;
        $this->m_tpl->assign('filtrePres3', $filtrePres3);
        
		$filtreCompet3 = utyGetSession('filtreCompet3', '');
		$filtreCompet3 = utyGetPost('filtreCompet3', $filtreCompet3);
        $_SESSION['filtreCompet3'] = $filtreCompet3;
        $this->m_tpl->assign('filtreCompet3', $filtreCompet3);
		
		$filtreChannel3 = utyGetSession('filtreChannel3', '');
		$filtreChannel3 = utyGetPost('filtreChannel3', $filtreChannel3);
        $_SESSION['filtreChannel3'] = $filtreChannel3;
        $this->m_tpl->assign('filtreChannel3', $filtreChannel3);
		
		$filtreMatch3 = utyGetSession('filtreMatch3', '');
		$filtreMatch3 = utyGetPost('filtreMatch3', $filtreMatch3);
        $_SESSION['filtreMatch3'] = $filtreMatch3;
        $this->m_tpl->assign('filtreMatch3', $filtreMatch3);
		
		$filtrePres4 = utyGetSession('filtrePres4', '');
		$filtrePres4 = utyGetPost('filtrePres4', $filtrePres4);
        $_SESSION['filtrePres4'] = $filtrePres4;
        $this->m_tpl->assign('filtrePres4', $filtrePres4);
        
		$filtreCompet4 = utyGetSession('filtreCompet4', '');
		$filtreCompet4 = utyGetPost('filtreCompet4', $filtreCompet4);
        $_SESSION['filtreCompet4'] = $filtreCompet4;
        $this->m_tpl->assign('filtreCompet4', $filtreCompet4);
		
		$filtreChannel4 = utyGetSession('filtreChannel4', '');
		$filtreChannel4 = utyGetPost('filtreChannel4', $filtreChannel4);
        $_SESSION['filtreChannel4'] = $filtreChannel4;
        $this->m_tpl->assign('filtreChannel4', $filtreChannel4);
		
		$filtreMatch4 = utyGetSession('filtreMatch4', '');
		$filtreMatch4 = utyGetPost('filtreMatch4', $filtreMatch4);
        $_SESSION['filtreMatch4'] = $filtreMatch4;
        $this->m_tpl->assign('filtreMatch4', $filtreMatch4);
		
		$jour = utyGetSession('jour', '');
		$jour = utyGetPost('jour', $jour);
        $_SESSION['jour'] = $jour;
        $this->m_tpl->assign('jour', $jour);
        
        // Evts
        $sql  = "SELECT * "
            . "FROM gickp_Evenement "
            . "WHERE Publication = 'O' "
            . "ORDER BY Date_debut DESC ";
        $arrayEvts = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result)){ 
            if( $row['Id'] == $codeEvt ) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            array_push($arrayEvts, $row);
        }
        $this->m_tpl->assign('arrayEvts', $arrayEvts);
        
        // Matchs
        $sql  = "SELECT m.*, j.Code_competition, j.Code_saison, j.Phase, 
            m.Id_equipeA, m.Id_equipeB,
            ce1.Libelle equipeA, ce2.Libelle equipeB 
            FROM gickp_Evenement_Journees evt, gickp_Journees j, gickp_Matchs m 
            LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON m.Id_equipeA = ce1.Id 
            LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON m.Id_equipeB = ce2.Id 
            WHERE evt.Id_evenement = $codeEvt 
            AND evt.Id_journee = m.Id_journee 
            AND j.Id = m.Id_journee 
            ORDER BY Date_match, Heure_match, Numero_ordre ";
        $arrayEquipes = array();
        $arrayEquipes2 = array();
        $arrayEquipes3 = array();
        $arrayEquipes4 = array();
        $arrayMatchs = array();
        $arrayMatchs2 = array();
        $arrayMatchs3 = array();
        $arrayMatchs4 = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result)){
            $arrayCompet[] = $row['Code_competition'];
            $saison = $row['Code_saison'];
            $arrayJours[] = $row['Date_match'];
            //            echo $row['Code_competition'] . ' ' . $row['Date_match'] . ' ' . $row['Heure_match'] . ' #' . $row['Numero_ordre'] . ' ' . $row['equipeA'] . ' ' . $row['equipeB'] . '<br>';
            if( ($row['Date_match'] == $jour || $jour == '')
                    && ($row['Code_competition'] == $filtreCompet || $filtreCompet == '') ) {
                if(array_search($row['Id_equipeA'], array_column($arrayEquipes, 'id_equipe')) === false) {
                    $arrayEquipes[] = array('id_equipe' => $row['Id_equipeA'], 'libelle_equipe' => $row['equipeA']);
                }
                if(array_search($row['Id_equipeB'], array_column($arrayEquipes, 'id_equipe')) === false) {
                    $arrayEquipes[] = array('id_equipe' => $row['Id_equipeB'], 'libelle_equipe' => $row['equipeB']);
                }
                array_push($arrayMatchs, $row);
            }
            if( ($row['Date_match'] == $jour || $jour == '')
                    && ($row['Code_competition'] == $filtreCompet2 || $filtreCompet2 == '') ) {
                if(array_search($row['Id_equipeA'], array_column($arrayEquipes2, 'id_equipe')) === false) {
                    $arrayEquipes2[] = array('id_equipe' => $row['Id_equipeA'], 'libelle_equipe' => $row['equipeA']);
                }
                if(array_search($row['Id_equipeB'], array_column($arrayEquipes2, 'id_equipe')) === false) {
                    $arrayEquipes2[] = array('id_equipe' => $row['Id_equipeB'], 'libelle_equipe' => $row['equipeB']);
                }
                array_push($arrayMatchs2, $row);
            }
            if( ($row['Date_match'] == $jour || $jour == '')
                    && ($row['Code_competition'] == $filtreCompet3 || $filtreCompet3 == '') ) {
                if(array_search($row['Id_equipeA'], array_column($arrayEquipes3, 'id_equipe')) === false) {
                    $arrayEquipes3[] = array('id_equipe' => $row['Id_equipeA'], 'libelle_equipe' => $row['equipeA']);
                }
                if(array_search($row['Id_equipeB'], array_column($arrayEquipes3, 'id_equipe')) === false) {
                    $arrayEquipes3[] = array('id_equipe' => $row['Id_equipeB'], 'libelle_equipe' => $row['equipeB']);
                }
                array_push($arrayMatchs3, $row);
            }
            if( ($row['Date_match'] == $jour || $jour == '')
                    && ($row['Code_competition'] == $filtreCompet4 || $filtreCompet4 == '') ) {
                if(array_search($row['Id_equipeA'], array_column($arrayEquipes4, 'id_equipe')) === false) {
                    $arrayEquipes4[] = array('id_equipe' => $row['Id_equipeA'], 'libelle_equipe' => $row['equipeA']);
                }
                if(array_search($row['Id_equipeB'], array_column($arrayEquipes4, 'id_equipe')) === false) {
                    $arrayEquipes4[] = array('id_equipe' => $row['Id_equipeB'], 'libelle_equipe' => $row['equipeB']);
                }
                array_push($arrayMatchs4, $row);
            }
            
        }
        $this->m_tpl->assign('arrayEvts', $arrayEvts);
        $this->m_tpl->assign('arrayEquipes', $arrayEquipes);
        $this->m_tpl->assign('arrayEquipes2', $arrayEquipes2);
        $this->m_tpl->assign('arrayEquipes3', $arrayEquipes3);
        $this->m_tpl->assign('arrayEquipes4', $arrayEquipes4);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('arrayMatchs2', $arrayMatchs2);
        $this->m_tpl->assign('arrayMatchs3', $arrayMatchs3);
        $this->m_tpl->assign('arrayMatchs4', $arrayMatchs4);
        $this->m_tpl->assign('saison', $saison);
        if(is_array($arrayCompet)) {
            $arrayCompet = array_keys(array_flip($arrayCompet));
            $arrayJours = array_keys(array_flip($arrayJours));
            $this->m_tpl->assign('arrayCompet', $arrayCompet);
            $this->m_tpl->assign('arrayJours', $arrayJours);
        }
        
	}
	
    
	// Tv 		
	function __construct()
    {
        MyPageSecure::MyPageSecure(1);
		
		$this->SetTemplate("KPI Tv control", "Matchs", true);
		$this->Load();
//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNewWide('kptv');
	}
}		  	

$page = new Tv();
