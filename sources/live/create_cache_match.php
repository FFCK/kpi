<?php

class CacheMatch
{
	var $m_arrayParams;		// Tableau des Paramètres
	var $m_bCache;
	var $m_bFTP;
	var $m_idFTP;

	// Constructeur ...
	function __construct(&$arrayParams)
	{
		$this->m_arrayParams = &$arrayParams;
		if ($this->GetParam('cache') == '0')
			$this->m_bCache = false;
		else
			$this->m_bCache = true;

		$this->m_bFTP = false; // fopen !
		if ($this->m_bFTP)
			$this->InitFTP();
	}

	function __destruct()
	{
		if ($this->m_bFTP)
			ftp_close($this->m_idFTP);
	}

	function InitFTP()
	{
		$ftp_server = FTP_SERVER;
		$ftp_user_name = FTP_USER_NAME;
		$ftp_user_pass = FTP_USER_PASS;

		// set up basic connection
		$this->m_idFTP = ftp_connect($ftp_server);

		// login with username and password
		$login_result = ftp_login($this->m_idFTP, $ftp_user_name, $ftp_user_pass);

		// Vérification de la connexion
		if ((!$this->m_idFTP) || (!$login_result)) {
			die("Echec de la connexion FTP !");
		}

		ftp_chdir($this->m_idFTP, "live/cache");
		//		echo "Dossier courant : " . ftp_pwd($this->m_idFTP) . "\n";
	}

	function GetParam($key, $defaultValue = '')
	{
		if (isset($this->m_arrayParams[$key]))
			return $this->m_arrayParams[$key];
		else
			return $defaultValue;
	}

	function StartCache()
	{
		if ($this->m_bCache)
			ob_start();
	}

	function EndCache($fileName)
	{
		if ($this->m_bCache) {
			$in = array("è", "é", "ê", "ç", "ô", "î", "â", "à", "È", "É", "Ê", "Ç", "Ô", "Î", "Â", "À", "Ï", "Ä", "Ë", "Ö", "Ü");
			$out = array("&egrave;", "&eacute;", "&ecric;", "&ccedil;", "&ocirc;", "&icirc;", "&acirc;", "&agrave;", "&Egrave;", "&Eacute;", "&Ecric;", "&Ccedil;", "&Ocirc;", "&Icirc;", "&Acirc;", "&Agrave;", "&Iuml;", "&Auml;", "&Euml;", "&Ouml;", "&Uuml;");
			// $content = str_replace($in, $out, ob_get_contents() . "@@END@@");
			$content = str_replace($in, $out, ob_get_contents());
			if ($this->m_bFTP) {
				// C'est pas du FTP !!!
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/live/cache/$fileName", $content);
			} else {
				if (!file_put_contents(dirname(__FILE__) . "/cache/$fileName", $content)) {
					$error = "Ecriture échouée :";
				}
			}

			ob_end_clean();
			if (isset($error)) {
				echo $error;
			}
		}
	}

	function Pitch($idEvent, $pitch, $idMatch, $idNext = -1)
	{
		$arrayCache = array('id_match' => $idMatch, 'pitch' => $pitch, 'id_next' => $idNext);

		//Nouveau format
		$this->StartCache();
		echo json_encode($arrayCache);
		$this->EndCache('event' . $idEvent . '_pitch' . $pitch . '.json');

		// Ancien format
		// $this->StartCache();
		// echo json_encode($arrayCache);
		// $this->EndCache($pitch.'_terrain.json');
	}

	function Match(&$db, $idMatch)
	{
		$this->MatchGlobal($db, $idMatch);
		$this->MatchScore($db, $idMatch);
		$this->MatchChrono($db, $idMatch);
		return true;
	}

	function MatchGlobal(&$db, $idMatch)
	{
		$this->StartCache();

		// Chargement Record Match ...
		$rMatch = null;
		$sql = "SELECT * 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$rMatch = $result->fetch();

		// Chargement Record Journée ...
		$rJournee = null;
		$sql = "SELECT * 
			FROM kp_journee 
			WHERE Id = ? ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($rMatch['Id_journee']));
		$rJournee = $result->fetch();

		// Chargement Record Compétition ...
		$rCompetition = null;
		$sql = "SELECT * 
			FROM kp_competition 
			WHERE Code = ? 
			AND Code_saison = ? ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($rJournee['Code_competition'], $rJournee['Code_saison']));
		$rCompetition = $result->fetch();

		$idEquipeA =  $rMatch['Id_equipeA'];
		$idEquipeB =  $rMatch['Id_equipeB'];

		$rEquipeA = null;
		$rEquipeB = null;
		$tJoueursA = null;
		$tJoueursB = null;

		if ($idEquipeA > 0) {
			// Chargement Equipe A 
			$sql = "SELECT * 
				FROM kp_competition_equipe 
				WHERE Id = ? ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array($idEquipeA));
			$rEquipeA = $result->fetch();

			// Chargement Joueurs Equipe A 
			$sql = "SELECT a.matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance 
				FROM kp_match_joueur a, kp_licence b 
				WHERE a.Id_match = ? 
				AND a.Equipe = ? 
				AND a.Matric = b.matric 
				ORDER BY a.Numero ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array($idMatch, 'A'));
			$tJoueursA = $result->fetchAll(PDO::FETCH_ASSOC);
		}

		if ($idEquipeB > 0) {
			// Chargement Equipe B 
			$sql = "SELECT * 
				FROM kp_competition_equipe 
				WHERE Id = ? ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array($idEquipeB));
			$rEquipeB = $result->fetch();

			// Chargement Joueurs Equipe B 
			$sql = "SELECT a.matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance 
				FROM kp_match_joueur a, kp_licence b 
				WHERE a.Id_match = ? 
				AND a.Equipe = ? 
				AND a.Matric = b.matric 
				ORDER BY a.Numero ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array($idMatch, 'B'));
			$tJoueursB = $result->fetchAll(PDO::FETCH_ASSOC);
		}

		// json ...
		$arrayCache = array(
			'id_match' => $idMatch,
			'tick' => uniqid(),
			'categ' => $rCompetition['Soustitre2'],
			'journee' => $rJournee['Nom'],
			'phase' => $rJournee['Phase'],
			'terrain' => $rMatch['Terrain'],
			'date' => $rMatch['Date_match'],
			'heure' => $rMatch['Heure_match'],
			'numero_ordre' => $rMatch['Numero_ordre'],
			'validation' => $rMatch['Validation'],
			'statut' => $rMatch['Statut'],
			'arbitre' => $rMatch['Arbitre_principal'],
			'arbitre_secondaire' => $rMatch['Arbitre_secondaire'],
			'equipe1' => array(
				'id' => $idEquipeA, 'nom' => $rEquipeA['Libelle'],
				'club' => $rEquipeA['Code_club'], 'joueurs' => $tJoueursA
			),
			'equipe2' => array(
				'id' => $idEquipeB, 'nom' => $rEquipeB['Libelle'],
				'club' => $rEquipeB['Code_club'], 'joueurs' => $tJoueursB
			)
		);

		echo json_encode($arrayCache);
		$this->EndCache($idMatch . '_match_global.json');
	}

	// Score , Cartons ...
	function MatchScore(&$db, $idMatch)
	{
		$this->StartCache();

		// Chargement Record Match ...
		$rMatch = null;
		$sql = "SELECT * 
			FROM kp_match 
			WHERE Id = ? ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$rMatch = $result->fetch();

		// Chargement kp_match_detail 
		$tMatchDetails = null;
		$sql = "SELECT md.*, l.Nom, l.Prenom, mj.Capitaine 
			FROM kp_match_detail md 
			LEFT OUTER JOIN kp_licence l ON (md.Competiteur = l.Matric) 
			LEFT OUTER JOIN kp_match_joueur mj
				ON (md.Competiteur = mj.Matric AND md.Id_match = mj.Id_match) 
			WHERE md.Id_match = ? 
			ORDER BY md.Periode DESC, md.Temps ASC, md.Id_evt_match DESC ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$tMatchDetails = $result->fetchAll(PDO::FETCH_ASSOC);

		// json ...
		$arrayCache = array(
			'id_match' => $idMatch,
			'tick' => uniqid(),
			'periode' => $rMatch['Periode'],
			'score1' => $rMatch['ScoreDetailA'],
			'score2' => $rMatch['ScoreDetailB'],
			'event' => $tMatchDetails
		);

		echo json_encode($arrayCache);

		$this->EndCache($idMatch . '_match_score.json');
	}

	// Gestion du Temps et de l'Etat du Match ...
	function MatchChrono(&$db, $idMatch)
	{
		$this->StartCache();

		$rChrono = null;
		$sql = "SELECT * 
			FROM kp_chrono 
			WHERE IdMatch = ? ";
		$result = $db->pdo->prepare($sql);
		$result->execute(array($idMatch));
		$rChrono = $result->fetch();

		if (!isset($rChrono['IdMatch'])) {
			$rChrono['IdMatch'] = $idMatch;
			$rChrono['action'] = 'stop';
			$rChrono['run_time'] = 600000;
			$rChrono['max_time'] = '10:00';
			$rChrono['start_time_server'] = 0;
			$rChrono['raz'] = 1;
		}

		$rChrono['tick'] = uniqid();

		// json ...
		echo json_encode($rChrono);

		$this->EndCache($idMatch . '_match_chrono.json');
	}

	// Liste des Matchs actifs ...
	function Matchs($list)
	{
		$this->StartCache();

		// json ...
		echo json_encode($list);

		$this->EndCache('matchs.json');
	}

	function Event(&$db, $idEvent, $dateMatch, $hourMatch, $realTime, $arrayPitchs = null)
	{
		// Chargement de tous les Matchs de l'évenement pour la date indiquée et les terrains concernés ...
		$tMatchs = null;
		if ($arrayPitchs != null && count($arrayPitchs) > 0) {
			$in  = str_repeat('?,', count($arrayPitchs) - 1) . '?';
			$sql = "SELECT a.* 
				FROM kp_match a, kp_journee b, kp_evenement_journee c 
				WHERE a.Id_journee = b.Id 
				AND b.Id = c.Id_journee 
				AND c.Id_evenement = ? 
				AND a.Date_match = ? 
				AND a.Publication = 'O' 
				AND a.Terrain IN ($in) 
				ORDER BY a.Heure_match, a.Terrain ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array_merge([$idEvent], [$dateMatch], $arrayPitchs));
		} else {
			$sql = "SELECT a.* 
				FROM kp_match a, kp_journee b, kp_evenement_journee c 
				WHERE a.Id_journee = b.Id 
				AND b.Id = c.Id_journee 
				AND c.Id_evenement = ? 
				AND a.Date_match = ? 
				AND a.Publication = 'O' 
				ORDER BY a.Heure_match, a.Terrain ";
			$result = $db->pdo->prepare($sql);
			$result->execute(array($idEvent, $dateMatch));
		}
		$tMatchs = $result->fetchAll(PDO::FETCH_ASSOC);
		// Prise des Terrains ...
		$arrayPitch = array();
		foreach ($tMatchs as $tMatch) {
			$pitch = $tMatch['Terrain'];
			array_push($arrayPitch, $pitch);
		}
		$arrayPitch = array_unique($arrayPitch);

		// Génération des fichiers 
		$time = utyHHMM_To_MM($hourMatch);
		$realTime = utyHHMM_To_MM($realTime);
		$arrayResult = [];
		foreach ($arrayPitch as $pitch) {
			$match = $this->GetBestMatch($tMatchs, $pitch, $time);
			$nextTime = $match['id'] > 0 ? utyHHMM_To_MM($match['time']) : $realTime;
			$next = $this->GetNextMatch($db, $tMatchs, $pitch, $nextTime);
			$this->Pitch($idEvent, $pitch, $match['id'], $next);
			$arrayResult[] = [
				'pitch' => $pitch,
				'game' => $match['id'],
				'num' => $match['num'],
				'next' => $next
			];
			// }
		}
		return $arrayResult;
	}

	function GetBestMatch(&$tMatchs, $pitch, $time)
	{
		$timeBest = 0;
		$idBest = -1;
		for ($i = 0; $i < count($tMatchs); $i++) {
			if ($tMatchs[$i]['Terrain'] != $pitch || $tMatchs[$i]['Statut'] === 'ATT')
				continue;

			$timeMatch = utyHHMM_To_MM($tMatchs[$i]['Heure_match']);
			if ($timeMatch <= $time) {
				if ($idBest == -1) {
					$idBest = $i;
					$timeBest = $timeMatch;
				} else {
					if ($timeBest < $timeMatch) {
						$idBest = $i;
						$timeBest = $timeMatch;
					}
				}
			}
		}

		if ($idBest == -1)
			return ['id' => null, 'time' => null, 'num' => null];
		else
			return [
				'id' => $tMatchs[$idBest]['Id'],
				'time' => $tMatchs[$idBest]['Heure_match'],
				'num' => $tMatchs[$idBest]['Numero_ordre']
			];
	}

	function GetNextMatch(&$db, &$tMatchs, $pitch, $time)
	{
		$idNext = -1;
		for ($i = 0; $i < count($tMatchs); $i++) {
			if ($tMatchs[$i]['Terrain'] != $pitch || $tMatchs[$i]['Statut'] != 'ATT')
				continue;

			$timeMatch = utyHHMM_To_MM($tMatchs[$i]['Heure_match']);
			if ($idNext == -1 && $timeMatch > $time) {
				$idNext = $i;
			}
		}

		if ($idNext == -1) {
			return ['id' => null, 'time' => null, 'num' => null];
		} else {
			if (!is_file(dirname(__FILE__) . '/cache/' . $tMatchs[$idNext]['Id'] . '_match_global.json')) {
				$this->MatchGlobal($db, $tMatchs[$idNext]['Id']);
			}
			return [
				'id' => $tMatchs[$idNext]['Id'],
				'time' => $tMatchs[$idNext]['Heure_match'],
				'num' => $tMatchs[$idNext]['Numero_ordre']
			];
		}
	}
}
