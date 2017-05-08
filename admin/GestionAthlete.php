<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion Athlete

class GestionAthlete extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$Athlete = utyGetSession('Athlete', '');
		$Athlete = utyGetPost('Athlete', $Athlete);
		$Athlete = utyGetGet('Athlete', $Athlete);
		$this->m_tpl->assign('Athlete', $Athlete);
                
        $SaisonAthlete = utyGetSession('SaisonAthlete', utyGetSaison());
        $SaisonAthlete = utyGetPost('SaisonAthlete', $SaisonAthlete);
		$this->m_tpl->assign('SaisonAthlete', $SaisonAthlete);

        // Saisons	
		$sql  = "Select Code "
                    . "From gickp_Saison "
                    . "Order By Code DESC ";	 
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);
		$arraySaison = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
			array_push($arraySaison, $row);
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);

                // Chargement des Informations relatives à l'athlète
		if ($Athlete != '')
		{
			// Données générales
			$sql  = "SELECT c.*, cl.Libelle nomclub, dep.Libelle nomcd, reg.Libelle nomcr, s.Date date_surclassement "
                        . "FROM gickp_Liste_Coureur c "
                        . "LEFT OUTER JOIN gickp_Surclassements s ON (c.Matric = s.Matric AND s.Saison = $SaisonAthlete), "
                        . "gickp_Club cl, gickp_Comite_dep dep, gickp_Comite_reg reg "
                        . "WHERE c.Numero_club = cl.Code "
                        . "AND c.Numero_comite_dept = dep.Code "
                        . "AND c.Numero_comite_reg = reg.Code "
                        . "AND c.Matric = '$Athlete' ";
			$result = $myBdd->Query($sql);
			if ($myBdd->NumRows($result) != 1) {
                return;
            }
            $row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
            $row['date_surclassement'] = utyDateUsToFr($row['date_surclassement']);
			$this->m_tpl->assign('Courreur', $row);
			$this->m_tpl->assign('Athlete_id', $row['Nom'].' '.$row['Prenom']);
			// Arbitre
			$sql  = "SELECT * FROM gickp_Arbitre "
                        . "WHERE Matric = '$Athlete' ";
			$result = $myBdd->Query($sql);
			if ($myBdd->NumRows($result) == 1)
			{
				$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
				switch ($row['Arb'])
				{
					case 'Int':
						$row['Arb']='Arbitre INTERNATIONAL';
						break;
					case 'Nat':
						$row['Arb']='Arbitre NATIONAL';
						break;
					case 'Reg':
						$row['Arb']='Arbitre REGIONAL';
						break;
					default :
						$row['Arb']='Néant';
						break;
				}
				$this->m_tpl->assign('Arbitre', $row);
			}
			// Titulaire
			$Titulaire = array();
			$sql  = "SELECT cej.*, ce.*, cej.Numero Num  "
                                . "FROM gickp_Competitions_Equipes_Joueurs cej, gickp_Competitions_Equipes ce "
                                . "WHERE cej.Matric = '$Athlete' "
                                . "AND cej.Id_equipe = ce.Id "
                                . "AND ce.Code_compet != 'POOL' "
                                . "AND ce.Code_saison = $SaisonAthlete "
                                . "ORDER BY ce.Code_compet ";
			$result = $myBdd->Query($sql);
			$num_results = $myBdd->NumRows($result);
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
				array_push($Titulaire, $row);
			}
			$this->m_tpl->assign('Titulaire', $Titulaire);

			// Arbitrages
			$Arbitrages = array();
			$sql  = "SELECT m.*, j.*, m.id Identifiant, if(m.Matric_arbitre_principal = '$Athlete','Prin','') Prin, if(m.Matric_arbitre_secondaire = '$Athlete','Sec','') Sec "
                                . "FROM gickp_Matchs m, gickp_Journees j "
                                . "WHERE (m.Matric_arbitre_principal = '$Athlete' "
                                . "OR m.Matric_arbitre_secondaire = '$Athlete') "
                                . "AND m.Id_journee = j.Id "
                                . "AND j.Code_saison = $SaisonAthlete "
                                . "ORDER BY m.Date_match DESC, m.Heure_match DESC ";
			$result = $myBdd->Query($sql);
			$num_results = $myBdd->NumRows($result);
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);
				if($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '')
					$row['ScoreOK'] = 'O';
				else
					$row['ScoreOK'] = 'N';
				array_push($Arbitrages, $row);
			}
			$this->m_tpl->assign('Arbitrages', $Arbitrages);

			// Joueur
			$Joueur = array();
			$sql  = "SELECT mj.*, m.*, m.id Identifiant, j.*, mj.Numero Num, ceA.Libelle eqA, ceB.Libelle eqB, "
                                . "SUM(IF(b.Id_evt_match='B',1,0)) But, "
                                . "SUM(IF(b.Id_evt_match='V',1,0)) Vert, "
                                . "SUM(IF(b.Id_evt_match='J',1,0)) Jaune, "
                                . "SUM(IF(b.Id_evt_match='R',1,0)) Rouge, "
                                . "SUM(IF(b.Id_evt_match='T',1,0)) Tir, "
                                . "SUM(IF(b.Id_evt_match='A',1,0)) Arret "
                                . "FROM gickp_Matchs_Joueurs mj left outer join gickp_Matchs_Detail b on (mj.Matric = b.Competiteur AND mj.Id_match = b.Id_match), "
                                . "gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ceA, gickp_Competitions_Equipes ceB "
                                . "WHERE mj.Matric = '$Athlete' "
                                . "AND mj.Id_match = m.Id "
                                . "AND m.Id_journee = j.Id "
                                . "AND m.Id_equipeA = ceA.Id "
                                . "AND m.Id_equipeB = ceB.Id "
                                . "AND j.Code_saison = $SaisonAthlete "
                                . "GROUP BY m.Id "
                                . "ORDER BY m.Date_match DESC, m.Heure_match DESC ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select : <br>".$sql);
			$num_results = mysql_num_rows($result);
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				if($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '')
					$row['ScoreOK'] = 'O';
				else
					$row['ScoreOK'] = 'N';
				array_push($Joueur, $row);
			}
			$this->m_tpl->assign('Joueur', $Joueur);
		}
	}
	
	function Add()
	{


	}
	

	
	function GestionAthlete()
	{			
	  MyPageSecure::MyPageSecure(7);
		
		$alertMessage = '';
	  
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 6) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
	
		$this->SetTemplate("Statistiques athlètes", "Athlètes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		
		$this->DisplayTemplate('GestionAthlete');
	}
}		  	

$page = new GestionAthlete();

?>
