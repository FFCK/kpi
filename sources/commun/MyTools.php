<?php
// MyTools.php

include_once('MyBdd.php');

// htmlpurifier
if (is_file('lib/htmlpurifier/HTMLPurifier.auto.php')) {
	require_once 'lib/htmlpurifier/HTMLPurifier.auto.php';
} elseif (is_file('../lib/htmlpurifier/HTMLPurifier.auto.php')) {
	require_once '../lib/htmlpurifier/HTMLPurifier.auto.php';
} else {
	require_once '../../lib/htmlpurifier/HTMLPurifier.auto.php';
}
$config = HTMLPurifier_Config::createDefault();
$config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
$config->set('HTML.Doctype', 'XHTML 1.0 Transitional'); // replace with your doctype
$purifier = new HTMLPurifier($config);


function debug($variable, $die = false)
{
	echo '<pre>';
	print_r($variable);
	echo '<pre>';

	if ($die) {
		die();
	}
}

/**
 * Redimentionne image pour PDF
 * 
 * @param String $image
 * @param Int $largeurPage
 * @param Int $marge
 * @param Int $newHauteur hauteur de l'image redimentionnée
 * @param String $position (L, C, R)
 */
function redimImage($image, $largeurPage, $marge, $newHauteur, $position = 'C')
{
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
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_web_page($url)
{
	$options = array(
		CURLOPT_RETURNTRANSFER => true,     // return web page
		CURLOPT_HEADER         => false,    // don't return headers
		CURLOPT_FOLLOWLOCATION => false,     // follow redirects
		CURLOPT_ENCODING       => "",       // handle all encodings
		CURLOPT_USERAGENT      => "spider", // who am i
		CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		CURLOPT_TIMEOUT        => 120,      // timeout on response
		CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
		CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
	);

	$ch      = curl_init($url);
	curl_setopt_array($ch, $options);
	$content = curl_exec($ch);
	$err     = curl_errno($ch);
	$errmsg  = curl_error($ch);
	$header  = curl_getinfo($ch);
	curl_close($ch);

	$header['errno']   = $err;
	$header['errmsg']  = $errmsg;
	$header['content'] = $content;
	return $header;
}

/**
 * captureImg Rappatrie une image jpg distante sur le serveur
 * 
 * @param String $url
 * @param String $type B|L|S (Bandeau, Logo, Sponsor)
 * @param String $code Code compétition
 * @param Integer $saison
 */
function captureImg($url, $type, $code, $saison, $folder = "../img/logo/")
{
	$types = ['B', 'L', 'S'];

	// jpg, png, gif or bmp?
	$exploded = explode('.', $url);
	$ext = substr($exploded[count($exploded) - 1], 0, 3);
	if ($ext == 'jpe') {
		$ext = 'jpg';
	}
	if (!in_array($type, $types)) {
		// echo "Type incorrect : $url !<br>";
		return FALSE;
	}
	if (
		strpos($url, 'http://') === false
		&& strpos($url, 'https://') === false
	) {
		// echo "Image locale : $url<br>";
		return FALSE;
	}

	$newfile = $type . '-' . $code . '-' . $saison;

	//Récupération du fichier distant
	if (!$header = get_web_page($url)) {
		// echo "Ouverture impossible du fichier distant<br>";
		return FALSE;
	}
	//Déjà existant ? on incrémente
	if (is_file($folder . $newfile . '.jpg')) {
		for ($i = 1; $i < 50; $i++) {
			if (!is_file($folder . $newfile . '(' . $i . ')' . '.jpg')) {
				$newfile = $newfile . '(' . $i . ')';
				break;
			}
		}
	}
	$newfile = $newfile . '.' . $ext;

	//Ecriture du fichier
	if (!file_put_contents($folder . $newfile, $header['content'])) {
		// echo "Ecriture impossible du fichier local<br>";
		return FALSE;
	}
	//Conversion en jpg
	if ($ext == "png") {
		if (!$newfile = convertPngToJpg($folder, $newfile)) {
			// echo "Image $newfile inexploitable ! <br>";
			return FALSE;
		}
	} elseif ($ext == "gif") {
		if (!$newfile = convertGifToJpg($folder, $newfile)) {
			// echo "Image $newfile inexploitable ! <br>";
			return FALSE;
		}
	}

	return $newfile;
}

/**
 * convertPngToJpg
 */
function convertPngToJpg($folder, $img)
{
	if (!$new_pic = imagecreatefrompng($folder . $img)) {
		return FALSE;
	}
	$new_name = str_replace(".png", ".jpg", $img);
	// Create a new true color image with the same size
	$w = imagesx($new_pic);
	$h = imagesy($new_pic);
	$white = imagecreatetruecolor($w, $h);
	// Fill the new image with white background
	$bg = imagecolorallocate($white, 255, 255, 255);
	imagefill($white, 0, 0, $bg);
	// Copy original transparent image onto the new image
	imagecopy($white, $new_pic, 0, 0, 0, 0, $w, $h);
	$new_pic = $white;
	imagejpeg($new_pic, $folder . $new_name);
	//nettoyage
	imagedestroy($new_pic);
	unlink($folder . $img);

	return $new_name;
}

/**
 * convertGifToJpg
 */
function convertGifToJpg($folder, $img)
{
	if (!$new_pic = imagecreatefromgif($folder . $img)) {
		return FALSE;
	}
	$new_name = str_replace(".gif", ".jpg", $img);
	// Create a new true color image with the same size
	$w = imagesx($new_pic);
	$h = imagesy($new_pic);
	$white = imagecreatetruecolor($w, $h);
	// Fill the new image with white background
	$bg = imagecolorallocate($white, 255, 255, 255);
	imagefill($white, 0, 0, $bg);
	// Copy original transparent image onto the new image
	imagecopy($white, $new_pic, 0, 0, 0, 0, $w, $h);
	$new_pic = $white;
	imagejpeg($new_pic, $folder . $new_name);
	//nettoyage
	imagedestroy($new_pic);
	unlink($folder . $img);

	return $new_name;
}


/**
 * Retourne les bandeau, logo et sponsor d'une compétition
 * 
 * @param array $recordCompetition
 * @return array bandeau, logo, sponsor
 */
function utyGetVisuels($recordCompetition, $admin = FALSE)
{
	$result = [];
	if ($admin) {
		$rel = '../';
	} else {
		$rel = '';
	}
	if ($recordCompetition['BandeauLink'] != '' && strpos($recordCompetition['BandeauLink'], 'http') === FALSE) {
		$recordCompetition['BandeauLink'] = $rel . 'img/logo/' . $recordCompetition['BandeauLink'];
		if (is_file($recordCompetition['BandeauLink'])) {
			$result['bandeau'] = $recordCompetition['BandeauLink'];
		}
	} elseif ($recordCompetition['BandeauLink'] != '') {
		$result['bandeau'] = $recordCompetition['BandeauLink'];
	}
	if ($recordCompetition['LogoLink'] != '' && strpos($recordCompetition['LogoLink'], 'http') === FALSE) {
		$recordCompetition['LogoLink'] = $rel . 'img/logo/' . $recordCompetition['LogoLink'];
		if (is_file($recordCompetition['LogoLink'])) {
			$result['logo'] = $recordCompetition['LogoLink'];
		}
	} elseif ($recordCompetition['LogoLink'] != '') {
		$result['logo'] = $recordCompetition['LogoLink'];
	}
	if ($recordCompetition['SponsorLink'] != '' && strpos($recordCompetition['SponsorLink'], 'http') === FALSE) {
		$recordCompetition['SponsorLink'] = $rel . 'img/logo/' . $recordCompetition['SponsorLink'];
		if (is_file($recordCompetition['SponsorLink'])) {
			$result['sponsor'] = $recordCompetition['SponsorLink'];
		}
	} elseif ($recordCompetition['LogoLink'] != '') {
		$result['sponsor'] = $recordCompetition['SponsorLink'];
	}
	return $result;
}

// Retourne fichier logo
function utySearchLogoFile($club)
{
	if (strlen($club) === 4 && is_file('../img/KIP/logo/' . $club . '-logo.png')) {
		$logo = 'KIP/logo/' . $club . '-logo.png';
	} elseif (strlen($club) !== 4 && is_file('../img/Nations/' . substr($club, 0, 3) . '.png')) {
		$logo = 'Nations/' . substr($club, 0, 3) . '.png';
	} else {
		$logo = null;
	}
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
	$data = explode($separaror, $dateUs);
	if (count($data) == 3)
		return $data[2] . '/' . $data[1] . '/' . $data[0];

	return $dateUs; // Error ...
}

// Transformation Date Fr : DD/MM/YYYY en Date Us : YYYY-MM-DD

function utyDateFrToUs($dateFr, $separaror = '/')
{
	$data = explode($separaror, $dateFr);
	if (count($data) == 3)
		return $data[2] . '-' . $data[1] . '-' . $data[0];

	return $dateFr; // Error ...
}

// utyYearOfDate 

function utyYearOfDate($dateUs)
{
	$data = explode('-', $dateUs);
	if (count($data) == 3)
		return (int) $data[0];

	return 0;	// Error
}

/**
 * Comparaison de dates
 *
 * @param [type] $date1
 * @param [type] $date2
 * @return void
 */
function utyDateCmpFr($date1, $date2)
{
	$data1 = explode('/', $date1);
	$data2 = explode('/', $date2);
	if (!isset($data1[2]) || !isset($data2[2])) {
		return 0;
	}
	// Comparaison Annee
	if ((int) $data1[2] != (int) $data2[2])
		return (int) $data1[2] - (int) $data2[2];

	// Comparaison Mois
	if ((int) $data1[1] != (int) $data2[1])
		return (int) $data1[1] - (int) $data2[1];

	// Comparaison Jour
	return (int) $data1[0] - (int) $data2[0];
}

// utyCodeCategorie
/*
function utyCodeCategorie($dateNaissance)
{
	$age = (int) $myBdd->GetActiveSaison() - utyYearOfDate($dateNaissance);
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
	if ($saison == '')
		$saison = utyGetSaison();
	$age = (int) $saison - utyYearOfDate($dateNaissance);
	switch ($age) {
		case 9:
		case 10:
			$cat = 'POU';
			break;
		case 11:
		case 12:
			$cat = 'BEN';
			break;
		case 13:
		case 14:
			$cat = 'MIN';
			break;
		case 15:
		case 16:
			$cat = 'CAD';
			break;
		case 17:
		case 18:
			$cat = 'JUN';
			break;
		case ($age >= 19 && $age <= 34):
			$cat = 'SEN';
			break;
		case ($age >= 35 && $age <= 39):
			$cat = 'V1';
			break;
		case ($age >= 40 && $age <= 44):
			$cat = 'V2';
			break;
		case ($age >= 45 && $age <= 49):
			$cat = 'V3';
			break;
		case ($age >= 50 && $age <= 54):
			$cat = 'V4';
			break;
		case ($age >= 55 && $age <= 59):
			$cat = 'V5';
			break;
		case ($age >= 60 && $age <= 64):
			$cat = 'V6';
			break;
		case ($age >= 65 && $age <= 69):
			$cat = 'V7';
			break;
		case ($age >= 70 && $age <= 74):
			$cat = 'V8';
			break;
		case ($age >= 75):
			$cat = 'V9';
			break;
		default:
			$cat = '';
	}
	return $cat;
}

// utyTimeInterval
function utyTimeInterval($time, $interval)
{
	$data = explode(':', $time);
	if (count($data) == 2) {
		$hour = (int) $data[0];
		$minute = (int) $data[1];

		$minute += (int) $interval;

		$hour += (int) ($minute / 60);
		$minute %= 60;

		return sprintf("%02d:%02d", $hour, $minute);
	}

	return $time;
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
	if (isset($_POST[$param])) {
		// return $_POST[$param];

		global $purifier;

		if (is_array($_POST[$param])) {
			foreach ($_POST[$param] as $element) {
				$pure_html[] = $purifier->purify($element);
			}
			return $pure_html;
		}

		$pure_html = $purifier->purify($_POST[$param]);
		return htmlspecialchars($pure_html);
	}

	return $default;
}

// utyGetGet
function utyGetGet($param, $default = '')
{
	if (isset($_GET[$param])) {
		// return $_GET[$param];

		global $purifier;
		$pure_html = $purifier->purify($_GET[$param]);

		return htmlspecialchars($pure_html);
	}

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
		// return $array[$param] ;

		global $purifier;
		$pure_html = $purifier->purify($array[$param]);

		return htmlspecialchars($pure_html);
	}
	return $default;
}

// utyGetJsonPost
function utyGetJsonPost($param, $default = '')
{
	if (isset($_POST[$param]) && !empty($_POST[$param])) {
		json_decode($_POST[$param]);
		if (json_last_error() === JSON_ERROR_NONE) {
			return $_POST[$param];
		}
	}

	return $default;
}

// utyGetPrenom
function utyGetPrenom($array, $param, $default = '')
{
	if (isset($array[$param])) {
		global $purifier;
		$pure_html = $purifier->purify($array[$param]);
		$prenom = utyUcName($pure_html);
		return $prenom;
	}
	return $default;
}

/**
 * Supprime numéro de licence entre parenthèse
 *
 * @param [string] $string
 * @return void
 */
function utyGetNomPrenom($string)
{
	$result = explode(' (', $string);
	return $result[0];
}

/**
 * ucfirst incluant tiret et apostrophe
 * @param $string
 * @return $string
 */
function utyUcName($string)
{
	$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");

	foreach (array('-', '\'') as $delimiter) {
		if (strpos($string, $delimiter) !== false) {
			str_replace($delimiter, ' £ ', $string);
			$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
			str_replace(' £ ', $delimiter, $string);
		}
	}
	return $string;
}

/**
 * initials incluant tiret et apostrophe
 * @param $string
 * @return $string
 */
function utyInitials($string)
{
	$string = mb_convert_case(mb_strtolower($string, "UTF-8"), MB_CASE_TITLE, "UTF-8");
	$response = '';
	$words = preg_split("/(\s|\-|\.|\')/", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
	foreach ($words as $w) {
		if ($w === ' ') {
			$response .= '.' . $w;
		} else {
			$response .= mb_substr($w, 0, 1, "UTF-8");
		}
	}
	$response = $response . '.';
	return $response;
}

/**
 * Retourne le nom d'un arbitre formaté avec les initiales du prénom
 * 
 * @param string $refText nom de l'arbitre
 * @param int $refId numéro de l'arbitre
 * 
 * @return string nom de l'arbitre reformaté
 */
function utyInitialesPrenomArbitre($refText, $refNom, $refPrenom, $refId = 0)
{
	if ($refId == 0) {
		return $refText;
	}

	$nom_origine = mb_convert_case(mb_strtolower($refNom, "UTF-8"), MB_CASE_TITLE, "UTF-8")
		. ' ' . mb_convert_case(mb_strtolower($refPrenom, "UTF-8"), MB_CASE_TITLE, "UTF-8");

	$nom_destination = utyUcName($refNom) . ' ' . utyInitials($refPrenom);

	$result = str_replace($nom_origine, $nom_destination, $refText);
	return $result;
}

/**
 * Raccourcis un string et ajoute un . si trop long
 * @param string $str chaîne à raccourcir
 * @param int $num nombre de caractères maxi
 * 
 * @return string chaîne raccourcie
 */
function utyTruncateString($str, $num = 15)
{
	if (mb_strlen($str, "UTF-8") <= $num) {
		return $str;
	}
	return mb_substr($str, 0, $num, "UTF-8") . '.';
}

/**
 * arbitres sans niveau
 * @param $string
 * @return $string
 */
function utyArbSansNiveau($string)
{
	$arbsup = array(
		" (Pool Arbitres 1)", " (Pool Arbitres 2)", " INT-A", " INT-B", " INT-C", " INT-S",
		" INT", " NAT-A", " NAT-B", " NAT-C", " NAT-S", " NAT", " REG-S", "REG", " OTM", " JO"
	);

	$response = str_replace($arbsup, '', $string);
	return $response;
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
	if (($scoreA == '') || ($scoreA == '?'))
		return false;

	if (($scoreB == '') || ($scoreB == '?'))
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

	if ((int) $idEquipeA <= 0)
		return false;

	if ((int) $idEquipeB <= 0)
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

	return substr($key, $pos + 1);
}

// utyGetSaison
// A remplacer par $myBdd->GetActiveSaison();
function utyGetSaison()
{
	if (isset($_SESSION['Saison']))
		return $_SESSION['Saison'];

	$myBdd = new MyBdd();
	$saison = $myBdd->GetActiveSaison();

	return $saison;
}

// utyAuthSaison

function utyAuthModif()
{
	$myBdd = new MyBdd();

	$profile = utyGetSession('Profile');
	$ActiveSaison = $myBdd->GetActiveSaison();
	$Saison = $myBdd->GetActiveSaison();
	$AuthSaison = utyGetSession('AuthSaison', '');

	if ($Saison >= $ActiveSaison && $profile != 8)
		$AuthModif = 'O';
	elseif ($AuthSaison == 'O')
		$AuthModif = 'O';
	else
		$AuthModif = '';

	$_SESSION['AuthModif'] = $AuthModif;
	return $AuthModif;
}

// utyGetLabelCompetition
// à remplacer par $myBdd->GetLabelCompetition($codeCompet)
function utyGetLabelCompetition($codeCompet)
{
	$myBdd = new MyBdd();
	return $myBdd->GetLabelCompetition($codeCompet, $myBdd->GetActiveSaison());
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

	$filtre = ',' . $filtre . ',';
	$key = ',' . $idJournee . ',';

	if (strstr($filtre, $key) == FALSE)
		return false;

	return true;
}

// utyStringQuote

function utyStringQuote($string)
{
	$newstring = "";
	for ($i = 0; $i < strlen($string); $i++) {
		$newstring .= $string[$i];
		if ($string[$i] == '\'')
			$newstring .= '\'';
	}

	return $newstring;
}

function utyNomPrenomCourt($nom, $prenom)
{
	$reponse = mb_strtoupper(substr($nom, 0, 3)) . '. ';
	$prenom = explode('-', $prenom);
	$i = 0;
	foreach ($prenom as $part) {
		if ($i > 0) {
			$reponse .= '-';
		}
		$reponse .= mb_strtoupper(substr($part, 0, 1));
		$i++;
	}
	$reponse .= '.';
	return $reponse;
}


function utyEquipesAffectAuto($intitule)
{
	// On contrôle qu'il y a un crochet ouvrant et un fermant, et on prend le contenu.
	$intitule = preg_split("/[\[]/", $intitule);
	if (!isset($intitule[1]) || $intitule[1] == "") {
		return '';
	}
	$intitule = preg_split("/[\]]/", $intitule[1]);
	if ($intitule[0] == "") {
		return '';
	}
	// On sépare par tiret, slash, étoile, virgule ou point-virgule.
	$intitule = preg_split("/[\-\/*,;]/", $intitule[0]);
	// On analyse le contenu
	for ($j = 0; $j < 4; $j++) {
		if (isset($intitule[$j])) {
			$resultat = '';
			$codeTirage = '';
			$codeVainqueur = '';
			$codePerdant = '';
			$codePoule = '';
			preg_match("/([A-Z_]+)/", $intitule[$j], $codeLettres); // lettre
			preg_match("/([0-9]+)/", $intitule[$j], $codeNumero); // numero de match ou classement de poule
			if (isset($codeNumero[1])) {
				$posNumero = strpos($intitule[$j], $codeNumero[1]);
			}
			if (isset($codeLettres[1])) {
				$posLettres = strpos($intitule[$j], $codeLettres[1]);
			}
			if (isset($codeLettres[1]) && $posNumero > $posLettres) { // Tirage ou match
				switch ($codeLettres[1]) {
					case 'T': // tirage
					case 'D': // draw
						$resultat = '(Team ' . $codeNumero[1] . ')';
						break;
					case 'V': // vainqueur
					case 'G': // gagnant
					case 'W': // winner
						$resultat = '(Winner game #' . $codeNumero[1] . ')';
						break;
					case 'P': // Perdant
					case 'L': // Loser
						$resultat = '(Loser game #' . $codeNumero[1] . ')';
						break;
					default:
						$resultat = 'ERREUR CODE : ' . $intitule[$j];
						break;
				}
			} elseif (isset($codeLettres[1]) && $posNumero < $posLettres) { // poule
				if ($codeNumero[1] == 1) {
					$resultat = '(1st Group ' . $codeLettres[1] . ')';
				} elseif ($codeNumero[1] == 2) {
					$resultat = '(2nd Group ' . $codeLettres[1] . ')';
				} elseif ($codeNumero[1] == 3) {
					$resultat = '(3rd Group ' . $codeLettres[1] . ')';
				} elseif ($codeNumero[1] > 3) {
					$resultat = '(' . $codeNumero[1] . 'th Group ' . $codeLettres[1] . ')';
				}
			}
			$result[$j] = $resultat;
		}
	}
	return $result;
}

function utyEquipesAffectAutoFR($intitule)
{
	// On contrôle qu'il y a un crochet ouvrant et un fermant, et on prend le contenu.
	$intitule = preg_split("/[\[]/", $intitule);
	if (!isset($intitule[1]) || $intitule[1] == "") {
		return '';
	}
	$intitule = preg_split("/[\]]/", $intitule[1]);
	if ($intitule[0] == "") {
		return '';
	}
	// On sépare par tiret, slash, étoile, virgule ou point-virgule.
	$intitule = preg_split("/[\-\/*,;]/", $intitule[0]);
	// On analyse le contenu
	for ($j = 0; $j < 4; $j++) {
		if (isset($intitule[$j])) {
			$resultat = '';
			$codeTirage = '';
			$codeVainqueur = '';
			$codePerdant = '';
			$codePoule = '';
			preg_match("/([A-Z_]+)/", $intitule[$j], $codeLettres); // lettre
			preg_match("/([0-9]+)/", $intitule[$j], $codeNumero); // numero de match ou classement de poule
			if (isset($codeNumero[1])) {
				$posNumero = strpos($intitule[$j], $codeNumero[1]);
			}
			if (isset($codeLettres[1])) {
				$posLettres = strpos($intitule[$j], $codeLettres[1]);
			}
			if (isset($codeLettres[1]) && $posNumero > $posLettres) { // Tirage ou match
				switch ($codeLettres[1]) {
					case 'T': // tirage
					case 'D': // draw
						$resultat = '(Equipe ' . $codeNumero[1] . ')';
						break;
					case 'V': // vainqueur
					case 'G': // gagnant
					case 'W': // winner
						$resultat = '(Vainqueur match ' . $codeNumero[1] . ')';
						break;
					case 'P': // Perdant
					case 'L': // Loser
						$resultat = '(Perdant match ' . $codeNumero[1] . ')';
						break;
					default:
						$resultat = 'ERREUR CODE : ' . $intitule[$j];
						break;
				}
			} elseif (isset($codeLettres[1]) && $posNumero < $posLettres) { // poule
				if (isset($codeNumero[1]) && isset($codeLettres[1])) {
					if ($codeNumero[1] == 1) {
						$resultat = '(1er Poule ' . $codeLettres[1] . ')';
					} elseif ($codeNumero[1] == 2) {
						$resultat = '(2nd Poule ' . $codeLettres[1] . ')';
					} elseif ($codeNumero[1] == 3) {
						$resultat = '(3e Poule ' . $codeLettres[1] . ')';
					} elseif ($codeNumero[1] > 3) {
						$resultat = '(' . $codeNumero[1] . 'e Poule ' . $codeLettres[1] . ')';
					}
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
	$characters = array(
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o",
		"p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"
	);
	$password = '';
	for ($i = 0; $i < $size; $i++) {
		$password .= ($i % 2) ? strtoupper($characters[array_rand($characters)]) : $characters[array_rand($characters)];
	}

	return $password;
}

// Petit exemple
//$mon_mot_de_passe = Genere_Password(10);
//echo $mon_mot_de_passe;

// Suppression d'une ligne d'un tableau non associatif
function utyArrayRemoveRow(&$array, $row)
{
	for ($i = $row; $i < count($array) - 1; $i++) {
		$array[$i] = $array[$i + 1];
	}

	unset($array[count($array) - 1]);
}

// 10:30 => 630 , 10:30:00 => 630
function utyHHMM_To_MM($hour)
{
	$data = explode(':', $hour);
	if (count($data) >= 2) {
		$hour = (int) $data[0];
		$minute = (int) $data[1];

		return $hour * 60 + $minute;
	}

	return (int) $hour;
}

function utyMM_To_HHMM($time)
{
	$hour = (int) ($time / 60);
	$min = $time - 60 * $hour;
	return sprintf("%02d:%02d", $hour, $min);
}


function controle_pagaie($ECA, $EVI, $MER)
{
	$PagaieValide = 0;

	switch ($ECA) {
		case 'PAGR':
			$pagaie = 'Rouge';
			$PagaieValide = 1;
			break;
		case 'PAGN':
			$pagaie = 'Noire';
			$PagaieValide = 1;
			break;
		case 'PAGBL':
			$pagaie = 'Bleue';
			$PagaieValide = 1;
			break;
		case 'PAGV':
			$pagaie = 'Verte';
			$PagaieValide = 1;
			break;
		case 'PAGJ':
			$pagaie = 'Jaune';
			break;
		case 'PAGB':
			$pagaie = 'Blanche';
			break;
		default:
			$pagaie = '';
	}
	if ($PagaieValide == 0) {
		switch ($EVI) {
			case 'PAGR':
				$pagaie = 'Rouge';
				$PagaieValide = 2;
				break;
			case 'PAGN':
				$pagaie = 'Noire';
				$PagaieValide = 2;
				break;
			case 'PAGBL':
				$pagaie = 'Bleue';
				$PagaieValide = 2;
				break;
			case 'PAGV':
				$pagaie = 'Verte';
				$PagaieValide = 2;
				break;
		}
		switch ($MER) {
			case 'PAGR':
				$pagaie = 'Rouge';
				$PagaieValide = 3;
				break;
			case 'PAGN':
				$pagaie = 'Noire';
				$PagaieValide = 3;
				break;
			case 'PAGBL':
				$pagaie = 'Bleue';
				$PagaieValide = 3;
				break;
			case 'PAGV':
				$pagaie = 'Verte';
				$PagaieValide = 3;
				break;
		}
	}

	return [
		'pagaie' => $pagaie,
		'PagaieValide' => $PagaieValide
	];
}

/* HTTP ERRORS */
function methods($methods_array)
{
	if (!in_array($_SERVER['REQUEST_METHOD'], $methods_array)) {
		return_405();
		exit;
	}
	return;
}

function return_400($msg = 'Bad Request')
{
	header('HTTP/1.0 400 Bad Request');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($msg);
	exit;
}

function return_401($msg = 'Unauthorized')
{
	header('HTTP/1.0 401 Unauthorized');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($msg);
	exit;
}

function return_403($msg = 'Forbiden')
{
	header('HTTP/1.0 401 Forbiden');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($msg);
	exit;
}

function return_404($msg = 'Not Found')
{
	header('HTTP/1.0 404 Not Found');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($msg);
	exit;
}

function return_405($msg = 'Method Not Allowed')
{
	header('HTTP/1.0 405 Method Not Allowed');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($msg);
	exit;
}

function return_200($result = 'OK', $convert_to_json = true)
{
	http_response_code(200);
	header('Content-Type: application/json; charset=utf-8');
	$result = ($convert_to_json) ? json_encode($result) : $result;
	echo $result;
	exit;
}

function return_201($result = 'Created', $convert_to_json = true)
{
	http_response_code(201);
	header('Content-Type: application/json; charset=utf-8');
	$result = ($convert_to_json) ? json_encode($result) : $result;
	echo $result;
	exit;
}


function utySendMail($title, $content)
{
	$headers = 'From: KPI <contact@kayak-polo.info>' . "\r\n";
	$destinataires = 'kayak-polo.info <contact@kayak-polo.info>';
	$content = utyGetSession('User') . ' : ' . $content;

	if (SEND_MAIL) {
		mail($destinataires, $title, $content, $headers);
	} else {
		error_log("$title, $content");
	}

	return;
}

/**
 * UUID Générator
 */
function gen_uuid()
{
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),

		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff)
	);
}

/**
 * Génère le script Matomo Analytics
 *
 * @param string $type Type de site : 'public' pour la partie publique, 'admin' pour l'administration
 * @return string Le code JavaScript Matomo prêt à être inséré
 */
function utyGetMatomoScript($type = 'public')
{
	// Vérifier que les constantes sont définies
	if (!defined('MATOMO_SERVER_URL')) {
		return '<!-- Matomo non configuré -->';
	}

	// Déterminer le site ID selon le type
	$siteId = ($type === 'admin') ? MATOMO_SITE_ID_ADMIN : MATOMO_SITE_ID_PUBLIC;

	// URL du serveur Matomo (retirer le slash final s'il existe)
	$matomoUrl = rtrim(MATOMO_SERVER_URL, '/') . '/';

	// Générer le script
	$script = <<<MATOMO
<!-- Matomo -->
<script>
var _paq = window._paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function() {
	var u="{$matomoUrl}";
	_paq.push(['setTrackerUrl', u+'matomo.php']);
	_paq.push(['setSiteId', '{$siteId}']);
	var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
	g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
})();
</script>
<!-- End Matomo Code -->
MATOMO;

	return $script;
}
