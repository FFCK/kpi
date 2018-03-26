<?php
include_once('MyBdd.php'); 

// MyTools.php

/**
 * Redimentionne image pour PDF
 * 
 * @param type $image
 * @param type $hauteur hauteur de l'image redimentionnée
 * @param type $largeurPage
 * @param type $position (L, C, R)
 */
function redimImage($image, $largeurPage, $marge, $newHauteur, $position='C') {
    $size = getimagesize($image);
    $largeurActuelle = $size[0];
    $hauteurActuelle = $size[1];
    $ratio = $newHauteur / $hauteurActuelle;
    $newLargeur = $largeurActuelle * $ratio;
    switch ($position) {
        case 'L':
            $positionX = $marge;
            break;
        case 'R':
            $positionX = $largeurPage - $marge - $newLargeur;
            break;
        default:
            $positionX = ($largeurPage - $newLargeur) / 2;
            break;
    }
    
    return array('image' => $image, 'positionX' => $positionX, 'newHauteur' => $newHauteur);
}

/**
 * 
 * @param type array $recordCompetition
 */
function utyGetVisuels($recordCompetition, $admin = FALSE) {
    $result = [];
    if($admin) {
        $rel = '../';
    } else {
        $rel = '';
    }
    if($recordCompetition['BandeauLink'] != '' && strpos($recordCompetition['BandeauLink'], 'http') === FALSE ){
        $recordCompetition['BandeauLink'] = $rel . 'img/logo/' . $recordCompetition['BandeauLink'];
        if(is_file($recordCompetition['BandeauLink'])) {
            $result['bandeau'] = $recordCompetition['BandeauLink'];
        }
    } elseif($recordCompetition['BandeauLink'] != '') {
        $result['bandeau'] = $recordCompetition['BandeauLink'];
    }
    if($recordCompetition['LogoLink'] != '' && strpos($recordCompetition['LogoLink'], 'http') === FALSE ){
        $recordCompetition['LogoLink'] = $rel . 'img/logo/' . $recordCompetition['LogoLink'];
        if(is_file($recordCompetition['LogoLink'])) {
            $result['logo'] = $recordCompetition['LogoLink'];
        }
    } elseif($recordCompetition['LogoLink'] != '') {
        $result['logo'] = $recordCompetition['LogoLink'];
    }
    if($recordCompetition['SponsorLink'] != '' && strpos($recordCompetition['SponsorLink'], 'http') === FALSE ){
        $recordCompetition['SponsorLink'] = $rel . 'img/logo/' . $recordCompetition['SponsorLink'];
        if(is_file($recordCompetition['SponsorLink'])) {
            $result['sponsor'] = $recordCompetition['SponsorLink'];
        }
    } elseif($recordCompetition['LogoLink'] != '') {
        $result['sponsor'] = $recordCompetition['SponsorLink'];
    }
    return $result;
}

// Transformation Date Us : YYYY-MM-DD en Date Fr Long : dddd DD mmmm YYYY
function utyDateUsToFrLong($dateUs, $separator = "-")
{
	$tab_dmy = explode($separator, $dateUs);
	$prefix = "";
	$tab_month = array(0, "janvier", "fevrier", "mars", "avril", "mai", "juin", "juillet", "aout", "septembre", "octobre", "novembre", "decembre");
	$tab_day = array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");
	settype($tab_dmy[1], 'integer');
	$day = date("w", mktime(0, 0, 0, $tab_dmy[1], $tab_dmy[2], $tab_dmy[0]));
	$ladate = $prefix . $tab_day[$day] . " " . $tab_dmy[2] . " ";
	$ladate .= $tab_month[$tab_dmy[1]] . " " . $tab_dmy[0] . " ";
	
	return $ladate; // Error ...
}

// Transformation Date Us : YYYY-MM-DD en Date Fr : DD/MM/YYYY

function utyDateUsToFr($dateUs, $separaror = '-')
{
	$data = explode($separaror,$dateUs);
	if (count($data) == 3)
		return $data[2].'/'.$data[1].'/'.$data[0];
		
	return $dateUs; // Error ...
}

// Transformation Date Fr : DD/MM/YYYY en Date Us : YYYY-MM-DD

function utyDateFrToUs($dateFr, $separaror = '/')
{
	$data = explode($separaror,$dateFr);
	if (count($data) == 3)
		return $data[2].'-'.$data[1].'-'.$data[0];
		
	return $dateFr; // Error ...
}

// utyYearOfDate 

function utyYearOfDate($dateUs)
{
	$data = explode('-',$dateUs);
	if (count($data) == 3)
		return (int) $data[0];
	
	return 0;	// Error
}

// utyDateCmpFr 

function utyDateCmpFr($date1, $date2)
{
	$data1 = explode('/',$date1);
	$data2 = explode('/',$date2);
	
	// Comparaison Annee
	if ( (int) $data1[2] != (int) $data2[2] )
		return (int) $data1[2] - (int) $data2[2];
		
	// Comparaison Mois
	if ( (int) $data1[1] != (int) $data2[1] )
		return (int) $data1[1] - (int) $data2[1];

	// Comparaison Jour
	return (int) $data1[0] - (int) $data2[0];
}

// utyCodeCategorie
/*
function utyCodeCategorie($dateNaissance)
{
	$age = (int) utyGetSaison() - utyYearOfDate($dateNaissance);
	$code = '';
	$libelle = '';
	$myBdd = new MyBdd();
	$myBdd->GetCategorie($age, $code, $libelle);	
	return $code;
}
*/
// utyCodeCategorie 2 (sans requête)

function utyCodeCategorie2($dateNaissance, $saison = '')
{
	if($saison == '')
		$saison = utyGetSaison();
	$age = (int) $saison - utyYearOfDate($dateNaissance);
	switch($age){
		case 9:
		case 10: $cat = 'POU'; break;
		case 11:
		case 12: $cat = 'BEN'; break;
		case 13:
		case 14: $cat = 'MIN'; break;
		case 15:
		case 16: $cat = 'CAD'; break;
		case 17:
		case 18: $cat = 'JUN'; break;
		case ($age >= 19 && $age <= 34): $cat = 'SEN'; break;
		case ($age >= 35 && $age <= 39): $cat = 'V1'; break;
		case ($age >= 40 && $age <= 44): $cat = 'V2'; break;
		case ($age >= 45 && $age <= 49): $cat = 'V3'; break;
		case ($age >= 50 && $age <= 54): $cat = 'V4'; break;
		case ($age >= 55 && $age <= 59): $cat = 'V5'; break;
		default: $cat = '';
	}
	return $cat;
}

// utyTimeInterval

function utyTimeInterval($time, $interval)
{
	$data = explode(':',$time);
	if (count($data) == 2)
	{
			$hour = (int) $data[0];
			$minute = (int) $data[1];
			
			$minute += (int) $interval;
			
			$hour += (int) ($minute/60);
			$minute %= 60;

			return sprintf("%02d:%02d", $hour, $minute);
	}

	return $time;
}

// utyCodeComiteDept

function utyCodeComiteDept($codeClub)
{
	$myBdd = new MyBdd();
	return $myBdd->GetCodeComiteDept($codeClub);	
}

// utyCodeComiteReg

function utyCodeComiteReg($codeComiteDept)
{
	$myBdd = new MyBdd();
	return $myBdd->GetCodeComiteReg($codeComiteDept);	
}

// utyGetSession

function utyGetSession($param, $default = '')
{
		if (isset($_SESSION[$param]))
			return $_SESSION[$param];
			
		return $default;
}

// utyGetPost

function utyGetPost($param, $default = '')
{
		if (isset($_POST[$param]))
			return $_POST[$param];
			
		return $default;
}

// utyGetGet

function utyGetGet($param, $default = '')
{
		if (isset($_GET[$param]))
			return $_GET[$param];
			
		return $default;
}

// utyGetInt
function utyGetInt($array, $param, $default = 1)
{
    if (isset($array[$param])) {
        return (int) $array[$param];
    }
    return $default;
}

// utyGetString
function utyGetString($array, $param, $default = 1)
{
    if (isset($array[$param])) {
        return addslashes( $array[$param] );
    }
    return $default;
}

// utyGetSessionPostGet

function utyGetSessionPostGet($param, $default = '')
{
    if (isset($_GET[$param])) {
        return $_GET[$param];
    } elseif (isset($_POST[$param])) {
        return $_POST[$param];
    } elseif (isset($_SESSION[$param])) {
        return $_SESSION[$param];
    } else {
        return $default;
    }

    return $default;
}

// utyGetPDF

function utyGetPDF($param)
{
		if (isset($_SESSION[$param]))
			return $_SESSION[$param];
			
		return '';
	
/*		return '*'.$param.'*'; */
}

// utyIsScoreOk

function utyIsScoreOk($scoreA, $scoreB)
{
	if ( ($scoreA == '') || ($scoreA == '?') )
		return false;

	if ( ($scoreB == '') || ($scoreB == '?') )
		return false;
				
	return true;
}

// utyIsTypeCltCoupe

function utyIsTypeCltCoupe($typeClt)
{
	if (strlen($typeClt) == 0)
		return false;

	if ($typeClt == 'CHPT')
		return false;
		
	return true;
}

// utyIsEquipeOk

function utyIsEquipeOk($idEquipeA, $idEquipeB)
{
	if (strlen($idEquipeA) == 0)
		return false;

	if (strlen($idEquipeB) == 0)
		return false;

	if ( (int) $idEquipeA <= 0)
		return false;
		
	if ( (int) $idEquipeB <= 0)
		return false;
				
	return true;
}

// utyKeyOrder

function utyKeyOrder($orderBy, $iKey, $removePrefixe = true)
{
		$arrayKey = explode(',', $orderBy);
		if (count($arrayKey) <= $iKey)
			return ''; // Pas de correspondance ... on retourne une chaine vide
	
		$key = trim($arrayKey[$iKey]);
		
		if ($iKey == 0)
			$key = str_ireplace('Order By ', '', $key);
		
		if (!$removePrefixe)
			return $key;
		
		$pos = strpos($key, '.');
		if ($pos === false)
			return $key;

		return substr($key, $pos+1);
}

// utyGetSaison

function utyGetSaison()
{
		if (isset($_SESSION['Saison']))
			return $_SESSION['Saison'];

		$myBdd = new MyBdd();
		$saison = $myBdd->GetActiveSaison();	
		
		$_SESSION['Saison'] = $saison;
		return $saison;
}

// utyAuthSaison

function utyAuthModif()
{
		$myBdd = new MyBdd();
		
		$profile = utyGetSession('Profile');
		$ActiveSaison = $myBdd->GetActiveSaison();	
		$Saison = utyGetSaison();	
		$AuthSaison = utyGetSession('AuthSaison','');
		
		if($Saison >= $ActiveSaison && $profile != 8)
			$AuthModif = 'O';
		elseif($AuthSaison == 'O')
			$AuthModif = 'O';
		else
			$AuthModif = '';
		
		$_SESSION['AuthModif'] = $AuthModif;
		return $AuthModif;
}

// utyGetLabelCompetition

function utyGetLabelCompetition($codeCompet)
{
		$myBdd = new MyBdd();
		return $myBdd->GetLabelCompetition($codeCompet, utyGetSaison());	
}

// utyGetFiltreCompetition

function utyGetFiltreCompetition($alias)
{
	// Exemple de Filtre : "And a.Code Like 'JN%' And a.Code_saison = '2008' And a.Code_niveau = 'NAT' ";	
	$filtre = utyGetSession('Filtre_Competition');

	$filtre = trim($filtre);
	if (strlen($filtre) == 0)
		return '';
		
	$filtre = str_replace('a.', $alias, $filtre);
	
	return $filtre;
}

// utyIsAutorisationJournee

function utyIsAutorisationJournee($idJournee)
{
	// Exemple de Filtre : "20085231,20085141";	
	$filtre = utyGetSession('Filtre_Journee');

	$filtre = trim($filtre);
	if (strlen($filtre) == 0)
		return true;

	$filtre = ','.$filtre.',';
	$key = ','.$idJournee.',';
		
	if (strstr($filtre, $key) == FALSE)
		return false;
		
	return true;
}

// utyStringQuote

function utyStringQuote($string)
{
	$newstring = "";
	for ($i=0;$i<strlen($string);$i++)
	{
		$newstring .= $string[$i];
		if ($string[$i] == '\'')
			$newstring .= '\'';
	}
	
	return $newstring;
}

function utyEquipesAffectAuto($intitule)
{
	// On contrôle qu'il y a un crochet ouvrant et un fermant, et on prend le contenu.
	$intitule = preg_split("/[\[]/",$intitule);
	if($intitule[1] == "")
		return '';
	$intitule = preg_split("/[\]]/",$intitule[1]);
	if($intitule[0] == "")
		return '';
	//$texte .= '<br>'.$intitule[0].'<br>';
	// On sépare par tiret, slash, étoile, virgule ou point-virgule.
	$intitule = preg_split("/[\-\/*,;]/",$intitule[0]);
	// On analyse le contenu
	for ($j=0;$j<4;$j++)
	{
		if(isset($intitule[$j]))
		{
			$resultat = '';
			$codeTirage = '';
			$codeVainqueur = '';
			$codePerdant = '';
			$codePoule = '';
			//preg_match("/([T])/",$intitule[$j],$codeTirage); // tirage au sort
			//preg_match("/([V])/",$intitule[$j],$codeVainqueur); // vainqueur
			//preg_match("/([P])/",$intitule[$j],$codePerdant); // perdant
			//preg_match("/([A-O])/",$intitule[$j],$codePoule); // lettre de poule
			preg_match("/([A-Z]+)/",$intitule[$j],$codeLettres); // lettre
			preg_match("/([0-9]+)/",$intitule[$j],$codeNumero); // numero de match ou classement de poule
				$posNumero = strpos($intitule[$j], $codeNumero[1]);
				$posLettres = strpos($intitule[$j], $codeLettres[1]);
				if($posNumero > $posLettres){
					switch($codeLettres[1]){
						case 'T' : // tirage
						case 'D' : // draw
							$resultat = '(Team '.$codeNumero[1].')';
							break;
						case 'V' : // vainqueur
						case 'G' : // gagnant
						case 'W' : // winner
							$resultat = '(Winner game #'.$codeNumero[1].')';
							break;
						case 'P' : // Perdant
						case 'L' : // Loser
							$resultat = '(Loser game #'.$codeNumero[1].')';
							break;
						default :
							$resultat = 'ERREUR CODE : '.$intitule[$j];
							break;
					}
				}else{ // poule
					if($codeNumero[1] == 1)
						$resultat = '(1st Group '.$codeLettres[1].')';
					elseif($codeNumero[1] == 2)
						$resultat = '(2nd Group '.$codeLettres[1].')';
					elseif($codeNumero[1] == 3)
						$resultat = '(3rd Group '.$codeLettres[1].')';
					elseif($codeNumero[1] > 3)
						$resultat = '('.$codeNumero[1].'th Group '.$codeLettres[1].')';
				}
			$result[$j] = $resultat;
		}
	}
	return $result;
}
	
function utyEquipesAffectAutoFR($intitule)
{
	// On contrôle qu'il y a un crochet ouvrant et un fermant, et on prend le contenu.
	$intitule = preg_split("/[\[]/",$intitule);
	if(!isset($intitule[1]) || $intitule[1] == "")
		return '';
	$intitule = preg_split("/[\]]/",$intitule[1]);
	if($intitule[0] == "")
		return '';
	// $texte = '<br>'.$intitule[0].'<br>';
	// On sépare par tiret, slash, étoile, virgule ou point-virgule.
	$intitule = preg_split("/[\-\/*,;]/",$intitule[0]);
	// On analyse le contenu
	for ($j=0;$j<4;$j++)
	{
		if(isset($intitule[$j]))
		{
			$resultat = '';
			$codeTirage = '';
			$codeVainqueur = '';
			$codePerdant = '';
			$codePoule = '';
			//preg_match("/([T])/",$intitule[$j],$codeTirage); // tirage au sort
			//preg_match("/([V])/",$intitule[$j],$codeVainqueur); // vainqueur
			//preg_match("/([P])/",$intitule[$j],$codePerdant); // perdant
			//preg_match("/([A-O])/",$intitule[$j],$codePoule); // lettre de poule
			preg_match("/([A-Z]+)/",$intitule[$j],$codeLettres); // lettre
			preg_match("/([0-9]+)/",$intitule[$j],$codeNumero); // numero de match ou classement de poule
				if(isset($codeNumero[1]))
                    $posNumero = strpos($intitule[$j], $codeNumero[1]);
				if(isset($codeLettres[1]))
                    $posLettres = strpos($intitule[$j], $codeLettres[1]);
				if(isset($codeLettres[1]) && $posNumero > $posLettres){
					switch($codeLettres[1]){
						case 'T' : // tirage
						case 'D' : // draw
							$resultat = '(Equipe '.$codeNumero[1].')';
							break;
						case 'V' : // vainqueur
						case 'G' : // gagnant
						case 'W' : // winner
							$resultat = '(Vainqueur match '.$codeNumero[1].')';
							break;
						case 'P' : // Perdant
						case 'L' : // Loser
							$resultat = '(Perdant match '.$codeNumero[1].')';
							break;
						default :
							$resultat = 'ERREUR CODE : '.$intitule[$j];
							break;
					}
				}else{ // poule
                    if(isset($codeNumero[1]) && isset($codeLettres[1])){
                        if($codeNumero[1] == 1)
                            $resultat = '(1er Poule '.$codeLettres[1].')';
                        elseif($codeNumero[1] == 2)
                            $resultat = '(2nd Poule '.$codeLettres[1].')';
                        elseif($codeNumero[1] == 3)
                            $resultat = '(3e Poule '.$codeLettres[1].')';
                        elseif($codeNumero[1] > 3)
                            $resultat = '('.$codeNumero[1].'e Poule '.$codeLettres[1].')';
                    }
				}
			$result[$j] = $resultat;
		}
	}
	return $result;
}
	
/***********************************/
/*     Génèrer un mot de passe      */
/***********************************/
// $size : longueur du mot passe voulue
function Genere_Password($size)
{
    // Initialisation des caractères utilisables
    $characters = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
    $password = '';
    for($i=0; $i<$size; $i++)
    {
        $password .= ($i%2) ? strtoupper($characters[array_rand($characters)]) : $characters[array_rand($characters)];
    }
		
    return $password;
}

// Petit exemple
//$mon_mot_de_passe = Genere_Password(10);
//echo $mon_mot_de_passe;

// Suppression d'une ligne d'un tableau non associatif
function utyArrayRemoveRow(&$array, $row)
{
	for ($i = $row; $i < count($array)-1; $i++)  
	{ 
		$array[$i] = $array[$i + 1]; 
	} 
	
	unset($array[count($array) - 1]);
}

// 10:30 => 630 , 10:30:00 => 630
function utyHHMM_To_MM($hour)
{
    $data = explode(':',$hour);
    if (count($data) >= 2)
    {
        $hour = (int) $data[0];
        $minute = (int) $data[1];

        return $hour*60+$minute;
    }

    return (int) $hour;
}

function utyMM_To_HHMM($time)
{
	$hour = (int) ($time/60);
	$min = $time - 60*$hour;
    return sprintf("%02d:%02d", $hour, $min);
}

?>
