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
                
		$codeSaison = $myBdd->GetActiveSaison();
        $SaisonAthlete = utyGetSession('SaisonAthlete', $codeSaison);
        $SaisonAthlete = utyGetPost('SaisonAthlete', $SaisonAthlete);
		$this->m_tpl->assign('SaisonAthlete', $SaisonAthlete);

        // Saisons	
        $arraySaison = array();
        $sql = "SELECT Code 
            FROM gickp_Saison 
            ORDER BY Code DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			array_push($arraySaison, $row);
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);

		// Chargement des Informations relatives à l'athlète
		if ($Athlete != '') {
			// Données générales
			$sql = "SELECT c.*, cl.Libelle nomclub, dep.Libelle nomcd, reg.Libelle nomcr, 
                s.Date date_surclassement 
                FROM gickp_Club cl, gickp_Comite_dep dep, gickp_Comite_reg reg, gickp_Liste_Coureur c 
                LEFT OUTER JOIN gickp_Surclassements s 
                    ON (c.Matric = s.Matric AND s.Saison = ?) 
                WHERE c.Numero_club = cl.Code 
                AND c.Numero_comite_dept = dep.Code 
                AND c.Numero_comite_reg = reg.Code 
                AND c.Matric = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($SaisonAthlete, $Athlete));
			if ($result->rowCount() != 1) {
                return;
            }
            $row = $result->fetch();
            if ($_SESSION['lang'] == 'fr') {
                $row['date_surclassement'] = utyDateUsToFr($row['date_surclassement']);
            }
			$this->m_tpl->assign('Courreur', $row);
			$this->m_tpl->assign('Athlete_id', $row['Nom'].' '.$row['Prenom']);
			// Arbitre
            $sql = "SELECT * FROM gickp_Arbitre 
                WHERE Matric = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Athlete));
			if ($result->rowCount() == 1) {
                $row = $result->fetch();
				switch ($row['Arb']) {
					case 'Int':
						$row['Arb']='INTERNATIONAL';
						break;
					case 'Nat':
						$row['Arb']='NATIONAL';
						break;
					case 'Reg':
						$row['Arb']='REGIONAL';
						break;
					case 'OTM':
						$_SESSION['lang'] == 'en' ? $row['Arb'] = 'Game official' : $row['Arb'] = 'Officiel table de marque';
						break;
					case 'JO':
						$_SESSION['lang']== 'en' ? $row['Arb'] = 'Young official' : $row['Arb'] = 'Jeune officiel';
						break;
					default :
						$row['Arb']='-';
						break;
				}
				$this->m_tpl->assign('Arbitre', $row);
			}
			// Titulaire
			$Titulaire = array();
			$sql = "SELECT cej.*, ce.*, cej.Numero Num 
                FROM gickp_Competitions_Equipes_Joueurs cej, gickp_Competitions_Equipes ce 
                WHERE cej.Matric = ? 
                AND cej.Id_equipe = ce.Id 
                AND ce.Code_compet != 'POOL' 
                AND ce.Code_saison = ? 
                ORDER BY ce.Code_compet ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($Athlete, $SaisonAthlete));
			while ($row = $result->fetch()) {
				array_push($Titulaire, $row);
			}
			$this->m_tpl->assign('Titulaire', $Titulaire);

			// Arbitrages
			$Arbitrages = array();
			$sql = "SELECT m.*, j.*, m.id Identifiant, 
                IF(m.Matric_arbitre_principal = :Athlete,'Prin','') Prin, 
                IF(m.Matric_arbitre_secondaire = :Athlete,'Sec','') Sec 
                FROM gickp_Matchs m, gickp_Journees j 
                WHERE (m.Matric_arbitre_principal = :Athlete 
                    OR m.Matric_arbitre_secondaire = :Athlete) 
                AND m.Id_journee = j.Id 
                AND j.Code_saison = :SaisonAthlete 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(':Athlete' => $Athlete, ':SaisonAthlete' => $SaisonAthlete));
			while ($row = $result->fetch()) {
                if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($Arbitrages, $row);
			}
			$this->m_tpl->assign('Arbitrages', $Arbitrages);

			// Table de marque
			$OTM = array();
            $sql = "SELECT m.*, j.*, m.id Identifiant, 
                IF(m.Secretaire LIKE :Athlete,'Sec','') Sec, 
                IF(m.Chronometre LIKE :Athlete,'Chrono','') Chrono, 
                IF(m.Timeshoot LIKE :Athlete,'TS','') TS, 
                IF(m.Ligne1 LIKE :Athlete OR m.Ligne2 LIKE :Athlete,'Ligne','') Ligne 
                FROM gickp_Matchs m, gickp_Journees j 
                WHERE (m.Secretaire LIKE :Athlete 
                    OR m.Chronometre LIKE :Athlete 
                    OR m.Timeshoot LIKE :Athlete 
                    OR m.Ligne1 LIKE :Athlete 
                    OR m.Ligne2 LIKE :Athlete ) 
                AND m.Id_journee = j.Id 
                AND j.Code_saison = :SaisonAthlete 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(':Athlete' => '%('.$Athlete.')%', ':SaisonAthlete' => $SaisonAthlete));
			while ($row = $result->fetch()) {
				if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($OTM, $row);
			}
			$this->m_tpl->assign('OTM', $OTM);

			// Joueur
			$Joueur = array();
			$sql = "SELECT mj.*, m.*, m.id Identifiant, j.*, mj.Numero Num, ceA.Libelle eqA, 
                ceB.Libelle eqB, 
                SUM(IF(b.Id_evt_match='B',1,0)) But, 
                SUM(IF(b.Id_evt_match='V',1,0)) Vert, 
                SUM(IF(b.Id_evt_match='J',1,0)) Jaune, 
                SUM(IF(b.Id_evt_match='R',1,0)) Rouge, 
                SUM(IF(b.Id_evt_match='T',1,0)) Tir, 
                SUM(IF(b.Id_evt_match='A',1,0)) Arret 
                FROM gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ceA, 
                    gickp_Competitions_Equipes ceB, gickp_Matchs_Joueurs mj 
                LEFT OUTER JOIN gickp_Matchs_Detail b 
                    ON (mj.Matric = b.Competiteur AND mj.Id_match = b.Id_match) 
                WHERE mj.Matric = :Athlete 
                AND mj.Id_match = m.Id 
                AND m.Id_journee = j.Id 
                AND m.Id_equipeA = ceA.Id 
                AND m.Id_equipeB = ceB.Id 
                AND j.Code_saison = :SaisonAthlete 
                GROUP BY m.Id 
                ORDER BY m.Date_match DESC, m.Heure_match DESC ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(':Athlete' => $Athlete, ':SaisonAthlete' => $SaisonAthlete));
			while ($row = $result->fetch()) {
				if ($row['ScoreA'] != '?' && $row['ScoreA'] != '' && $row['ScoreB'] != '?' && $row['ScoreB'] != '') {
                    $row['ScoreOK'] = 'O';
                } else {
                    $row['ScoreOK'] = 'N';
                }
                if ($_SESSION['lang'] == 'fr') {
                    $row['Date_match'] = substr(utyDateUsToFr($row['Date_match']), 0, 5);
                } else {
                    $row['Date_match'] = substr($row['Date_match'], 5, 5);
                }
                array_push($Joueur, $row);
			}
			$this->m_tpl->assign('Joueur', $Joueur);
		}
	}
	
	function Update()
	{
        $myBdd = new MyBdd();
        $update_matric = utyGetPost('update_matric');
        if ($update_matric < 2000000) {
            return 'Modification interdite !';
        }
        $update_nom = strtoupper(trim(utyGetPost('update_nom')));
        $update_prenom = strtoupper(trim(utyGetPost('update_prenom')));
        $update_sexe = trim(utyGetPost('update_sexe'));
        $update_naissance = utyDateFrToUs(trim(utyGetPost('update_naissance')));
        $update_saison = trim(utyGetPost('update_saison'));
        $update_icf = (int) trim(utyGetPost('update_icf'));
        $update_arb = trim(utyGetPost('update_arb'));
        $update_niveau = trim(utyGetPost('update_niveau'));
        $update_club = trim(utyGetPost('update_club'));
        $update_cd = trim(utyGetPost('update_cd'));
        $update_cr = trim(utyGetPost('update_cr'));
        
        $sql = "UPDATE gickp_Liste_Coureur 
            SET Origine = ?, Nom = ?, 
            Prenom = ?, Sexe = ?, 
            Naissance = ? ";
        $arrayQuery = array($update_saison, $update_nom, $update_prenom, 
            $update_sexe, $update_naissance);

        if ($update_icf > 0) {
            $sql .= ", Reserve = ? ";
            $arrayQuery = array_merge($arrayQuery, [$update_icf]);
        } else {
            $sql .= ", Reserve = NULL ";
        }
        if ($update_club != '') {
            $sql .= ", Numero_club = ?, 
            Numero_comite_dept = ?, 
            Numero_comite_reg = ? ";
            $arrayQuery = array_merge($arrayQuery, [$update_club], [$update_cd], [$update_cr]);
        }
        $sql .= "WHERE Matric = ? ";
        $arrayQuery = array_merge($arrayQuery, [$update_matric]);
        $result = $myBdd->pdo->prepare($sql);
        $result->execute($arrayQuery);
    
        $sql = "UPDATE gickp_Competitions_Equipes_Joueurs 
            SET Nom = ?, Prenom = ?, 
            Sexe = ? 
            WHERE Matric = ? ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($update_nom, $update_prenom, $update_sexe, $update_matric));
        
        $sql = "REPLACE INTO gickp_Arbitre VALUES (?, ";
        switch ($update_arb) {
            case 'Reg' :
                $sql .= "'O','N','N','N','Reg','',?,?) ";
                break;
            case 'IR' :
                $sql .= "'N','O','N','N','IR','',?,?) ";
                break;
            case 'Nat' :
                $sql .= "'N','N','O','N','Nat','',?,?) ";
                break;
            case 'Int' :
                $sql .= "'N','N','O','O','Int','',?,?) ";
                break;
            case 'OTM' :
                $sql .= "'N','N','O','N','OTM','',?,?) ";
                break;
            case 'JO' :
                $sql .= "'N','N','O','N','JO','',?,?) ";
                break;
            default :
                $sql .= "'N','N','N','N','','',?,?) ";
                $update_niveau = '';
                $update_saison = '';
                break;
        }
        
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($update_matric, $update_niveau, $update_saison));

        return "Modification effectuée !";
	}
	
	function FusionJoueurs()
	{
		$myBdd = new MyBdd();
		$numFusionSource = utyGetPost('numFusionSource', 0);
		$numFusionCible = utyGetPost('numFusionCible', 0);
        $sql = "UPDATE gickp_Matchs_Detail 
            SET Competiteur = ? 
            WHERE Competiteur = ? ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($numFusionCible, $numFusionSource));

        $sql = "UPDATE gickp_Matchs_Joueurs 
            SET Matric = ? 
            WHERE Matric = ? ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($numFusionCible, $numFusionSource));

		$sql = "UPDATE gickp_Competitions_Equipes_Joueurs 
            SET Matric = ? 
            WHERE Matric = ? ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($numFusionCible, $numFusionSource));

        $sql = "DELETE FROM gickp_Liste_Coureur 
            WHERE Matric = ? ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($numFusionSource));

		$myBdd->utyJournal('Fusion Joueurs', $myBdd->GetActiveSaison(), utyGetSession('codeCompet'), 'NULL', 'NULL', 'NULL', $numFusionSource.' => '.$numFusionCible);
		return('Joueurs fusionnés : ');
	}
	
	function __construct()
	{			
        MyPageSecure::MyPageSecure(7);
        
		$alertMessage = '';
	  
		$Cmd = utyGetPost('Cmd', '');
		$ParamCmd = utyGetPost('ParamCmd', '');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Update') {
                ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

			if ($Cmd == 'FusionJoueurs') {
                ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->FusionJoueurs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
	
		$this->SetTemplate("Statistiques_athlete", "Athletes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		
		$this->DisplayTemplate('GestionAthlete');
	}
}		  	

$page = new GestionAthlete();
