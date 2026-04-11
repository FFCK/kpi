<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Evenements

class GestionGroupe extends MyPageSecure
{
	function Load()
	{
		$idGroupe = (int) utyGetSession('idGroupe', -1);

		// Chargement des Groupes
		$myBdd = new MyBdd();
		$arrayGroupes = array();
		$arraySectionNames = [
			1 => 'International',
			2 => 'National',
			3 => 'Regional',
			4 => 'Tournoi/Tournament',
			5 => 'Continental',
			100 => 'Divers/Miscellaneous'
		];

		$sql = "SELECT * 
			FROM kp_groupe 
			ORDER BY section, ordre ";
		$arrayGroupes = array();
		$groupe = null;
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			if ($idGroupe == $row['id']) {
				$row['selected'] = 'selected';
				$groupe = $row;
			} else {
				$row['selected'] = '';
			}
			$row['section_name'] = $arraySectionNames[$row['section']];
			array_push($arrayGroupes, $row);
		}

		$this->m_tpl->assign('groupe', $groupe);
		$this->m_tpl->assign('arrayGroupes', $arrayGroupes);
		$this->m_tpl->assign('arraySectionNames', $arraySectionNames);
	}

	function Add()
	{
		$libelle = utyGetPost('Libelle');
		$libelle_en = utyGetPost('Libelle_en');
		$section = utyGetPost('section');
		$ordre = utyGetPost('ordre');
		$Code_niveau = utyGetPost('Code_niveau');
		$Groupe = utyGetPost('Groupe');

		$myBdd = new MyBdd();

		$sql = "UPDATE kp_groupe
			SET ordre = ordre + 1
			WHERE ordre >= ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($ordre));

		$sql = "INSERT INTO kp_groupe
			SET Libelle = ?, Libelle_en = ?, section = ?, ordre = ?, Code_niveau = ?, Groupe = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			$libelle, $libelle_en ?: null, $section, $ordre, $Code_niveau, $Groupe
		));

		$this->Raz();
		$myBdd->utyJournal('Ajout Groupe', '', '', $Groupe);
		return "Ajout effectué.";
	}

	function UpOrder($idGroupe, $ParamCmd)
	{
		$id = (int) $idGroupe;
		$ordre = (int) $ParamCmd;
		$ordre_up = $ordre - 1;

		$myBdd = new MyBdd();

		$sql = "UPDATE kp_groupe 
			SET ordre = ? 
			WHERE ordre = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($ordre, $ordre_up));

		$sql = "UPDATE kp_groupe 
			SET ordre = ? 
			WHERE id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($ordre_up, $id));

		$myBdd->utyJournal('Ordre Groupe Up', '', '', $id);
		return;
	}

	function Remove($idGroupe)
	{
		$myBdd = new MyBdd();
		$sql = "SELECT c.Code_saison, c.Code 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_ref = g.Groupe 
			AND g.id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idGroupe));
		$num_results = $result->rowCount();

		if ($num_results > 0) {
			$conflict = '';
			while ($row = $result->fetch()) {
				$conflict .= ' ' . $row['Code_saison'] . '-' . $row['Code'];
			}
			return "Il existe des compétitions dans ce groupe :$conflict. Suppression impossible !";
		}

		$sql = "SELECT ordre FROM kp_groupe 
			WHERE id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idGroupe));
		$row = $result->fetch();

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM kp_groupe 
				WHERE id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idGroupe));

			$sql = "UPDATE kp_groupe 
				SET ordre = ordre - 1 
				WHERE ordre > ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($row['ordre']));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression groupe, $idGroupe" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		return "Suppression effectuée.";
	}

	function Edit($idGroupe)
	{
		$_SESSION['idGroupe'] = $idGroupe;
	}

	function Raz()
	{
		$_SESSION['idGroupe'] = -1;
	}

	function Update()
	{
		$idGroupe = utyGetPost('idGroupe');
		$libelle = utyGetPost('Libelle');
		$libelle_en = utyGetPost('Libelle_en');
		$section = utyGetPost('section');
		$ordre = utyGetPost('ordre');
		$Code_niveau = utyGetPost('Code_niveau');
		$Groupe = utyGetPost('Groupe');
		$oldGroupe = utyGetPost('oldGroupe');

		$myBdd = new MyBdd();

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			// Si le code groupe a changé, désactiver temporairement les FK pour permettre la mise à jour
			if ($oldGroupe && $Groupe && $oldGroupe !== $Groupe) {
				$myBdd->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
			}

			$sql = "UPDATE kp_groupe
				SET Libelle = ?, Libelle_en = ?, section = ?, ordre = ?, Code_niveau = ?, Groupe = ?
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array(
				$libelle, $libelle_en ?: null, $section, $ordre, $Code_niveau, $Groupe, $idGroupe
			));

			// Si le code groupe a changé, mettre à jour les Code_ref dans kp_competition
			if ($oldGroupe && $Groupe && $oldGroupe !== $Groupe) {
				$sql = "UPDATE kp_competition
					SET Code_ref = ?
					WHERE Code_ref = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($Groupe, $oldGroupe));

				$myBdd->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Modif Groupe, $libelle" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$this->Raz();
		$myBdd->utyJournal('Modif Groupe', '', '', $idGroupe);
		return "Mise à jour effectuée.";
	}

	function __construct()
	{
		parent::__construct(2);

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd', '');
		$ParamCmd = utyGetPost('ParamCmd', 0);
		$idGroupe = utyGetPost('idGroupe', 0);

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add') ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Remove') ($_SESSION['Profile'] <= 1) ? $alertMessage = $this->Remove($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Edit') ($_SESSION['Profile'] <= 2) ? $this->Edit($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Update') ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Raz') ($_SESSION['Profile'] <= 2) ? $this->Raz() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'UpOrder') ($_SESSION['Profile'] <= 2) ? $this->UpOrder($idGroupe, $ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($alertMessage == '') {
				header("Location: " . utyGetScheme() . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_groupes", "Operations", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionGroupe');
	}
}

$page = new GestionGroupe();
