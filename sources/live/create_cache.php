<?php
include_once('base.php');
include_once('create_cache_match.php');

function CreateCache($bCache)
{
	if ($bCache)
		ob_start();
		
	$equipe1 = array(	'nom' => 'France' , 
						'competiteurs' => array( array('nom' => 'Dupont', 'prenom' => 'Pierre', 'type' => 'Capitaine'),
												array('nom' => 'Durand', 'prenom' => 'Paul', 'type' => 'Titulaire'),
												array('nom' => 'Dugenoux', 'prenom' => 'Henri', 'type' => 'Remplaçant'))
					);

	$equipe2 = array(	'nom' => 'Allemagne' , 
						'competiteurs' => array( array('nom' => 'Maier', 'prenom' => 'Hans', 'type' => 'Capitaine'),
												array('nom' => 'Schumacher', 'prenom' => 'Pieter', 'type' => 'Titulaire'),
												array('nom' => 'Lagerfeld', 'prenom' => 'Helmuth', 'type' => 'Remplaçant'))
					);

					
	$arrayCache = array(	'tick' => 1234, 
							'match' => array('id' => 4321, 'nom' => 'Quart de Finale Poule B'),
							'arbitre' => array( 'nom' => 'Van-Morgen', 'prenom' => 'Maurice', 'nation' => 'BEL'),
							'equipe1' => $equipe1,
							'equipe2' => $equipe2,
							'score'	=> array(4, 0),	
							'temps_ecoule' => 480,		// Temps de Match (en sec) déja écoulé depuis les Stop antérieurs 
							'temps_reprise'	=> 40500,	// Temps (en sec) ou l'on a redémarré le chrono après une pause 
							'etat' => 'E'		// X = Pas débuté, E = En cours, S = match Stoppé, F = match Fini
						);
							
	echo json_encode($arrayCache);

	if ($bCache)
	{
		$in = array("è", "é", "ê", "ç", "ô", "î", "â", "à","È", "É", "Ê", "Ç", "Ô", "Î", "Â", "À", "Ï", "Ä", "Ë", "Ö", "Ü");  
		$out = array("&egrave;","&eacute;","&ecric;","&ccedil;","&ocirc;","&icirc;","&acirc;","&agrave;","&Egrave;","&Eacute;","&Ecric;","&Ccedil;","&Ocirc;","&Icirc;","&Acirc;","&Agrave;","&Iuml;","&Auml;","&Euml;", "&Ouml;", "&Uuml;");
		file_put_contents($_SERVER['DOCUMENT_ROOT']."/live/cache/live_score.txt", str_replace($in,$out,ob_get_contents()."@@END@@"));
		ob_end_clean();
	}
}

//CreateCache(true);

$match = new CacheMatch($_GET);
$db = new MyBdd();

$listMatch = array();
array_push($listMatch, 1500);
array_push($listMatch, 3772279);
//$match->Matchs($listMatch);

$match->Match($db, 1500);
$match->Match($db, 3772298);

//$match->Terrain(1, 3772279);
