<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des paramètres d'une Journee

class GestionParamJournee extends MyPageSecure	 
{	
	function Load() {
		$idJournee = utyGetSession('idJournee', 0);
		$this->m_tpl->assign('idJournee', $idJournee);
		$myBdd = new MyBdd();

		if ($idJournee != 0) {		
			// Liste ...
			$sql  = "Select j.Id, j.Code_competition, j.Code_saison, j.Phase, j.Niveau, j.Etape, j.Nbequipes, j.Date_debut, j.Date_fin, j.Nom, j.Libelle, j.Lieu, j.Type, ";
			$sql .= "j.Plan_eau, j.Departement, j.Responsable_insc, j.Responsable_R1, j.Organisateur, j.Delegue, j.ChefArbitre, ";
			$sql .= "c.Code_typeclt ";
			$sql .= "From gickp_Journees j, gickp_Competitions c ";
			$sql .= "Where j.Id = $idJournee ";
			$sql .= "And j.Code_competition = c.Code ";
			$sql .= "And j.Code_saison = c.Code_saison ";

			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
			if (mysql_num_rows($result) == 1) {
				$ListJournees = array();
				$row = mysql_fetch_array($result);	  
				
				$this->m_tpl->assign('Num_Journee', $row['Id']);
				$this->m_tpl->assign('J_saison', $row['Code_saison']);
				$this->m_tpl->assign('J_competition', $row['Code_competition']);
				$this->m_tpl->assign('Type', $row['Type']);
				$this->m_tpl->assign('Phase', $row['Phase']);
				$this->m_tpl->assign('Niveau', $row['Niveau']);
				$this->m_tpl->assign('Etape', $row['Etape']);
				$this->m_tpl->assign('Nbequipes', $row['Nbequipes']);
				$this->m_tpl->assign('Date_debut', utyDateUsToFr($row['Date_debut']));
				$this->m_tpl->assign('Date_fin', utyDateUsToFr($row['Date_fin']));
				$this->m_tpl->assign('Nom', $row['Nom']);
				$this->m_tpl->assign('Libelle', $row['Libelle']);
				$this->m_tpl->assign('Lieu', $row['Lieu']);
				$this->m_tpl->assign('Plan_eau', $row['Plan_eau']);
				$this->m_tpl->assign('Departement', $row['Departement']);
				$this->m_tpl->assign('Responsable_insc', $row['Responsable_insc']);
				$this->m_tpl->assign('Responsable_R1', $row['Responsable_R1']);
				$this->m_tpl->assign('Organisateur', $row['Organisateur']);
				$this->m_tpl->assign('Delegue', $row['Delegue']);
				$this->m_tpl->assign('ChefArbitre', $row['ChefArbitre']);
				$this->m_tpl->assign('Code_typeclt', $row['Code_typeclt']);
				
				if ($row['Code_typeclt'] = 'CP') {
					$sql2  = "Select Id, Code_competition, Code_saison, Phase, Niveau, Etape, Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre ";
					$sql2 .= "From gickp_Journees ";
					$sql2 .= "Where Code_competition = '".$row['Code_competition']."' ";
					$sql2 .= "And Code_saison = '".$row['Code_saison']."' ";
					$sql2 .= "Order by Niveau, Phase ";

					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select 2 =>  ".$sql2);
					$num_results2 = mysql_num_rows($result2);
					
					for ($i=0;$i<$num_results2;$i++) {
						$row2 = mysql_fetch_array($result2);	  
						array_push($ListJournees, $row2);
					}
					$this->m_tpl->assign('ListJournees', $ListJournees);
				}
			}
		} else {
			$this->m_tpl->assign('Num_Journee', 0);
			$this->m_tpl->assign('J_saison', utyGetSaison());
			$this->m_tpl->assign('J_competition', utyGetSession('codeCompet'));
		}
		
		//Liste des codes compétition
		$arrayCompetition = array();
		$sql  = "Select c.*, g.section, g.ordre From gickp_Competitions c, gickp_Competitions_Groupes g ";
		$sql .= "WHERE c.Code_ref = g.Groupe ";
		$sql .= "Group By c.Code ";
		$sql .= "Order By g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 

		$result = $myBdd->Query($sql);
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $myBdd->FetchArray($result)) {
            if($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if($row["Code"] == utyGetSession('codeCompet')) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);

		// Liste des saisons
		$arraySaisons = array();
		$sql  = "Select distinct Code From gickp_Saison order by Code ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 1");
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++) {
			$row = mysql_fetch_array($result);
			array_push($arraySaisons, array( 'Code' => $row['Code']));
		}
		$this->m_tpl->assign('arraySaisons', $arraySaisons);
	}
	
	function Ok() {
        $myBdd = new MyBdd();
		$idJournee = (int)utyGetPost('idJournee', -1);
		$duppliThis = $myBdd->RealEscapeString(trim(utyGetPost('duppliThis')));
		
		$J_saison = $myBdd->RealEscapeString(trim(utyGetPost('J_saison')));
		$J_competition = $myBdd->RealEscapeString(trim(utyGetPost('J_competition')));
		$Phase = $myBdd->RealEscapeString(trim(utyGetPost('Phase')));
		$Niveau = $myBdd->RealEscapeString(trim(utyGetPost('Niveau')));
		$Etape = $myBdd->RealEscapeString(trim(utyGetPost('Etape')));
		$Nbequipes = $myBdd->RealEscapeString(trim(utyGetPost('Nbequipes')));
		$Type = $myBdd->RealEscapeString(trim(utyGetPost('Type')));
		$Date_debut = utyDateFrToUs($myBdd->RealEscapeString(trim(utyGetPost('Date_debut'))));
		$Date_origine = utyDateFrToUs($myBdd->RealEscapeString(trim(utyGetPost('Date_origine'))));
		$Date_fin = utyDateFrToUs($myBdd->RealEscapeString(trim(utyGetPost('Date_fin'))));
		$Nom = $myBdd->RealEscapeString(trim(utyGetPost('Nom')));
		$Libelle = $myBdd->RealEscapeString(trim(utyGetPost('Libelle')));
		$Lieu = $myBdd->RealEscapeString(trim(utyGetPost('Lieu')));
		$Plan_eau = $myBdd->RealEscapeString(trim(utyGetPost('Plan_eau')));
		$Departement = $myBdd->RealEscapeString(trim(utyGetPost('Departement')));
		$Responsable_insc = $myBdd->RealEscapeString(trim(utyGetPost('Responsable_insc')));
		$Responsable_R1 = $myBdd->RealEscapeString(trim(utyGetPost('Responsable_R1')));
		$Organisateur = $myBdd->RealEscapeString(trim(utyGetPost('Organisateur')));
		$Delegue = $myBdd->RealEscapeString(trim(utyGetPost('Delegue')));
		$ChefArbitre = $myBdd->RealEscapeString(trim(utyGetPost('ChefArbitre')));

		$AvecMatchs = $myBdd->RealEscapeString(trim(utyGetPost('AvecMatchs')));
		$CodMatchs = $myBdd->RealEscapeString(trim(utyGetPost('CodMatchs')));
		
		$d1 = strtotime($Date_debut.' 00:00:00'); 
		$d2 = strtotime($Date_origine.' 00:00:00'); 
		$diffdate = round(($d1-$d2)/60/60/24);
		
		
		if ($idJournee != 0 && $duppliThis != 'Duppli') {
			if (!utyIsAutorisationJournee($idJournee)) {
                die("Vous n'avez pas l'autorisation de modifier cette journée ! (<a href='javascript:history.back()'>Retour</a>)");
            }

            // Modification ...
    		$sql  = 'UPDATE gickp_Journees '
                    . 'SET Code_competition = "'.$J_competition.'", Code_saison = "'.$J_saison.'", '
                    . 'Type = "'.$Type.'", Phase = "'.$Phase.'", Niveau = "'.$Niveau.'", Etape = "'.$Etape.'", '
                    . 'Nbequipes = "'.$Nbequipes.'", Date_debut = "'.$Date_debut.'", Date_fin = "'.$Date_fin.'", '
                    . 'Nom = "'.$Nom.'", Libelle = "'.$Libelle.'", Lieu = "'.$Lieu.'", Plan_eau = "'.$Plan_eau.'", '
                    . 'Departement = "'.$Departement.'", Responsable_insc = "'.$Responsable_insc.'", '
                    . 'Responsable_R1 = "'.$Responsable_R1.'", Organisateur = "'.$Organisateur.'", '
                    . 'Delegue = "'.$Delegue.'", ChefArbitre = "'.$ChefArbitre.'" '
                    . 'WHERE Id = '.$idJournee.' ';
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update : ".$sql);
		
            $myBdd->utyJournal('Modification journee', $J_saison, $J_competition, '', $idJournee);
		} else {
			// Création ...
			$nextIdJournee = $this->GetNextIdJournee();
			
    		$sql  = "INSERT INTO gickp_Journees (Id, Code_competition, code_saison, Phase, Type, Niveau, Etape, Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, ";
			$sql .= "Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre) ";
			$sql .= "VALUES ($nextIdJournee, '$J_competition', '$J_saison', '$Phase', '$Type', '$Niveau', '$Etape', '$Nbequipes', '$Date_debut', '$Date_fin', '$Nom', '$Libelle', '$Lieu', '$Plan_eau', ";
			$sql .= "'$Departement', '$Responsable_insc', '$Responsable_R1', '$Organisateur', '$Delegue', '$ChefArbitre') ";

			mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert :<br>".$sql);
			
			//Copie des matchs
			if($AvecMatchs == 'oui') {
				$sql2a  = "CREATE TEMPORARY TABLE gickp_Tmp (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
				mysql_query($sql2a, $myBdd->m_link) or die ("Erreur Insert 2a ".$sql2a);
				$sql2  = "INSERT INTO gickp_Tmp (Num) SELECT DISTINCT ce.Id FROM gickp_Competitions_Equipes ce, gickp_Journees j ";
				$sql2 .= "WHERE ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison AND j.Id = $idJournee ORDER BY ce.Tirage; ";
				mysql_query($sql2, $myBdd->m_link) or die ("Erreur Insert 2 ".$sql2);

				$sql3a  = "CREATE TEMPORARY TABLE gickp_Tmp2 (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
				mysql_query($sql3a, $myBdd->m_link) or die ("Erreur Insert 3a ".$sql3a);
				$sql3  = "INSERT INTO gickp_Tmp2 (Num) SELECT DISTINCT ce.Id FROM gickp_Competitions_Equipes ce, gickp_Journees j ";
				$sql3 .= "WHERE ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison AND j.Id = $idJournee ORDER BY ce.Tirage; ";
				mysql_query($sql3, $myBdd->m_link) or die ("Erreur Insert 3 ".$sql3);

				$sql4  = "Insert Into gickp_Matchs (Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, Validation) ";
				$sql4 .= "Select $nextIdJournee, ";
				if ($Niveau <= 1 && $CodMatchs == 'oui') {
					$sql4 .= "CONCAT(m.Libelle, ' [T', ta.Id, '/T', tb.Id, ']'), ";
					$sql4 .= "DATE_ADD(m.Date_match,INTERVAL +'$diffdate' DAY), m.Heure_match, m.Terrain, m.Numero_ordre, m.Validation ";
					$sql4 .= "FROM gickp_Matchs m ";
					$sql4 .= ", gickp_Tmp ta, gickp_Tmp2 tb ";
					$sql4 .= "WHERE m.Id_journee = $idJournee ";
					$sql4 .= "AND ta.Num=m.Id_equipeA ";
					$sql4 .= "AND tb.Num=m.Id_equipeB ";
				} else {
					$sql4 .= "m.Libelle, ";
					$sql4 .= "DATE_ADD(m.Date_match,INTERVAL +'$diffdate' DAY), m.Heure_match, m.Terrain, m.Numero_ordre, m.Validation ";
					$sql4 .= "FROM gickp_Matchs m ";
					$sql4 .= "WHERE m.Id_journee = $idJournee ";
				}
				
				mysql_query($sql4, $myBdd->m_link) or die ("Erreur Insert 4 : ".$sql.'<br>'.$sql2a.'<br>'.$sql2.'<br>'.$sql3a.'<br>'.$sql3.'<br>'.$sql4);
			}
		
			$myBdd->utyJournal('Ajout journee', $codeSaison, $J_competition, '', $nextIdJournee);
		}			
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function DuppliListJournees() {
		$myBdd = new MyBdd();
        $Date_debut = utyDateFrToUs($myBdd->RealEscapeString(trim(utyGetPost('Date_debut'))));
		$Date_fin = utyDateFrToUs($myBdd->RealEscapeString(trim(utyGetPost('Date_fin'))));
		$Nom = $myBdd->RealEscapeString(trim(utyGetPost('Nom')));
		$Libelle = $myBdd->RealEscapeString(trim(utyGetPost('Libelle')));
		$Lieu = $myBdd->RealEscapeString(trim(utyGetPost('Lieu')));
		$Plan_eau = $myBdd->RealEscapeString(trim(utyGetPost('Plan_eau')));
		$Departement = $myBdd->RealEscapeString(trim(utyGetPost('Departement')));
		$Responsable_insc = $myBdd->RealEscapeString(trim(utyGetPost('Responsable_insc')));
		$Responsable_R1 = $myBdd->RealEscapeString(trim(utyGetPost('Responsable_R1')));
		$Organisateur = $myBdd->RealEscapeString(trim(utyGetPost('Organisateur')));
		$Delegue = $myBdd->RealEscapeString(trim(utyGetPost('Delegue')));
		$ChefArbitre = $myBdd->RealEscapeString(trim(utyGetPost('ChefArbitre')));
		$listJournees = explode (",", $myBdd->RealEscapeString(trim(utyGetPost('ParamCmd'))));
        
		foreach($listJournees as $Journee) {
			// Modification ...
			$sql  = "UPDATE gickp_Journees SET Date_debut = '$Date_debut', Date_fin = '$Date_fin', "
                    . "Nom = '$Nom', Libelle = '$Libelle', Lieu = '$Lieu', Plan_eau = '$Plan_eau', "
                    . "Departement = '$Departement', Responsable_insc = '$Responsable_insc', "
                    . "Responsable_R1 = '$Responsable_R1', Organisateur = '$Organisateur', Delegue = '$Delegue', ChefArbitre = '$ChefArbitre' "
                    . "WHERE Id = $Journee ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update : ".$sql);
		
			$myBdd->utyJournal('Modification journee', '', '', '', $Journee);
		}
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function AjustDates() {
		$idJournee = $myBdd->RealEscapeString(trim(utyGetPost('idJournee', -1)));
		if ($idJournee != 0) {
			$myBdd = new MyBdd();
	
			$sql  = "SELECT Date_debut, Date_fin "
                    . "FROM gickp_Journees "
                    . "WHERE Id = $idJournee ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load : ".$sql);
			$row = mysql_fetch_row($result);
			$Date_debut = $row[0];
			$Date_fin = $row[1];
			
			$sql  = "UPDATE gickp_Matchs "
                    . "SET Date_match = '".$Date_debut."' "
                    . "WHERE Id_journee = $idJournee ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert : ".$sql);
		}			
	}

	function GetNextIdJournee() {
		$myBdd = new MyBdd();

		$sql  = "SELECT max(Id) maxId "
                . "FROM gickp_Journees "
                . "WHERE Id < 19000001 ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
	
		if (mysql_num_rows($result) == 1) {
			$row = mysql_fetch_array($result);	  
			return ((int) $row['maxId'])+1;
		} else {
			return 1;
		}
	}		
	
	function Cancel() {
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function Duplicate() {
		$myBdd = new MyBdd();
        $idJournee = $myBdd->RealEscapeString(trim(utyGetPost('idJournee', -1)));
		if ($idJournee != 0) {
			$nextIdJournee = $this->GetNextIdJournee();
	
			$sql  = "INSERT INTO gickp_Journees (Id, Code_competition, code_saison, Phase, Type, Niveau, Etape, Nbequipes, "
                    . "Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, "
                    . "Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre) "
                    . "SELECT $nextIdJournee, Code_competition, code_saison, Phase, Type, Niveau, Etape, Nbequipes, Date_debut, Date_fin, "
                    . "Nom, Libelle, Lieu, Plan_eau, "
                    . "Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre "
                    . "FROM gickp_Journees Where Id = $idJournee ";

			mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert");
		}			
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}

		$myBdd->utyJournal('Dupplication journee', '', '', '', $nextIdJournee); // A compléter (saison, compétition, options)
	}
	
	function GestionParamJournee() {			
		MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';
	  
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Ok') {
                ($_SESSION['Profile'] <= 4) ? $this->Ok() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'DuppliListJournees') {
                ($_SESSION['Profile'] <= 4) ? $this->DuppliListJournees() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'AjustDates') {
                ($_SESSION['Profile'] <= 2) ? $this->AjustDates() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Cancel') {
                ($_SESSION['Profile'] <= 10) ? $this->Cancel() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Duplicate') {
                ($_SESSION['Profile'] <= 4) ? $this->Duplicate() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($alertMessage == '') {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
	
		$this->SetTemplate("Parametres_journee", "Journees_phases", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionParamJournee');
	}
}		  	

$page = new GestionParamJournee();

