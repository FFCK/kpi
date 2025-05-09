<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Utilisateurs

class GestionUtilisateur extends MyPageSecure
{
	function IsSelected($code, $arraySelection)
	{
		foreach ($arraySelection as $selectValue) {
			if ($selectValue == $code) {
				return 'SELECTED';
			}
		}
		return '';
	}

	function IsSaisonSelectedPost($code)
	{
		if (utyGetPost('comboSaison', false)) {
			return $this->IsSelected($code, utyGetPost('comboSaison', false));
		}
		return '';
	}

	function IsCompetitionSelectedPost($code)
	{
		if (utyGetPost('comboCompetition', false)) {
			return $this->IsSelected($code, utyGetPost('comboCompetition', false));
		}
		return '';
	}

	function IsEvenementSelectedPost($code)
	{
		if (utyGetPost('comboEvenement', false)) {
			return $this->IsSelected($code, utyGetPost('comboEvenement', false));
		}
		return '';
	}

	function IsStringSelected($code, $string)
	{
		$key = '|' . $code . '|';
		if (strstr($string, $key) == FALSE) {
			return '';
		}
		return 'SELECTED';
	}

	function GetFiltreSaison()
	{
		if (utyGetPost('comboSaison', false)) {
			return $this->SetFiltreSaison(utyGetPost('comboSaison', false));
		}
		return '';
	}

	function SetFiltreSaison($arraySaison)
	{
		$lstSaison = '';
		foreach ($arraySaison as $selectValue) {
			if ($selectValue == '*') {
				return '';
			}
			if ($lstSaison != '') {
				$lstSaison .= '|';
			}
			$lstSaison .= $selectValue;
		}
		if ($lstSaison == '') {
			return '';
		}
		return '|' . $lstSaison . '|';
	}

	function GetFiltreCompetition()
	{
		if (utyGetPost('comboCompetition', false)) {
			return $this->SetFiltreCompetition(utyGetPost('comboCompetition', false));
		}
		return '';
	}

	function SetFiltreCompetition($arrayCompetition)
	{
		$lstCompetition = '';
		foreach ($arrayCompetition as $selectValue) {
			if ($selectValue == '*') {
				return '';
			}
			if ($lstCompetition != '') {
				$lstCompetition .= '|';
			}
			$lstCompetition .= $selectValue;
		}
		if ($lstCompetition == '') {
			return '';
		}
		return '|' . $lstCompetition . '|';
	}

	function GetFiltreEvenement()
	{
		if (utyGetPost('comboEvenement', false)) {
			return $this->SetFiltreEvenement(utyGetPost('comboEvenement', false));
		}
		return '';
	}

	function SetFiltreEvenement($arrayEvenement)
	{
		$lstEvenement = '';
		foreach ($arrayEvenement as $selectValue) {
			if ($selectValue == '*') {
				return '';
			}
			if ($lstEvenement != '') {
				$lstEvenement .= '|';
			}
			$lstEvenement .= $selectValue;
		}
		if ($lstEvenement == '') {
			return '';
		}
		return '|' . $lstEvenement . '|';
	}

	function Load($selectUser)
	{
		$myBdd = new MyBdd();
		$user = $_SESSION['User'];
		$profile = $_SESSION['Profile'];
		$limitProfils = utyGetPost('limitProfils', '%');
		$this->m_tpl->assign('limitProfils', $limitProfils);
		$limitSaisons = utyGetPost('limitSaisons', '%');
		$this->m_tpl->assign('limitSaisons', $limitSaisons);
		$Saison = $myBdd->GetActiveSaison();
		$this->m_tpl->assign('Saison', $Saison);


		// Chargement des Utilisateurs ...
		$sql = "SELECT u.* 
			FROM kp_user u 
			WHERE u.Niveau >= ? 
			AND u.Niveau LIKE ? 
			ORDER BY u.Niveau, u.Identite, u.Fonction ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($profile, $limitProfils));

		$typeFiltreCompetition = '2';
		$filtreSaison = '*';
		$filtreCompetition = '*';
		$filtreEvenement = '';

		$arrayUser = array();
		$emails = '';

		while ($row = $result->fetch()) {
			$StdOrSelected = '';
			if ($selectUser == $row["Code"]) {
				$StdOrSelected = 'selected';
			}

			$filtreSaisons = explode('|', $row['Filtre_saison']);
			$filtreSaisons = array_slice($filtreSaisons, 1);
			$filtreSaisons = implode(', ', $filtreSaisons);

			$filtreCompets = explode('|', $row['Filtre_competition']);
			$filtreCompets = array_slice($filtreCompets, 1);
			$filtreCompets = implode(', ', $filtreCompets);

			$row["Date_debut"] = utyDateUsToFr($row["Date_debut"]);
			$row["Date_fin"] = utyDateUsToFr($row["Date_fin"]);

			if (mb_ereg('(' . $limitSaisons . ')', $filtreSaisons) || $limitSaisons == '%' || $filtreSaisons == '') {
				array_push($arrayUser, array(
					'Code' => $row["Code"], 'Identite' => $row["Identite"],
					'StdOrSelected' => $StdOrSelected, 'filtreSaisons' => $filtreSaisons,
					'Limitation_equipe_club' => $row['Limitation_equipe_club'],
					'filtreCompets' => $filtreCompets, 'Mail' => $row["Mail"],
					'Tel' => $row["Tel"], 'Fonction' => $row["Fonction"], 'Niveau' => $row['Niveau'],
					'Id_Evenement' => $row["Id_Evenement"], 'Date_debut' => $row["Date_debut"],
					'Date_fin' => $row["Date_fin"],
				));
				$emails .= $row["Mail"] . ',';
			}

			if ($selectUser == $row["Code"]) {
				$this->m_tpl->assign('action', 'Update');

				$this->m_tpl->assign('guser', $selectUser);
				$this->m_tpl->assign('gidentite', $row['Identite']);
				$this->m_tpl->assign('gmail', $row['Mail']);
				$this->m_tpl->assign('gtel', $row['Tel']);
				$this->m_tpl->assign('gfonction', $row['Fonction']);
				$this->m_tpl->assign('gniveau', $row['Niveau']);

				$this->m_tpl->assign('limitclub', $row['Limitation_equipe_club']);
				$this->m_tpl->assign('filtre_journee', $row['Filtre_journee']);
				$this->m_tpl->assign('filtre_competition_special', $row['Filtre_competition_sql']);

				$typeFiltreCompetition = $row['Type_filtre_competition'];
				$filtreSaison = $row['Filtre_saison'];
				$filtreCompetition = $row['Filtre_competition'];
				$filtreEvenement = $row['Id_Evenement'];

				$this->m_tpl->assign('filtreEvenement', $row['Id_Evenement']);
				$this->m_tpl->assign('Date_debut', $row['Date_debut']);
				$this->m_tpl->assign('Date_fin', $row['Date_fin']);
			}
		}

		$this->m_tpl->assign('arrayUser', $arrayUser);

		$emails .= 'contact@kayak-polo.info';
		$this->m_tpl->assign('emails', $emails);

		if ($selectUser == '') {
			$this->m_tpl->assign('action', utyGetPost('Action'));

			$this->m_tpl->assign('guser', utyGetPost('user'));
			$this->m_tpl->assign('gidentite', utyGetPost('identite'));
			$this->m_tpl->assign('gmail', utyGetPost('mail'));
			$this->m_tpl->assign('gtel', utyGetPost('tel'));
			$this->m_tpl->assign('gfonction', utyGetPost('fonction'));
			$this->m_tpl->assign('gniveau', utyGetPost('niveau'));

			$this->m_tpl->assign('limitclub', utyGetPost('limitclub'));
			$this->m_tpl->assign('filtre_journee', utyGetPost('filtre_journee'));
			$this->m_tpl->assign('filtre_competition_special', utyGetPost('filtre_competition_special'));

			$typeFiltreCompetition = utyGetPost('filtre_competition', $typeFiltreCompetition);
		}

		// Type de Filtre Compétitions ...
		$this->m_tpl->assign('typeFiltreCompetition', $typeFiltreCompetition);

		// Chargement des Saisons ...
		$sql = "SELECT Code 
			FROM kp_saison 
            WHERE Code > '1900' 
			ORDER BY Code DESC ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		$arraySaison = array();

		if ($selectUser == '') {
			$select = $this->IsSaisonSelectedPost('*');
		} else {
			$select = $this->IsStringSelected('*', $filtreSaison);
		}

		array_push($arraySaison, array('Code' => '*', 'Libelle' => '* - Toutes les Saisons', 'Selection' => $select));
		while ($row = $result->fetch()) {
			if ($selectUser == '') {
				$select = $this->IsSaisonSelectedPost($row["Code"]);
			} else {
				$select = $this->IsStringSelected($row["Code"], $filtreSaison);
			}

			array_push($arraySaison, array('Code' => $row["Code"], 'Libelle' => $row['Code'] . ' - Saison ' . $row['Code'], 'Selection' => $select));
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);

		// Chargement des Compétitions ...
		$sql = "SELECT DISTINCT c.Code, c.Libelle, c.Code_niveau, g.id, g.section, g.ordre 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_ref = g.Groupe 
			GROUP BY c.Code 
			ORDER BY g.section, c.Code ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		$arrayCompetition = array();

		$arrayCompetitionsSelected = explode('|', trim($filtreCompetition, '|'));
		if (in_array('*', $arrayCompetitionsSelected)) {
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$arrayCompetition[0]['label'] = "Toutes les compétitions";
		$arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'Toutes les compétitions', 'selected' => $selected);

		$i = 0;
		$j = '';
		$label = $myBdd->getSections();
		while ($row = $result->fetch()) {
			if ($j != $row['section']) {
				$i++;
				$arrayCompetition[$i]['label'] = $label[$row['section']];
			}
			if (in_array($row["Code"], $arrayCompetitionsSelected)) {
				$row['selected'] = 'selected';
			} else {
				$row['selected'] = '';
			}
			$j = $row['section'];
			$arrayCompetition[$i]['options'][] = $row;
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);

		// Chargement des évènements
		$sql = "SELECT * 
			FROM kp_evenement 
			ORDER BY Id DESC ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		$arrayEvenements = array();

		if ($selectUser == '') {
			$select = $this->IsEvenementSelectedPost('*');
		} else {
			$select = $this->IsStringSelected('*', $filtreEvenement);
		}

		while ($row = $result->fetch()) {
			if ($selectUser == '') {
				$select = $this->IsEvenementSelectedPost($row["Id"]);
			} else {
				$select = $this->IsStringSelected($row["Id"], $filtreEvenement);
			}

			array_push($arrayEvenements, array('Id' => $row["Id"], 'Libelle' => $row['Libelle'], 'Lieu' => $row["Lieu"], 'Selection' => $select));
		}
		$this->m_tpl->assign('arrayEvenements', $arrayEvenements);
	}

	function Replace($bNew)
	{
		$guser = utyGetPost('guser');
		$gpwd = utyGetPost('gpwd');
		$generepwd = utyGetPost('generepwd');
		if ($generepwd == 'O') {
			$gpwd = Genere_Password(10);
		}
		$gidentite = utyGetPost('gidentite');
		$gmail = utyGetPost('gmail');
		$gtel = utyGetPost('gtel');
		$gfonction = utyGetPost('gfonction');

		$gniveau = utyGetPost('gniveau', 100);
		if ($gniveau < utyGetSession('Profile')) {
			$gniveau = utyGetSession('Profile');
		}

		$typeFiltreCompetition = utyGetPost('filtre_competition', "1");

		$filtreCompetitionSql = '';
		$filtreCompetition = '';
		$filtreSaison = '';

		$filtreEvenement = $this->GetFiltreEvenement(); // COSANDCO 

		if ($typeFiltreCompetition == "2") { // Filtre Classique...

			$filtreSaison = $this->GetFiltreSaison();
			$filtreCompetition = $this->GetFiltreCompetition();
			// $filtreEvenement = $this->GetFiltreEvenement();// COSANDCO 

			if (strlen($filtreSaison) > 0) {
				$txt = substr($filtreSaison, 1);
				$txt = substr($txt, 0, -1);
				$txt = str_replace("|", "','", $txt);
				$filtreCompetitionSql .= " And a.Code_saison In ('" . $txt . "')";
			}

			if (strlen($filtreCompetition) > 0) {
				$txt = substr($filtreCompetition, 1);
				$txt = substr($txt, 0, -1);
				$txt = str_replace("|", "','", $txt);
				$filtreCompetitionSql .= " And a.Code In ('" . $txt . "')";
			}
		} elseif ($typeFiltreCompetition == "3") {	 // Filtre Spécial
			$filtreCompetitionSql = utyGetPost('filtre_competition_special');
		}

		$filtreJournee = utyGetPost('filtre_journee');
		$limitclub = utyGetPost('limitclub');

		$comboEvenement = utyGetPost('comboEvenement');
		$Date_debut = utyDateFrToUs(utyGetPost('Date_debut'));
		$Date_fin = utyDateFrToUs(utyGetPost('Date_fin'));
		$Date_debut = ($Date_debut != '') ? $Date_debut : null;
		$Date_fin = ($Date_fin != '') ? $Date_fin : null;

		$plusmail = utyGetPost('plusmail');
		$plusPJ = utyGetPost('plusPJ');
		$message_complementaire = utyGetPost('message_complementaire');
		if (strlen($guser) > 0) {
			$myBdd = new MyBdd();

			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				if ($bNew) {
					$sql = "SELECT Code 
						FROM kp_user 
						WHERE Code = ? ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array($guser));
					if ($result->rowCount() == 1) {
						return "Utilisateur déjà existant !";
					} else {
						$sql = "INSERT INTO kp_user 
							(Code, Identite, Mail, Tel, Fonction, Niveau, Pwd, Type_filtre_competition, 
							Filtre_competition, Filtre_saison, Filtre_competition_sql, Filtre_journee, 
							Limitation_equipe_club, Id_Evenement, Date_debut, Date_fin) 
							VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
						$result = $myBdd->pdo->prepare($sql);
						$result->execute(array(
							$guser, $gidentite, $gmail, $gtel, $gfonction, $gniveau, md5($gpwd),
							$typeFiltreCompetition, $filtreCompetition, $filtreSaison,
							$filtreCompetitionSql, $filtreJournee, $limitclub, $filtreEvenement,
							$Date_debut, $Date_fin
						));
						$action = "Création ";
					}
				} else {
					$arrayQuery = array(
						$gidentite, $gmail, $gtel, $gfonction, $gniveau,
						$typeFiltreCompetition, $filtreCompetition, $filtreSaison,
						$filtreCompetitionSql, $filtreJournee, $limitclub,
						$filtreEvenement, $Date_debut, $Date_fin
					);
					$sql = "UPDATE kp_user 
						SET Identite = ?, Mail = ?, Tel = ?, Fonction = ?, Niveau = ?,  
						Type_filtre_competition = ?, Filtre_competition = ?, Filtre_saison = ?, 
						Filtre_competition_sql = ?, Filtre_journee = ?, Limitation_equipe_club = ?, 
						Id_Evenement = ?, Date_debut = ?, Date_fin = ? ";
					if ($gpwd != '') {
						$sql .= ", Pwd = ? ";
						$arrayQuery = array_merge($arrayQuery, [md5($gpwd)]);
					}
					$sql .= "WHERE Code = ? ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array_merge($arrayQuery, [$guser]));
					$action = "Modification ";
				}

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout Modif Utilisateur, $guser" . '\r\n' . $e->getMessage());

				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}

			$myBdd->utyJournal('Ajout Modif Utilisateur', '', '', null, null, null, $guser);

			//MAIL 
			$sujet = $action . 'de votre accès à kayak-polo.info (KPI)';
			$email_expediteur = 'contact@kayak-polo.info';
			$email_reply = 'contact@kayak-polo.info';
			$message_texte  = 'Bonjour ' . $gidentite . ',' . "\n\n" . 'Nous vous confirmons la ' . $action . 'de votre accès à www.kayak-polo.info';
			$message_texte .= "\n\n" . 'Votre identifiant : ' . $guser;
			$message_texte .= "\n" . 'Votre mot de passe ';
			if ($gpwd != '') {
				$message_texte .= ' : ' . $gpwd;
			} else {
				$message_texte .= 'est inchangé';
			}
			$message_texte .= "\n" . 'Vos fonctions : ' . $gfonction;
			$message_texte .= "\n\n" . 'Connectez-vous sur https://www.kayak-polo.info onglet Administration.';
			$message_texte .= "\n" . 'Lors de votre prochaine connexion, n\'oubliez pas de changer votre mot de passe en cliquant sur Mes Paramètres.';
			if ($message_complementaire != '') {
				$message_texte .= "\n\n" . $message_complementaire;
			}
			$message_texte .= "\n\n" . 'A bientôt.';
			$message_texte .= "\n\n" . 'L\'équipe KPI.';
			$message_texte .= "\n" . '---------------------------------------';

			//GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
			$frontiere = '-----=' . md5(uniqid(mt_rand()));
			//HEADERS DU MAIL 
			$headers  = 'From: "KPI " <' . $email_expediteur . '>' . "\n";
			//$headers .= 'Bcc: '.$email_bcc."\n";
			$headers .= 'Return-Path: <' . $email_reply . '>' . "\n";
			$headers .= 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-Type: multipart/mixed; boundary="' . $frontiere . '"';
			//MESSAGE TEXTE 
			$message = 'This is a multi-part message in MIME format.' . "\n\n";
			$message .= '--' . $frontiere . "\n";
			$message .= 'Content-Type: text/plain; charset="UTF-8"' . "\n";
			$message .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
			//$message .= $message_texte."\n\n"; 
			//MESSAGE HTML 
			$message2 = '--' . $frontiere . "\n";
			$message2 .= 'Content-Type: text/html; charset="UTF-8"' . "\n";
			$message2 .= 'Content-Transfer-Encoding: 8bit' . "\n\n";
			//	$message2 .= $message_html."\n\n"; 
			$message2 .= '--' . $frontiere . "\n";
			//PIECE JOINTE
			$messagePJ = '';
			if ($plusPJ == 'Manuel7.pdf') {
				$messagePJ = 'Content-Type: application/pdf; name="Manuel7.pdf"' . "\n";
				$messagePJ .= 'Content-Transfer-Encoding: base64' . "\n";
				$messagePJ .= 'Content-Disposition:attachement; filename="../Manuel7.pdf"' . "\n\n";
				$messagePJ .= chunk_split(base64_encode(file_get_contents('../Manuel7.pdf'))) . "\n";
			}
			//ENVOI
			$messageComplet = $message . $message_texte . "\n\n" . $message2 . $messagePJ;
			if ($plusmail == 'O') {
				mail($gmail, $sujet, $messageComplet, $headers);
			}

			// MAIL ADMINISTRATEUR
			$sujet = $action . 'accès KPI : ' . $gidentite . ' (par ' . ucwords(strtolower(utyGetSession('userName'))) . ')';
			$message_texte  = 'Bonjour, ' . "\n\n" . 'Nous vous confirmons la ' . $action . 'd\'un accès à www.kayak-polo.info.';
			$message_texte .= "\n\n" . 'Identité : ' . $gidentite;
			$message_texte .= "\n" . 'Email : ' . $gmail;
			$message_texte .= "\n" . 'Tel : ' . $gtel;
			$message_texte .= "\n" . 'Identifiant : ' . $guser;
			$message_texte .= "\n" . 'Pwd : ' . $gpwd;
			$message_texte .= "\n" . 'Fonctions : ' . $gfonction;
			$message_texte .= "\n" . 'Profil : ' . $gniveau;
			$message_texte .= "\n" . 'Clubs : ' . $limitclub;
			$message_texte .= "\n" . 'Journées : ' . $filtreJournee;
			$message_texte .= "\n" . 'Evénements : ' . implode(',', $comboEvenement);
			$message_texte .= "\n" . 'Saisons : ' . $filtreSaison;
			$message_texte .= "\n" . 'Competitions : ' . $filtreCompetition;
			$message_texte .= "\n" . 'Message complémentaire : ' . $message_complementaire;
			$message_texte .= "\n" . 'Envoi email : ' . $plusmail;
			$message_texte .= "\n" . 'Envoi pièce jointe : ' . $plusPJ;
			$message_texte .= "\n\n" . 'Modification effectuée par : ' . ucwords(strtolower(utyGetSession('userName')));
			$message_texte .= "\n\n" . 'A bientôt.';
			$message_texte .= "\n\n" . 'L\'équipe KPI.';
			$message_texte .= "\n" . '---------------------------------------';
			$email_admin = 'contact@kayak-polo.info';
			//ENVOI
			$messageComplet = $message . $message_texte;
			mail($email_admin, $sujet, $messageComplet, $headers);

			return "Utilisateur mis à jour.";
		}
	}

	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');

		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0) {
			return;
		} // Rien à Detruire ...

		$myBdd = new MyBdd();

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$in = str_repeat('?,', count($arrayParam) - 1) . '?';
			$sql = "DELETE FROM kp_user 
				WHERE Code IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression utilisateur, $ParamCmd" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		for ($i = 0; $i < count($arrayParam); $i++) {
			$myBdd->utyJournal('Suppression utilisateur', '', '', null, null, null, $arrayParam[$i]);
		}
		$sql .= "')";
	}

	function __construct()
	{
		parent::__construct(3);

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		$ParamCmd = utyGetPost('ParamCmd');

		$selectUser = '';
		if ($Cmd == 'Edit') {
			$selectUser = utyGetPost('ParamCmd');
			$Cmd = '';
		}

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Replace(true) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Update') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Replace(false) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Remove') {
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_utilisateurs", "Utilisateurs", false);
		$this->Load($selectUser);
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionUtilisateur');
	}
}

$page = new GestionUtilisateur();
