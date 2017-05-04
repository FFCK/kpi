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
	
	function __destruct ()
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
	
    function GetParam($key, $defaultValue='')
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
		if ($this->m_bCache)
		{
			$in = array("è", "é", "ê", "ç", "ô", "î", "â", "à","È", "É", "Ê", "Ç", "Ô", "Î", "Â", "À", "Ï", "Ä", "Ë", "Ö", "Ü");  
			$out = array("&egrave;","&eacute;","&ecric;","&ccedil;","&ocirc;","&icirc;","&acirc;","&agrave;","&Egrave;","&Eacute;","&Ecric;","&Ccedil;","&Ocirc;","&Icirc;","&Acirc;","&Agrave;","&Iuml;","&Auml;","&Euml;", "&Ouml;", "&Uuml;");
			$content = str_replace($in, $out, ob_get_contents() . "@@END@@");
            if($this->m_bFTP) {
                // C'est pas du FTP !!!
                file_put_contents($_SERVER['DOCUMENT_ROOT']."/live/cache/$fileName", $content);
            } else {
                if(!file_put_contents(dirname(__FILE__) . "/cache/$fileName", $content)) {
                    $error = "Ecriture échouée :";
                }
            }
            
			ob_end_clean();
            if(isset($error)) {
                echo $error;
            }
/*
			if ($this->m_bFTP)
			{
				$fp = fopen($_SERVER['DOCUMENT_ROOT']."/live/cache/$fileName", 'r');
				if ($fp == false)
					echo "FOPENNNNNNNNNNNNNNNNNNNN";
				else
					echo "FOPEN OK";
				
				if (!ftp_fput($this->m_idFTP, $fileName, $fp, FTP_BINARY)) 
				{
					echo "There was a problem while uploading $fileName\n";
					exit;
				}
				fclose($fp);
			}
*/
		}
	}
	
	function Pitch($idEvent, $pitch, $idMatch)
	{
		$this->StartCache();

		$arrayCache = array('id_match' => $idMatch, 'pitch' => $pitch);
		echo json_encode($arrayCache);
		$this->EndCache('event'.$idEvent.'_pitch'.$pitch.'.json');
	}

	function Match(&$db, $idMatch)
	{
		$this->MatchGlobal($db, $idMatch);
		$this->MatchScore($db, $idMatch);
		$this->MatchChrono($db, $idMatch);
	}
	
	function MatchGlobal(&$db, $idMatch)
	{
		$this->StartCache();
		
		// Chargement Record Match ...
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);

		// Chargement Record Journée ...
		$rJournee = null;
		$db->LoadRecord("Select * from gickp_Journees Where Id = ".$rMatch['Id_journee'], $rJournee);

		// Chargement Record Compétition ...
		$rCompetition = null;
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetition);
		
		$idEquipeA =  $rMatch['Id_equipeA'];
		$idEquipeB =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe A 
		$rEquipeA = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeA", $rEquipeA);
		
		// Chargement Equipe B 
		$rEquipeB = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeB", $rEquipeB);

/*		
		// Chargement Joueurs Equipe A 
		$cmd  = "Select a.matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = 'A' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "Order By a.Numero ";

		$tJoueursA = null;
		$db->LoadTable($cmd, $tJoueursA);
		
		// Chargement Joueurs Equipe B 
		$cmd  = "Select a.matric, a.Numero, a.Capitaine, b.Nom, b.Prenom, b.Sexe, b.Naissance ";
		$cmd .= "From gickp_Matchs_Joueurs a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Equipe = 'B' ";
		$cmd .= "And a.Matric = b.matric ";
		$cmd .= "Order By a.Numero ";

		$tJoueursB = null;
		$db->LoadTable($cmd, $tJoueursB);
*/

		$tJoueursA = '';
		$tJoueursB = '';

		// json ...
		$arrayCache = array(
							'id_match' => $idMatch,
							'tick' => uniqid(), 
							'categ' => $rCompetition['Soustitre2'],
							'journee' => $rJournee['Nom'],
							'phase' => $rJournee['Phase'],
							'terrain' => $rMatch['Terrain'],
							'date' => $rMatch['Date_match'],
							'numero_ordre' => $rMatch['Numero_ordre'],
							'validation' => $rMatch['Validation'],
							'statut' => $rMatch['Statut'],
							'arbitre' => $rMatch['Arbitre_principal'],
							'arbitre_secondaire' => $rMatch['Arbitre_secondaire'],
							'equipe1' => array( 'id' => $idEquipeA, 'nom' => $rEquipeA['Libelle'], 'club' => $rEquipeA['Code_club'], 'joueurs' => $tJoueursA),
							'equipe2' => array( 'id' => $idEquipeB, 'nom' => $rEquipeB['Libelle'], 'club' => $rEquipeB['Code_club'], 'joueurs' => $tJoueursB)
						);
		
		echo json_encode($arrayCache);
		$this->EndCache($idMatch.'_match_global.json');
	}
	
	// Score , Cartons ...
	function MatchScore(&$db, $idMatch)
	{
		$this->StartCache();
		
		// Chargement Record Match ...
		$rMatch = null;
		$db->LoadRecord("Select * from gickp_Matchs Where Id = $idMatch", $rMatch);
		
		// Chargement gickp_Matchs_Detail 
		$cmd  = "Select a.*, b.Nom, b.Prenom  ";
		$cmd .= "From gickp_Matchs_Detail a, gickp_Liste_Coureur b ";
		$cmd .= "Where a.Id_match = $idMatch ";
		$cmd .= "And a.Competiteur = b.Matric ";
		$cmd .= "Order By Id Desc ";
		$cmd .= "Limit 5 ";
		
		$tMatchDetails = null;
		$db->LoadTable($cmd, $tMatchDetails);
		
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
		
		$this->EndCache($idMatch.'_match_score.json');
	}

	// Gestion du Temps et de l'Etat du Match ...
	function MatchChrono(&$db, $idMatch)
	{
		$this->StartCache();
		
		$rChrono = null;
		$db->LoadRecord("Select * from gickp_Chrono Where IdMatch =  $idMatch", $rChrono);
		
		if (!isset($rChrono['IdMatch']))
		{
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
	
		$this->EndCache($idMatch.'_match_chrono.json');
	}
	
	// Liste des Matchs actifs ...
	function Matchs($list)
	{
		$this->StartCache();
	
		// json ...
		echo json_encode($list);
	
		$this->EndCache('matchs.json');
	}
	
	function Event(&$db, $idEvent, $dateMatch, $hourMatch, $arrayPitchs=null)
	{
		// Chargement de tous les Matchs de l'évenement pour la date indiquée et les terrains concernés ...
		$cmd  = "SELECT a.* ";
		$cmd .= "FROM gickp_Matchs a, gickp_Journees b, gickp_Evenement_Journees c ";
		$cmd .= "WHERE a.Id_journee = b.Id ";
		$cmd .= "And b.Id = c.Id_journee ";
		$cmd .= "And c.Id_evenement = $idEvent ";
		$cmd .= "And a.Date_match = '$dateMatch' ";

		if ($arrayPitchs != null)
		{
			if (count($arrayPitchs) > 0)
			{
				$cmd .= 'And a.Terrain In ('.implode(',', $arrayPitchs).') ';
			}
		}

		$cmd .= "Order By a.Heure_match, a.Terrain ";
		
		$tMatchs = null;
		$db->LoadTable($cmd, $tMatchs);
		
		// Prise des Terrains ...
		$arrayPitch = array();
		for ($i=0;$i<count($tMatchs);$i++)
		{
			$pitch = $tMatchs[$i]['Terrain'];
			$bNew = true;
			for ($j=0;$j<count($arrayPitch);$j++)
			{
				if ($arrayPitch[$j] == $pitch)
				{
					$bNew = false;
					break;
				}
			}
			if ($bNew) 
				array_push($arrayPitch, $pitch);
		}
		
		// Génération des fichiers 
		$time = utyHHMM_To_MM($hourMatch);
		for ($i=0;$i<count($arrayPitch);$i++)
		{
			$idMatch = $this->GetBestMatch($tMatchs, $arrayPitch[$i], $time);
			if ($idMatch >= 0)
			{
				$this->Pitch($idEvent, $arrayPitch[$i], $idMatch);
			}
		}
	}
	
	function GetBestMatch(&$tMatchs, $pitch, $time)
	{
		$timeBest = 0;
		$idBest = -1;
		for ($i=0;$i<count($tMatchs);$i++)
		{
			if ($tMatchs[$i]['Terrain'] != $pitch) 
				continue;
			
			$timeMatch = utyHHMM_To_MM($tMatchs[$i]['Heure_match']);
			if ($timeMatch <= $time)
			{
				if ($idBest == -1)
				{
					$idBest = $i;
					$timeBest = $timeMatch;
				}
				else
				{
					if ($timeBest < $timeMatch)
					{
						$idBest = $i;
						$timeBest = $timeMatch;
					}
				}
			}
		}
		
		if ($idBest == -1)
			return -1;
		else
			return $tMatchs[$idBest]['Id'];
	}
}	
