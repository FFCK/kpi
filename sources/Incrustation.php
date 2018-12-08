<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Incrustation

class Incrustation extends MyPage 
{	
	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd)
	{
		$myBdd = new MyBdd();
		$sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
		$result = $myBdd->Query($sql);

		if ($myBdd->NumRows($result) != 1) {
            return;
        }

        $row = $myBdd->FetchArray($result);
		if ((int) $row['Nb'] > 0) {
            return;
        }

        $sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		$sql .= "AND Capitaine <> 'A' ";
		$result = $myBdd->Query($sql);
 	}
	
	function Load()
	{
		$nblignes = 26;
		$idMatch = utyGetGet('idMatch', -1);
		$this->m_tpl->assign('idMatch', $idMatch);
		$numOrdre = utyGetGet('numOrdre', -1);
		$this->m_tpl->assign('numOrdre', $numOrdre);
		$lastPeriode = utyGetSession('lastPeriode');
		$thePeriode = '';		
		
		$myBdd = new MyBdd();

		//Chargement Match
		$sql  = "Select a.Validation, a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle Intitule, a.Terrain, a.Commentaires_officiels, ";
		$sql .= "a.Id_equipeA, a.Id_equipeB, a.Arbitre_principal, a.Arbitre_secondaire, a.ScoreA, a.ScoreB, a.ColorA, a.ColorB, a.Secretaire, a.Chronometre, ";
		$sql .= "b.Nom, b.Phase, b.Libelle, b.Lieu, b.Organisateur, b.Responsable_R1, b.Responsable_insc, b.Delegue, b.ChefArbitre, b.Code_competition, b.Code_saison ";
		$sql .= "From gickp_Matchs a, gickp_Journees b ";
		$sql .= "Where a.Id = $idMatch ";
		$sql .= "And a.Id_journee = b.Id ";
		
		$result = $myBdd->Query($sql);
		if ($myBdd->NumRows($result) == 1) {
			$row = $myBdd->FetchArray($result);
			
			$SaisonMatch = $row['Code_saison'];
			$this->m_tpl->assign('saison', $row['Code_saison']);
			$this->m_tpl->assign('competition', $row['Nom']);
			$this->m_tpl->assign('lieu', $row['Lieu']);
			$this->m_tpl->assign('terrain', $row['Terrain']);

			$this->m_tpl->assign('phase', $row['Phase']);
	
			$this->m_tpl->assign('categorie', $row['Code_competition']);
			$this->m_tpl->assign('date', utyDateUsToFr($row['Date_match']));
			$this->m_tpl->assign('heure', $row['Heure_match']);
			$this->m_tpl->assign('no', $row['Numero_ordre']);
			
			$score_A = $row['ScoreA'];
			$score_B = $row['ScoreB'];
			$this->m_tpl->assign('ScoreA', $score_A);
			$this->m_tpl->assign('ScoreB', $score_B);

			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];
			$this->m_tpl->assign('idEquipeA', $idEquipeA);
			$this->m_tpl->assign('idEquipeB', $idEquipeB);
			
			// drapeaux
			$paysA = substr($myBdd->GetCodeClubEquipe($idEquipeA), 0, 3);
			if(is_numeric($paysA[0]) || is_numeric($paysA[1]) || is_numeric($paysA[2]))
				$paysA = 'FRA';
			$paysB = substr($myBdd->GetCodeClubEquipe($idEquipeB), 0, 3);
			if(is_numeric($paysB[0]) || is_numeric($paysB[1]) || is_numeric($paysB[2]))
				$paysB = 'FRA';
			$this->m_tpl->assign('paysA', $paysA);
			$this->m_tpl->assign('paysB', $paysB);
			
			// Nom Equipe A
			$sql  = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeA";
            $result = $myBdd->Query($sql);
            if ($myBdd->NumRows($result) == 1) {
				$row = $myBdd->FetchArray($result);
				$this->m_tpl->assign('equipea', $row['Libelle']);
			}
			
			// Nom Equipe B
			$sql  = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeB";
            $result = $myBdd->Query($sql);
            if ($myBdd->NumRows($result) == 1) {
				$row = $myBdd->FetchArray($result);
				$this->m_tpl->assign('equipeb', $row['Libelle']);
			}
			
			//Détails match
			$detail = array();
			$scoreDetailA = 0;
			$scoreDetailB = 0;
			$scoreM1A = 0;
			$scoreM1B = 0;
			
			$sql  = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.Competiteur, d.Numero, d.Equipe_A_B, ";
			$sql .= "c.Nom, c.Prenom ";
			$sql .= "From gickp_Matchs_Detail d Left Outer Join gickp_Liste_Coureur c On d.Competiteur = c.Matric ";
			$sql .= "Where d.Id_match = $idMatch ";
			$sql .= "Order By d.Periode, d.Temps, d.Id ";	 
            $result = $myBdd->Query($sql);
			$num_results = $myBdd->NumRows($result);
			if ($num_results == 0)
			{
				$lastPeriode = '';
				$_SESSION['lastPeriode'] = '';
			}
			if ($num_results > 26) {
                $nblignes = $num_results;
            } else {
                $nblignes = 26;
            }

            while($row = $myBdd->FetchAssoc($result)) {
				for($j=0;$j<=11;$j++) { $d[$j] = '&nbsp;'; }
				if($row['Id']) {
					if($row['Equipe_A_B'] == 'A' && $row['Id_evt_match'] == 'B')
						$scoreDetailA++;
					if($row['Equipe_A_B'] == 'B' && $row['Id_evt_match'] == 'B')
						$scoreDetailB++;
					$d[6] = $row['Periode'];
					if (strftime("%M:%S", strtotime($row['Temps'])) != '00:00') {
                        $d[6] .= ' - ' . strftime("%M:%S", strtotime($row['Temps']));
                        $CChrono = strftime("%M:%S", strtotime($row['Temps']));
                    } else {
                        $CChrono = '';
                    }
                    $d[0] = $row['Id'];
					$lastPeriode = $row['Periode'];
				}
				array_push($detail, array('d0' => $d[0], 'd1' => $d[1], 'd2' => $d[2], 'd3' => $d[3], 'd4' => $d[4], 'd5' => $d[5], 'd6' => $d[6],
											'd7' => $d[7], 'd8' => $d[8], 'd9' => $d[9], 'd10' => $d[10], 'd11' => $d[11]));
			}
			$this->m_tpl->assign('nblignes', $nblignes);
			$this->m_tpl->assign('detail', $detail);
			$this->m_tpl->assign('scoreDetailA', $scoreDetailA);
			$this->m_tpl->assign('scoreDetailB', $scoreDetailB);
			$this->m_tpl->assign('scoreM1A', $scoreM1A);
			$this->m_tpl->assign('scoreM1B', $scoreM1B);
			if ($scoreDetailA == $score_A && $scoreDetailB == $score_B) {
                $scoreEq = 'O';
            } else {
                $scoreEq = 'N';
            }
            $this->m_tpl->assign('scoreEq', $scoreEq);
			if ($thePeriode != '') {
                $lastPeriode = $thePeriode;
            }
            $this->m_tpl->assign('lastPeriode', $lastPeriode);
		}
	}

	function Incrustation()
	{			
		MyPage::MyPage();
		
		$idMatch = utyGetGet('idMatch');
		$numOrdre = utyGetGet('numOrdre');

		$this->SetTemplate("Match ".utyGetGet('idMatch', -1)." - n°".$numOrdre, "Matchs", false);
		$this->Load();
		$this->DisplayTemplateNu('Incrustation');
	}
}		  	

$page = new Incrustation();

