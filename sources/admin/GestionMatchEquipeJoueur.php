<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Joueurs d'une Equipe pour un Match 

class GestionMatchEquipeJoueur extends MyPageSecure	 
{	
	function Load()
	{
		$idMatch = utyGetSession('idMatch',-1);
		$idMatch = utyGetGet('idMatch',$idMatch);
		$_SESSION['idMatch'] = $idMatch;	
		
		$codeEquipe = utyGetSession('codeEquipe','?');
		$codeEquipe = utyGetGet('codeEquipe',$codeEquipe);
		$_SESSION['codeEquipe'] = $codeEquipe;	
		
		$_SESSION['parentUrl'] = $_SERVER['PHP_SELF'];
		
		$_SESSION['updatecell_tableName'] = 'kp_match_joueur';
		$_SESSION['updatecell_where'] = "Where Id_match = $idMatch And Matric = ";
		$_SESSION['updatecell_document'] = 'formMatchEquipeJoueur';
				
		$idEquipe = -1;
				
		$myBdd = new MyBdd();
		
		// Chargement des Joueurs provenant de la Recherche ...
		if (isset($_SESSION['Signature'])) {
			$sql = "REPLACE INTO kp_match_joueur (Id_match, Matric, Equipe) 
				SELECT ?, a.Matric, ? 
				FROM kp_licence a, kp_recherche_licence b 
				WHERE a.Matric = b.Matric 
				AND b.Signature = ? 
				AND b.Validation = 'O' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $codeEquipe, $_SESSION['Signature'] ));
			// $result->debugDumpParams();


			// Vidage kp_recherche_licence ...				
			$sql = "DELETE FROM kp_recherche_licence 
				WHERE `Signature` = ?  ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($_SESSION['Signature']));
			
			unset($_SESSION['Signature']);
		}

		$clefEntraineur = '';
		
		if ($idMatch > 0) {
			// Prise des Informations sur le Match ...
			$sql = "SELECT Date_match, Heure_match, Libelle, Terrain, Numero_ordre, 
				`Validation`, Id_journee 
				FROM kp_match 
				WHERE Id =  ?";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
			if ($result->rowCount() == 1) {
					$row = $result->fetch();	 
					$Numero_ordre = $row['Numero_ordre'];
					// Titre ...
					$this->m_tpl->assign('headerTitle', '(Saison '.$myBdd->GetActiveSaison().') Match n°'.$row['Numero_ordre'].' du '.utyDateUsToFr($row['Date_match']).' à '.$row['Heure_match'].' Terrain '.$row['Terrain'].' ('.$idMatch.')');	
					$this->m_tpl->assign('idJournee', $row['Id_journee']);
					$this->m_tpl->assign('Validation', $row['Validation']);
			}
			
			// Prise des Informations sur l'Equipe ...
			if ($codeEquipe == 'A') {
				$sql = "SELECT a.Id, a.Code_compet, a.Code_saison, a.Libelle, a.Code_club 
					FROM kp_competition_equipe a,	kp_match b 
					WHERE a.Id  = b.Id_EquipeA 
					AND b.Id = ? ";
			} else {
				$sql = "SELECT a.Id, a.Code_compet, a.Code_saison, a.Libelle, a.Code_club 
					FROM kp_competition_equipe a,	kp_match b 
					WHERE a.Id  = b.Id_EquipeB 
					AND b.Id = ? ";
			}
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
			if ($result->rowCount() == 1) {
				$row = $result->fetch();	 
				$idEquipe = $row['Id'];
			
				$infoEquipe = $row['Libelle'].' ('.$row['Code_compet'].'-'.$row['Code_saison'].')';
				$_SESSION['idEquipe'] = $idEquipe;
				$_SESSION['infoEquipe'] = $infoEquipe;
				$_SESSION['codeClub'] = $row['Code_club'];
				
				// Sous-Titre ...
				$this->m_tpl->assign('headerSubTitle', 'Joueurs Equipe '.$codeEquipe.' : '.$infoEquipe);
			}
			
			// Chargement des Joueurs de l'Equipe déja inscrit ...
			$arrayJoueur = array();
			
			$sql = "SELECT a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, 
				b.Sexe, b.Naissance, b.Origine, b.Numero_club, b.Pagaie_ECA, b.Pagaie_EVI, 
				b.Pagaie_MER, b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, 
				b.Reserve icf, c.arbitre, c.niveau 
				FROM kp_licence b, kp_match_joueur a 
				LEFT OUTER JOIN kp_arbitre c ON (a.Matric = c.Matric) 
				WHERE a.Matric = b.Matric 
				AND a.Id_match = ? 
				AND a.Equipe = ? 
				ORDER BY Field(IF(a.Capitaine='C','-',
					IF(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), 
				Numero, Nom, Prenom ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $codeEquipe));
            while ($row = $result->fetch()) {
				if ($row['niveau'] != '')
					$row['arbitre'] .= '-'.$row['niveau'];
				
				$numero = $row['Numero'];
				if (strlen($numero) == 0)
					$numero = 0;
				
				$controlePagaie = controle_pagaie($row['Pagaie_ECA'], $row['Pagaie_EVI'], $row['Pagaie_MER']);
				$pagaie = $controlePagaie['pagaie'];
				$PagaieValide = $controlePagaie['PagaieValide'];
				if ($PagaieValide > 1) {
					$pagaie = '(' . $pagaie . ')';
				}
					
				$capitaine = $row['Capitaine'];
				if (strlen($capitaine) == 0)
					$capitaine = '-';
					
				array_push($arrayJoueur, array( 'Matric' => $row['Matric'], 'Numero' => $numero, 
					'Capitaine' => $capitaine, 
					'Nom' => mb_strtoupper($row['Nom']), 
					'Prenom' => mb_convert_case($row['Prenom'], MB_CASE_TITLE, "UTF-8"), 
					'Pagaie' => $pagaie, 
					'CertifCK' => $row['CertifCK'], 'CertifAPS' => $row['CertifAPS'], 
					'Sexe' => $row['Sexe'], 'Categ' => utyCodeCategorie2($row['Naissance']), 
					'Pagaie_ECA' => $row['Pagaie_ECA'], 'Pagaie_EVI' => $row['Pagaie_EVI'] , 
					'Pagaie_MER' => $row['Pagaie_MER'], 'Arbitre' => $row['arbitre'], 
					'PagaieValide' => $PagaieValide, 
					'Saison' => $row['Origine'], 'Numero_club' => $row['Numero_club'], 
					'icf' => $row['icf']
				));
			}
		}	
		
		$this->m_tpl->assign('arrayJoueur', $arrayJoueur);
		$this->m_tpl->assign('idMatch', $idMatch);
		$this->m_tpl->assign('sSaison', $myBdd->GetActiveSaison());
		$this->m_tpl->assign('Numero_ordre', $Numero_ordre);
		$this->m_tpl->assign('infoEquipe', $infoEquipe);
	}
	
	function AddJoueurTitulaire()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql = "SELECT `Validation` 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$row = $result->fetch();
		if ($row['Validation'] == 'O')
			return;

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();
	
			$sql = "REPLACE INTO kp_match_joueur 
				SELECT ?, Matric, Numero, ?, Capitaine 
				FROM kp_competition_equipe_joueur 
				WHERE Id_equipe = ? 
				AND Capitaine <> 'X' 
				AND Capitaine <> 'A' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $codeEquipe, $idEquipe));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Ajout titulaires match, $idMatch, $idEquipe" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Ajout titulaires match', '', '', null, null, $idMatch, 'Equipe : '.$idEquipe);
		return;
	}
	
	function Add2()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);

		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql = "SELECT `Validation` 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$row = $result->fetch();
		if ($row['Validation'] == 'O')
			return;

		$matricJoueur = utyGetPost('matricJoueur2', '');
		$capitaineJoueur = utyGetPost('capitaineJoueur2', '-');
		$numeroJoueur = utyGetPost('numeroJoueur2', '');
		
		if (strlen($idEquipe) > 0) {
			// $categJoueur = utyCodeCategorie2($naissanceJoueur);
			if (strlen($matricJoueur) == 0)
				$matricJoueur = $myBdd->GetNextMatricLicence();
			//$idMatch, Matric, Numero, '$codeEquipe', Capitaine

			try {  
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();
		
				$sql = "REPLACE INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine) 
					VALUES (?, ?, ?, ?, ?) ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$idMatch, $matricJoueur, $numeroJoueur, $codeEquipe, $capitaineJoueur
				));

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout joueur, $idMatch, Equipe: $codeEquipe - Joueur: $matricJoueur" . '\r\n' . $e->getMessage());
	
				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}
				
			$myBdd->utyJournal('Ajout joueur', '', '', null, null, null, 'Match:'.$idMatch.' - Equipe:'.$codeEquipe.' - Joueur:'.$matricJoueur);
			return;
		}
	}
	
	function DelJoueurs()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);

		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql = "SELECT `Validation` 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$row = $result->fetch();
		if ($row['Validation'] == 'O')
			return;

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();
	
			$sql = "DELETE FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Equipe = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $codeEquipe));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression joueurs match, $idMatch, Equipe: $idEquipe" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Suppression joueurs match', '', '', null, null, $idMatch, 'Equipe : '.$idEquipe);
		return;
	}
	
	function copieCompoEquipeJournee()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$idJournee = utyGetPost('ParamCmd');

		$myBdd = new MyBdd();
			
		//Sélection des matchs de destination
		$sql = "SELECT Id, Id_equipeA, Id_equipeB 
			FROM kp_match 
			WHERE Id_journee = ? 
			AND `Validation` != 'O' 
			AND Id != ? 
			AND (Id_equipeA = ? 
				OR Id_equipeB = ?) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee, $idMatch, $idEquipe, $idEquipe));

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();
	
			$sql2 = "DELETE FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Equipe = ? ";
			$result2 = $myBdd->pdo->prepare($sql2);

			$sql3 = "SELECT Matric, Numero, Capitaine 
				FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Equipe = ? ";
			$result3 = $myBdd->pdo->prepare($sql3);

			$sql4 = "INSERT INTO kp_match_joueur 
				(Id_match, Matric, Numero, Equipe, Capitaine) 
				VALUES (?, ?, ?, ?, ?) ";
			$result4 = $myBdd->pdo->prepare($sql4);

			while ($row = $result->fetch()) {
				($row['Id_equipeA'] == $idEquipe) ? $AB = 'A' : $AB = 'B';
				// Vidage
				$result2->execute(array($row['Id'], $AB));
				
				//Selection de la compo à copier
				$result3->execute(array($idMatch, $codeEquipe));
				while ($row3 = $result3->fetch()) {
					//Insertion ligne par ligne compo à copier
					$result4->execute(array(
						$row['Id'], $row3['Matric'], $row3['Numero'], $AB, $row3['Capitaine']
					));
				}
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Copie Compo sur Journée, $idMatch, $idEquipe" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Copie Compo sur Journée', $myBdd->GetActiveSaison(), utyGetSession('Compet'), null, null, $idMatch, 'Equipe : '.$idEquipe);
		return;
	}

	function copieCompoEquipeCompet()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$idJournee = utyGetPost('ParamCmd');
		$arrayJournees = [];

		$myBdd = new MyBdd();
		//Sélection de la compétition et des journées concernées
		$sql = "SELECT Id 
			FROM kp_journee 
			WHERE Code_competition = ( 
				SELECT Code_competition 
				FROM kp_journee 
				WHERE Id = $idJournee) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		while ($row = $result->fetch()) {
			$arrayJournees[] = $row['Id'];
		}
		
		//Sélection des matchs de destination
		$in  = str_repeat('?,', count($arrayJournees) - 1) . '?';
		$sql = "SELECT Id, Id_equipeA, Id_equipeB 
			FROM kp_match 
			WHERE Id_journee IN ($in) 
			AND `Validation` !=  'O' 
			AND Id != ?  
			AND (Id_equipeA = ? 
				OR Id_equipeB = ?) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array_merge($arrayJournees, [$idMatch], [$idEquipe], [$idEquipe]));

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();
	
			$sql2 = "DELETE FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Equipe = ? ";
			$result2 = $myBdd->pdo->prepare($sql2);

			$sql3 = "SELECT Matric, Numero, Capitaine 
				FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Equipe = ? ";
			$result3 = $myBdd->pdo->prepare($sql3);

			$sql4 = "INSERT INTO kp_match_joueur 
				(Id_match, Matric, Numero, Equipe, Capitaine) 
				VALUES (?, ?, ?, ?, ?) ";
			$result4 = $myBdd->pdo->prepare($sql4);

			while ($row = $result->fetch()) {
				($row['Id_equipeA'] == $idEquipe) ? $AB = 'A' : $AB = 'B';
				// Vidage
				$result2->execute(array($row['Id'], $AB));
				
				//Selection de la compo à copier
				$result3->execute(array($idMatch, $codeEquipe));
				while ($row3 = $result3->fetch()) {
					//Insertion ligne par ligne compo à copier
					$result4->execute(array(
						$row['Id'], $row3['Matric'], $row3['Numero'], $AB, $row3['Capitaine']
					));
				}
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Copie Compo sur Compet, $idMatch, $idEquipe" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}
				
		$myBdd->utyJournal('Copie Compo sur Compet', $myBdd->GetActiveSaison(), utyGetSession('Compet'), null, null, $idMatch, 'Equipe : '.$idEquipe);
		return;
	}

	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
	
		$idMatch = utyGetSession('idMatch');

		$myBdd = new MyBdd();
		// Contrôle verrouillage Match ...
		$sql = "SELECT `Validation` 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$row = $result->fetch();
		if ($row['Validation'] == 'O')
			return;

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();
	
			$in  = str_repeat('?,', count($arrayParam) - 1) . '?';
			$sql = "DELETE FROM kp_match_joueur 
				WHERE Id_match = ? 
				AND Matric IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array_merge([$idMatch], $arrayParam));	

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression joueurs match, $idMatch, $ParamCmd" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}
		
		$myBdd->utyJournal('Suppression joueurs match', '', '', null, null, $idMatch, 'joueurs : '.$ParamCmd);
		return;
	}
	
	function FindLicence()
	{
		$myBdd = new MyBdd();
		$_SESSION['Signature'] = uniqid('GMEJ-');

		if (isset($_SESSION['codeClub']))
		{
			$_SESSION['codeComiteDep'] = $myBdd->GetCodeComiteDept($_SESSION['codeClub']);
			$_SESSION['codeComiteReg'] = $myBdd->GetCodeComiteReg($_SESSION['codeComiteDep']);
		}
		
		header("Location: RechercheLicence.php");	
		exit;	
	}
	
	function __construct()
	{			
	  	parent::__construct(9);
		
		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd','');
		$ParamCmd = utyGetPost('ParamCmd','');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'AddJoueurTitulaire')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $alertMessage = $this->AddJoueurTitulaire() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Add2')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $alertMessage = $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'DelJoueurs')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $alertMessage = $this->DelJoueurs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'FindLicence')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->FindLicence() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'copieCompoEquipeJournee')
				($_SESSION['Profile'] <= 6) ? $alertMessage = $this->copieCompoEquipeJournee($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'copieCompoEquipeCompet')
				($_SESSION['Profile'] <= 4) ? $alertMessage = $this->copieCompoEquipeCompet($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion des joueurs d'une Equipe pour un Match", "Matchs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionMatchEquipeJoueur');
	}
}		  	

$page = new GestionMatchEquipeJoueur();
