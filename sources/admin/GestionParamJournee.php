<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des paramètres d'une Journee

class GestionParamJournee extends MyPageSecure	 
{	
	var $myBdd;

	function Load() 
	{
		$myBdd = $this->myBdd;
		$idJournee = utyGetSession('idJournee', 0);
		$this->m_tpl->assign('idJournee', $idJournee);
		$codecompet = utyGetSession('codeCompet', '');
		$codeSaison = $myBdd->GetActiveSaison();

		if ($idJournee != 0) {		
			// Liste ...
			$sql = "SELECT j.Id, j.Code_competition, j.Code_saison, j.Phase, j.Niveau, 
				j.Etape, j.Nbequipes, j.Date_debut, j.Date_fin, j.Nom, j.Libelle, j.Lieu, 
				j.Type, j.Plan_eau, j.Departement, c.Code_typeclt, j.Responsable_insc, 
				j.Responsable_R1, j.Organisateur, j.Delegue, j.ChefArbitre, 
				j.Rep_athletes, j.Arb_nj1, j.Arb_nj2, j.Arb_nj3, j.Arb_nj4, j.Arb_nj5 
				FROM kp_journee j, kp_competition c 
				WHERE j.Id = ? 
				AND j.Code_competition = c.Code 
				AND j.Code_saison = c.Code_saison ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idJournee));
			if ($result->rowCount() == 1) {
				$ListJournees = array();
				$row = $result->fetch();
				if ($_SESSION['lang'] == 'fr') {
					$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
					$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
				}
				$codecompet = $row['Code_competition'];
				$codeSaison = $row['Code_saison'];
				
				$this->m_tpl->assign('Num_Journee', $row['Id']);
				$this->m_tpl->assign('J_saison', $row['Code_saison']);
				$this->m_tpl->assign('J_competition', $row['Code_competition']);
				$this->m_tpl->assign('Type', $row['Type']);
				$this->m_tpl->assign('Phase', $row['Phase']);
				$this->m_tpl->assign('Niveau', $row['Niveau']);
				$this->m_tpl->assign('Etape', $row['Etape']);
				$this->m_tpl->assign('Nbequipes', $row['Nbequipes']);
				$this->m_tpl->assign('Date_debut', $row['Date_debut']);
				$this->m_tpl->assign('Date_fin', $row['Date_fin']);
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
				$this->m_tpl->assign('Rep_athletes', $row['Rep_athletes']);
				$this->m_tpl->assign('Arb_nj1', $row['Arb_nj1']);
				$this->m_tpl->assign('Arb_nj2', $row['Arb_nj2']);
				$this->m_tpl->assign('Arb_nj3', $row['Arb_nj3']);
				$this->m_tpl->assign('Arb_nj4', $row['Arb_nj4']);
				$this->m_tpl->assign('Arb_nj5', $row['Arb_nj5']);
				$this->m_tpl->assign('Code_typeclt', $row['Code_typeclt']);
				
				if ($row['Code_typeclt'] = 'CP') {
					$sql2 = "SELECT Id, Code_competition, Code_saison, Phase, Niveau, Etape, 
						Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, 
						Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre, 
						Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5 
						FROM kp_journee 
						WHERE Code_competition = ? 
						AND Code_saison = ? 
						ORDER BY Niveau, Phase ";
					$result2 = $myBdd->pdo->prepare($sql2);
					$result2->execute(array($row['Code_competition'], $row['Code_saison']));
					while($row2 = $result2->fetch()) {
						array_push($ListJournees, $row2);
					}
					$this->m_tpl->assign('ListJournees', $ListJournees);
				}
			}
		} else {
			$this->m_tpl->assign('Num_Journee', 0);
			$this->m_tpl->assign('J_saison', $codeSaison);
			$this->m_tpl->assign('J_competition', $codecompet);
		}
		
		//Liste des codes compétition
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();

		//Liste des codes compétition
		$arrayCompetition = array();
		$sql = "SELECT c.*, g.section, g.ordre 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_ref = g.Groupe 
			GROUP BY c.Code 
			ORDER BY g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
            if ($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if ($row["Code"] == $codecompet) {
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
		$sql  = "SELECT DISTINCT Code 
			FROM kp_saison 
            WHERE Code > '1900' 
			ORDER BY Code ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			array_push($arraySaisons, array( 'Code' => $row['Code']));
		}
		$this->m_tpl->assign('arraySaisons', $arraySaisons);

		// RC disponibles
		$arrayRC = array();
		$sql = "SELECT rc.Matric, rc.Ordre, lc.Nom, lc.Prenom 
			FROM kp_rc rc 
			LEFT OUTER JOIN kp_licence lc 
				ON (rc.Matric = lc.Matric) 
			WHERE rc.Code_Competition = ?
			AND rc.Code_saison = ? 
			ORDER BY rc.Ordre DESC";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codecompet, $codeSaison));
		$arrayRC = $result->fetchAll(PDO::FETCH_ASSOC);
		$this->m_tpl->assign('arrayRC', $arrayRC);
		
	}
	
	function Ok() 
	{
        $myBdd = $this->myBdd;
		$idJournee = (int)utyGetPost('idJournee', -1);
		$duppliThis = trim(utyGetPost('duppliThis'));
		
		$J_saison = trim(utyGetPost('J_saison'));
		$J_competition = trim(utyGetPost('J_competition'));
		$Phase = trim(utyGetPost('Phase'));
		$Niveau = trim(utyGetPost('Niveau'));
		$Etape = trim(utyGetPost('Etape'));
		$Nbequipes = trim(utyGetPost('Nbequipes'));
		$Type = trim(utyGetPost('Type'));
		$Date_debut = utyDateFrToUs(trim(utyGetPost('Date_debut')));
		$Date_origine = utyDateFrToUs(trim(utyGetPost('Date_origine')));
		$Date_fin = utyDateFrToUs(trim(utyGetPost('Date_fin')));
		$Nom = trim(utyGetPost('Nom'));
		$Libelle = trim(utyGetPost('Libelle'));
		$Lieu = trim(utyGetPost('Lieu'));
		$Plan_eau = trim(utyGetPost('Plan_eau'));
		$Departement = trim(utyGetPost('Departement'));
		$Responsable_insc = trim(utyGetPost('Responsable_insc'));
		$Responsable_R1 = trim(utyGetPost('Responsable_R1'));
		$Organisateur = trim(utyGetPost('Organisateur'));
		$Delegue = trim(utyGetPost('Delegue'));
		$ChefArbitre = trim(utyGetPost('ChefArbitre'));
		$Rep_athletes = trim(utyGetPost('Rep_athletes'));
		$Arb_nj1 = trim(utyGetPost('Arb_nj1'));
		$Arb_nj2 = trim(utyGetPost('Arb_nj2'));
		$Arb_nj3 = trim(utyGetPost('Arb_nj3'));
		$Arb_nj4 = trim(utyGetPost('Arb_nj4'));
		$Arb_nj5 = trim(utyGetPost('Arb_nj5'));

		$AvecMatchs = trim(utyGetPost('AvecMatchs'));
		$CodMatchs = trim(utyGetPost('CodMatchs'));
		
		$d1 = strtotime($Date_debut.' 00:00:00'); 
		$d2 = strtotime($Date_origine.' 00:00:00'); 
		$diffdate = round(($d1-$d2)/60/60/24);
		
		if ($idJournee != 0 && $duppliThis != 'Duppli') {
			if (!utyIsAutorisationJournee($idJournee)) {
                die("Vous n'avez pas l'autorisation de modifier cette journée ! (<a href='javascript:history.back()'>Retour</a>)");
            }

			try {  
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();
		
				// Modification ...
				$sql = "UPDATE kp_journee 
					SET Code_competition = ?, Code_saison = ?, 
					`Type` = ?, Phase = ?, Niveau = ?, 
					Etape = ?, Nbequipes = ?, Date_debut = ?, 
					Date_fin = ?, Nom = ?, Libelle = ?, 
					Lieu = ?, Plan_eau = ?, Departement = ?, 
					Responsable_insc = ?, Responsable_R1 = ?, 
					Organisateur = ?, Delegue = ?, 
					ChefArbitre = ?, Rep_athletes = ?, Arb_nj1 = ?, Arb_nj2 = ?,
					Arb_nj3 = ?, Arb_nj4 = ?, Arb_nj5 = ?
					WHERE Id = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$J_competition, $J_saison, $Type, $Phase, $Niveau, $Etape, $Nbequipes, $Date_debut, 
					$Date_fin, $Nom, $Libelle, $Lieu, $Plan_eau, $Departement, $Responsable_insc, 
					$Responsable_R1, $Organisateur, $Delegue, $ChefArbitre, 
					$Rep_athletes, $Arb_nj1, $Arb_nj2,
					$Arb_nj3, $Arb_nj4, $Arb_nj5, $idJournee
				));

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Modification journee, $J_saison, $J_competition, $idJournee" . '\r\n' . $e->getMessage());
	
				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}
			
			$myBdd->utyJournal('Modification journee', $J_saison, $J_competition, null, $idJournee);
		} else {
			// Création ...
			$nextIdJournee = $myBdd->GetNextIdJournee();

			try {  
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();
		
				$sql = "INSERT INTO kp_journee (Id, Code_competition, code_saison, Phase, 
					`Type`, Niveau, Etape, Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, 
					Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, 
					Delegue, ChefArbitre, Rep_athletes, Arb_nj1, Arb_nj2,
					Arb_nj3, Arb_nj4, Arb_nj5) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$nextIdJournee, $J_competition, $J_saison, $Phase, $Type, $Niveau, $Etape, $Nbequipes, 
					$Date_debut, $Date_fin, $Nom, $Libelle, $Lieu, $Plan_eau, $Departement, $Responsable_insc, 
					$Responsable_R1, $Organisateur, $Delegue, $ChefArbitre, $Rep_athletes, $Arb_nj1, $Arb_nj2,
					$Arb_nj3, $Arb_nj4, $Arb_nj5
				));
				
				//Copie des matchs
				if ($AvecMatchs == 'oui') {
					$sql2a = "CREATE TEMPORARY TABLE kp_tmp 
						(Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
					$myBdd->pdo->exec($sql2a);
					$sql2 = "INSERT INTO kp_tmp (Num) 
						SELECT DISTINCT ce.Id 
						FROM kp_competition_equipe ce, kp_journee j 
						WHERE ce.Code_compet = j.Code_competition 
						AND ce.Code_saison = j.Code_saison 
						AND j.Id = $idJournee 
						ORDER BY ce.Tirage; ";
					$myBdd->pdo->exec($sql2);

					$sql3a = "CREATE TEMPORARY TABLE kp_tmp2 
						(Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
					$myBdd->pdo->exec($sql3a);
					$sql3 = "INSERT INTO kp_tmp2 (Num) 
						SELECT DISTINCT ce.Id 
						FROM kp_competition_equipe ce, kp_journee j 
						WHERE ce.Code_compet = j.Code_competition 
						AND ce.Code_saison = j.Code_saison 
						AND j.Id = $idJournee 
						ORDER BY ce.Tirage; ";
					$myBdd->pdo->exec($sql3);

					if ($Niveau <= 1 && $CodMatchs == 'oui') {
						$sql4 = "INSERT INTO kp_match 
							(Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, `Validation`) 
							SELECT $nextIdJournee, CONCAT(m.Libelle, ' [T', ta.Id, '/T', tb.Id, ']'), 
							DATE_ADD(m.Date_match,INTERVAL +'$diffdate' DAY), m.Heure_match, m.Terrain, 
							m.Numero_ordre, m.Validation 
							FROM kp_match m, kp_tmp ta, kp_tmp2 tb 
							WHERE m.Id_journee = $idJournee 
							AND ta.Num=m.Id_equipeA 
							AND tb.Num=m.Id_equipeB ";
					} else {
						$sql4 = "INSERT INTO kp_match 
							(Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, `Validation`) 
							SELECT $nextIdJournee, m.Libelle, DATE_ADD(m.Date_match,INTERVAL +'$diffdate' DAY), 
							m.Heure_match, m.Terrain, m.Numero_ordre, m.Validation 
							FROM kp_match m 
							WHERE m.Id_journee = $idJournee ";
					}
					$myBdd->pdo->exec($sql4);
				}

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout journee, $J_saison, $J_competition, $idJournee" . '\r\n' . $e->getMessage());
	
				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}
			
			$myBdd->utyJournal('Ajout journee', $J_saison, $J_competition, null, $nextIdJournee);
		}			
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
		return;
	}
	
	function DuppliListJournees() 
	{
		$myBdd = $this->myBdd;
        $Date_debut = utyDateFrToUs(trim(utyGetPost('Date_debut')));
		$Date_fin = utyDateFrToUs(trim(utyGetPost('Date_fin')));
		$Nom = trim(utyGetPost('Nom'));
		$Libelle = trim(utyGetPost('Libelle'));
		$Lieu = trim(utyGetPost('Lieu'));
		$Plan_eau = trim(utyGetPost('Plan_eau'));
		$Departement = trim(utyGetPost('Departement'));
		$Responsable_insc = trim(utyGetPost('Responsable_insc'));
		$Responsable_R1 = trim(utyGetPost('Responsable_R1'));
		$Organisateur = trim(utyGetPost('Organisateur'));
		$Delegue = trim(utyGetPost('Delegue'));
		$ChefArbitre = trim(utyGetPost('ChefArbitre'));
		$Rep_athletes = trim(utyGetPost('Rep_athletes'));
		$Arb_nj1 = trim(utyGetPost('Arb_nj1'));
		$Arb_nj2 = trim(utyGetPost('Arb_nj2'));
		$Arb_nj3 = trim(utyGetPost('Arb_nj3'));
		$Arb_nj4 = trim(utyGetPost('Arb_nj4'));
		$Arb_nj5 = trim(utyGetPost('Arb_nj5'));
		$listJournees = explode (",", trim(utyGetPost('ParamCmd')));

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			foreach ($listJournees as $Journee) {
				// Modification ...
				$sql = "UPDATE kp_journee 
					SET Date_debut = ?, Date_fin = ?, Nom = ?, 
					Libelle = ?, Lieu = ?, Departement = ?, Plan_eau = ?, 
					Responsable_R1 = ?, Responsable_insc = ?, Organisateur = ?, 
					Delegue = ?, ChefArbitre = ?, Rep_athletes = ?, Arb_nj1 = ?, Arb_nj2 = ?,
					Arb_nj3 = ?, Arb_nj4 = ?, Arb_nj5 = ? 
					WHERE Id = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$Date_debut, $Date_fin, $Nom,
					$Libelle, $Lieu, $Departement, $Plan_eau, 
					$Responsable_R1, $Responsable_insc, $Organisateur,
					$Delegue, $ChefArbitre, $Rep_athletes, $Arb_nj1, $Arb_nj2,
					$Arb_nj3, $Arb_nj4, $Arb_nj5, $Journee
				));
			
				$myBdd->utyJournal('Modification journee', '', '', null, $Journee);
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Modification journee, $Journee" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
		return;
	}
	
	function AjustDates() 
	{
		$myBdd = $this->myBdd;
		$idJournee = trim(utyGetPost('idJournee', -1));
		if ($idJournee != 0) {
			$myBdd = $this->myBdd;
	
			$sql = "SELECT Date_debut, Date_fin 
				FROM kp_journee 
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idJournee));
			$row = $result->fetch();
			$Date_debut = $row[0];
			
			$sql = "UPDATE kp_match 
				SET Date_match = ? 
				WHERE Id_journee = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($Date_debut, $idJournee));
		}			
	}
	
	function Cancel() 
	{
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function Duplicate() 
	{
		$myBdd = $this->myBdd;
        $idJournee = trim(utyGetPost('idJournee', -1));
		if ($idJournee != 0) {
			$nextIdJournee = $myBdd->GetNextIdJournee();

			try {  
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();
	
				$sql = "INSERT INTO kp_journee (Id, Code_competition, code_saison, Phase, 
					`Type`, Niveau, Etape, Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, 
					Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, 
					ChefArbitre, Rep_athletes, Arb_nj1, Arb_nj2,
					Arb_nj3, Arb_nj4, Arb_nj5) 
					SELECT ?, Code_competition, code_saison, Phase, `Type`, Niveau, 
					Etape, Nbequipes, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, 
					Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre, 
					Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5 
					FROM kp_journee 
					WHERE Id = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($nextIdJournee, $idJournee));

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Dupplication journee, $idJournee" . '\r\n' . $e->getMessage());
	
				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}
	
		}			
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}

		$myBdd->utyJournal('Dupplication journee', '', '', null, $nextIdJournee); // A compléter (saison, compétition, options)
		return;
	}
	
	function __construct()
	{
		parent::__construct(10);
		
		$this->myBdd = new MyBdd();
		
		$alertMessage = '';
	  
		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Ok') {
                ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Ok() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'DuppliListJournees') {
                ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->DuppliListJournees() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'AjustDates') {
                ($_SESSION['Profile'] <= 2) ? $this->AjustDates() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Cancel') {
                ($_SESSION['Profile'] <= 10) ? $this->Cancel() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Duplicate') {
                ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Duplicate() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
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

