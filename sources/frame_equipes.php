<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Equipes
	
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
		$Equipe = (int) utyGetGet('Equipe', $Equipe);
		$this->m_tpl->assign('Equipe', $Equipe);
		$_SESSION['Equipe'] = $Equipe;
        
        $Saison = $myBdd->GetActiveSaison();
        $codeSaison = (int) utyGetGet('Saison', $Saison);
        
        $codeCompet = utyGetGet('Compet', '');
        $this->m_tpl->assign('codeCompet', $codeCompet);
        
        $this->m_tpl->assign('Css', utyGetGet('Css', ''));
            
        if ($Equipe > 0) {

            // Equipe
            $sql = "SELECT e.Libelle Equipe, e.Code_club, cl.Libelle Club 
                FROM gickp_Equipe e, gickp_Club cl 
                WHERE e.Numero = ? 
                AND cl.Code = e.Code_club";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Equipe));
            $row = $result->fetch();
            $nomEquipe = $row['Equipe'];
            $Code_club = $row['Code_club'];
            $Club =  $row['Club'];
            $this->m_tpl->assign('nomEquipe', $nomEquipe);
            $this->m_tpl->assign('Code_club', $Code_club);
            $this->m_tpl->assign('Club', $Club);

            // Palmares
            $sql  = "SELECT g.id, c.Libelle Competitions, c.Code, c.Code_ref, c.Code_tour, 
                    c.Code_saison Saison, 
                    IF(c.Code_typeclt = 'CHPT', e.Clt_publi, e.CltNiveau_publi) Classt 
                FROM gickp_Competitions_Equipes e, gickp_Competitions c, 
                    gickp_Competitions_Groupes g, gickp_Club cl 
                WHERE c.Code = e.Code_compet 
                AND c.Code_ref = g.Groupe 
                AND e.Code_club = cl.Code 
                AND c.Code_saison = e.Code_saison 
                AND c.Statut = 'END' 
                AND c.Publication = 'O' 
                AND g.Id > 0 
                AND e.Numero = ? 
                ORDER BY Saison DESC, g.id, c.Code_tour DESC, c.Code ";
            $arrayPalmares = array();
            $arraySaisons = array();
            $tempSaison = 0;
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Equipe));
            while ($row = $result->fetch()){ 
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
            $eSeason2 = $Saison;
            $eLink = $Equipe.'-'.$eSeason;
            $eTeam = 'img/KIP/teams/'.$eLink.'-team.jpg';
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
            }elseif(is_file('img/Nations/'.substr($Code_club, 0, 3).'.png')){
                $this->m_tpl->assign('eLogo', 'img/Nations/'.substr($Code_club, 0, 3).'.png');
            }
            $this->m_tpl->assign('eSeason', $eSeason);

            if(is_file('img/KIP/teams/'.$Equipe.'-'.date('Y').'-team.jpg')){
                $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.date('Y').'-team.jpg');
                $eSeason2 = date('Y');
            }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-1).'-team.jpg')){
                $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-1).'-team.jpg');
                $eSeason2 = date('Y')-1;
            }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-2).'-team.jpg')){
                $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-2).'-team.jpg');
                $eSeason2 = date('Y')-2;
            }elseif(is_file('img/KIP/teams/'.$Equipe.'-'.(date('Y')-3).'-team.jpg')){
                $this->m_tpl->assign('eTeam', 'img/KIP/teams/'.$Equipe.'-'.(date('Y')-3).'-team.jpg');
                $eSeason2 = date('Y')-3;
            }
            $this->m_tpl->assign('eSeason2', $eSeason2);

            // Compo + stats individuelles
            if ($codeCompet != '' && $codeSaison > 2000) {
                // Infos sur la compétition
                $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
                $this->m_tpl->assign('recordCompetition', $recordCompetition);

                // stats individuelles
                $sql = "SELECT cej.Matric, cej.Nom, cej.Prenom, cej.Sexe, cej.Categ, cej.Numero, cej.Capitaine,
                        SUM(IF(md.Id_evt_match = 'B', 1, 0)) buts,
                        SUM(IF(md.Id_evt_match = 'V', 1, 0)) verts,
                        SUM(IF(md.Id_evt_match = 'J', 1, 0)) jaunes,
                        SUM(IF(md.Id_evt_match = 'R', 1, 0)) rouges
                    FROM gickp_Competitions_Equipes ce
                    LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs cej ON ce.Id = cej.Id_equipe
                    LEFT OUTER JOIN gickp_Journees j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
                    LEFT OUTER JOIN gickp_Matchs m ON j.Id = m.Id_journee
                    LEFT OUTER JOIN gickp_Matchs_Detail md ON m.Id = md.Id_match
                    WHERE md.Competiteur = cej.Matric
                    AND ce.Numero = ? 
                    AND ce.Code_compet = ? 
                    AND ce.Code_saison = ? 
                    AND cej.Capitaine != 'A'
                    AND cej.Capitaine != 'X'
                    AND m.Validation = 'O'
                    AND m.Publication = 'O'
                    GROUP BY cej.Matric
                    ORDER BY Field(if(cej.Capitaine='C','-',if(cej.Capitaine='','-',cej.Capitaine)), '-', 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array($Equipe, $codeCompet, $codeSaison));
                while ($row = $result->fetch()) {
                    $arrayCompo[] = $row;
                }
                $this->m_tpl->assign('arrayCompo', $arrayCompo);
            }
        }
	}
	

	function __construct()
	{			
        MyPage::MyPage();
		
		$this->SetTemplate("Equipes", "Equipes", true);
		$this->Load();
		// COSANDCO : Gestion Param Voie ...
		if (utyGetGet('voie', false)) {
			$voie = (int) utyGetGet('voie', 0);
			if ($voie > 0) {
                $this->m_tpl->assign('voie', $voie);
            }
            
			$intervalle = (int) utyGetGet('intervalle', 0);
			if ($intervalle > 0) {
                $this->m_tpl->assign('intervalle', $intervalle);
			}
		}        

		$this->DisplayTemplateFrame('kpequipes');
	}
}		  	

$page = new Equipes();

