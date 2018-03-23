<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion des Equipes
	
class Equipes extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
        $nomEquipe = '';
        $Code_club = 0;
        $Club =  '';

		$Equipe = utyGetSession('Equipe', 1);
		$Equipe = utyGetPost('Equipe', $Equipe);
		$Equipe = utyGetGet('Equipe', $Equipe);
		$this->m_tpl->assign('Equipe', $Equipe);
		$_SESSION['Equipe'] = $Equipe;
        $Saison = utyGetSaison();
        
        $sql = "SELECT e.Libelle Equipe, e.Code_club, cl.Libelle Club "
                . "FROM gickp_Equipe e, gickp_Club cl "
                . "WHERE e.Numero = $Equipe "
                . "AND cl.Code = e.Code_club";
//        echo $sql;
        $result = $myBdd->Query($sql);
        $row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
        $nomEquipe = $row['Equipe'];
        $Code_club = $row['Code_club'];
        $Club =  $row['Club'];
        $this->m_tpl->assign('nomEquipe', $nomEquipe);
        $this->m_tpl->assign('Code_club', $Code_club);
        $this->m_tpl->assign('Club', $Club);

        $sql  = "SELECT g.id, c.Libelle Competitions, c.Code, c.Code_ref, c.Code_tour, "
                . "c.Code_saison Saison, IF(c.Code_typeclt = 'CHPT', e.Clt_publi, e.CltNiveau_publi) Classt "
                . "FROM gickp_Competitions_Equipes e, gickp_Competitions c, gickp_Competitions_Groupes g, gickp_Club cl "
                . "WHERE c.Code = e.Code_compet "
                . "AND c.Code_ref = g.Groupe "
                . "AND e.Code_club = cl.Code "
                . "AND c.Code_saison = e.Code_saison "
                . "AND c.Statut = 'END' "
                . "AND c.Publication = 'O' "
                . "AND g.Id > 0 "
                . "AND e.Numero = $Equipe "
                . "ORDER BY Saison DESC, g.id, c.Code_tour DESC, c.Code ";
		$arrayPalmares = array();
        $arraySaisons = array();
        $tempSaison = 0;
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
			if($row['Classt'] != 0) {
                if($row['Saison'] != $tempSaison){
                    $arraySaisons[] = array('Saison' => $row['Saison']);
                }
                $tempSaison = $row['Saison'];
				$arrayPalmares[$tempSaison][] = array('Code' => $row['Code'], 'Code_tour' => $row['Code_tour'], 'Code_ref' => $row['Code_ref'], 
                                                    'Competitions' => $row['Competitions'], 'Saison' => $row['Saison'], 'Classt' => $row['Classt'] );
            }
		}
		$this->m_tpl->assign('arraySaisons', $arraySaisons);
		$this->m_tpl->assign('arrayPalmares', $arrayPalmares);
        
        //Images
        $eSeason = $Saison;
        $eLink = $Equipe.'-'.$eSeason;
        $eTeam = 'img/KIP/teams/'.$eLink.'-team.jpg';
        //die($eColors.'<br>'.$eTeam);
        if(is_file('img/KIP/colors/'.$Equipe.'-'.date('Y').'-colors.png')){
            $this->m_tpl->assign('eColors', 'img/KIP/colors/'.$Equipe.'-'.date('Y').'-colors.png');
            $eSeason = date('Y');
        }elseif(is_file('img/KIP/colors/'.$Equipe.'-'.(date('Y')-1).'-colors.png')){
            $this->m_tpl->assign('eColors', 'img/KIP/colors/'.$Equipe.'-'.(date('Y')-1).'-colors.png');
            $eSeason = date('Y')-1;
        }elseif(is_file('img/KIP/colors/'.$Equipe.'-'.(date('Y')-2).'-colors.png')){
            $this->m_tpl->assign('eColors', 'img/KIP/colors/'.$Equipe.'-'.(date('Y')-2).'-colors.png');
            $eSeason = date('Y')-2;
        }elseif(is_file('img/KIP/colors/'.$Equipe.'-'.(date('Y')-3).'-colors.png')){
            $this->m_tpl->assign('eColors', 'img/KIP/colors/'.$Equipe.'-'.(date('Y')-3).'-colors.png');
            $eSeason = date('Y')-3;
        }elseif(is_file('img/KIP/logo/'.$Code_club.'-logo.png')){
            $this->m_tpl->assign('eLogo', 'img/KIP/logo/'.$Code_club.'-logo.png');
        }
        $this->m_tpl->assign('eSeason', $eSeason);
        
        if(is_file('img/KIP/teams/'.$Equipe.'-'.date('Y').'-team.jpg')){
            $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.date('Y').'-team.jpg');
        }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-1).'-team.jpg')){
            $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-1).'-team.jpg');
        }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-2).'-team.jpg')){
            $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-2).'-team.jpg');
        }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-3).'-team.jpg')){
            $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-3).'-team.jpg');
        }
        
        
	}
	

	function Equipes()
	{			
	  MyPage::MyPage();
		
		$this->SetTemplate("Equipes", "Equipes", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpequipes');
	}
}		  	

$page = new Equipes();

