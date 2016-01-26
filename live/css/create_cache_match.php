<?php
class CacheMatch
{
	var $m_arrayParams;		// Tableau des Paramètres
	var $m_bCache;
    
    // Constructeur ...
    function __construct(&$arrayParams)
    {
        $this->m_arrayParams = &$arrayParams;
		if ($this->GetParam('cache') == '0')
			$this->m_bCache = false;
		else
			$this->m_bCache = true;
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
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/live/cache/$fileName", str_replace($in,$out,ob_get_contents()."@@END@@"));
			ob_end_clean();
		}
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
		$db->LoadRecord("Select * from gickp_Competitions Where Code = '".$rJournee['Code_competition']."' And Code_saison = '".$rJournee['Code_saison']."'", $rCompetitions);
		
		$idEquipeA =  $rMatch['Id_equipeA'];
		$idEquipeB =  $rMatch['Id_equipeB'];
		
		// Chargement Equipe A 
		$rEquipeA = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeA", $rEquipeA);
		
		// Chargement Equipe B 
		$rEquipeB = null;
		$db->LoadRecord("Select * from gickp_Competitions_Equipes Where Id = $idEquipeB", $rEquipeB);

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

		// json ...
		$arrayCache = array(
							'id_match' => $idMatch,
							'tick' => uniqid(), 
							'competition' => $rCompetition['Soustitre2'],
							'journee' => $rJournee['Nom'],
							'phase' => $rJournee['Phase'],
							'terrain' => $rMatch['Terrain'],
							'numero_ordre' => $rMatch['Numero_ordre'],
							'validation' => $rMatch['Validation'],
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
		$cmd  = "Select * ";
		$cmd .= "From gickp_Matchs_Detail ";
		$cmd .= "Where Id_match = $idMatch ";
		$cmd .= "Order By Numero ";

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
		
		if ( (!isset($rChrono["IdMatch"])) || ($rChrono["IdMatch"] != $idMatch))
		{
			$rChrono['IdMatch'] = $idMatch;
			$rChrono['action'] = 'stop';
			$rChrono['run_time'] = 0;
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
}	

?>