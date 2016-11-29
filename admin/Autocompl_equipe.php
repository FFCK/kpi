<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

	$myBdd = new MyBdd();
	
	function __encode($var){
		global $html_entities;
 
		foreach ($html_entities as $key => $value) {
			$var = str_replace($key, $value, $var);
		}
		return $var;
	}
/*	
	$html_entities = array (
			"À" =>  "&Agrave;",	#capital a, grave accent
			"Á" =>  "&Aacute;", 	#capital a, acute accent
			"Â" =>  "&Acirc;", 	#capital a, circumflex accent
			"Ã" =>  "&Atilde;", 	#capital a, tilde
			"Ä" => "&Auml;",	#capital a, umlaut mark
			"Å" => "&Aring;", 	#capital a, ring
			"Æ" => "&AElig;", 	#capital ae
			"Ç" => "&Ccedil;", 	#capital c, cedilla
			"È" => "&Egrave;", 	#capital e, grave accent
			"É" => "&Eacute;", 	#capital e, acute accent
			"Ê" => "&Ecirc;", 	#capital e, circumflex accent
			"Ë" => "&Euml;", 	#capital e, umlaut mark
			"Ì" => "&Igrave;", 	#capital i, grave accent
			"Í" => "&Iacute;", 	#capital i, acute accent
			"Î" => "&Icirc;", 	#capital i, circumflex accent
			"Ï" => "&Iuml;", 	#capital i, umlaut mark
			"Ð" => "&ETH;",		#capital eth, Icelandic
			"Ñ" => "&Ntilde;", 	#capital n, tilde
			"Ò" => "&Ograve;", 	#capital o, grave accent
			"Ó" => "&Oacute;", 	#capital o, acute accent
			"Ô" => "&Ocirc;", 	#capital o, circumflex accent
			"Õ" => "&Otilde;", 	#capital o, tilde
			"Ö" => "&Ouml;", 	#capital o, umlaut mark
			"Ø" => "&Oslash;", 	#capital o, slash
			"Ù" => "&Ugrave;", 	#capital u, grave accent
			"Ú" => "&Uacute;", 	#capital u, acute accent
			"Û" => "&Ucirc;", 	#capital u, circumflex accent
			"Ü" => "&Uuml;", 	#capital u, umlaut mark
			"Ý" => "&Yacute;", 	#capital y, acute accent
			"Þ" => "&THORN;", 	#capital THORN, Icelandic
			"ß" => "&szlig;", 	#small sharp s, German
			"à" => "&agrave;", 	#small a, grave accent
			"á" => "&aacute;", 	#small a, acute accent
			"â" => "&acirc;", 	#small a, circumflex accent
			"ã" => "&atilde;", 	#small a, tilde
			"ä" => "&auml;", 	#small a, umlaut mark
			"å" => "&aring;", 	#small a, ring
			"æ" => "&aelig;", 	#small ae
			"ç" => "&ccedil;", 	#small c, cedilla
			"è" => "&egrave;", 	#small e, grave accent
			"é" => "&eacute;", 	#small e, acute accent
			"ê" => "&ecirc;", 	#small e, circumflex accent
			"ë" => "&euml;", 	#small e, umlaut mark
			"ì" => "&igrave;", 	#small i, grave accent
			"í" => "&iacute;", 	#small i, acute accent
			"î" => "&icirc;", 	#small i, circumflex accent
			"ï" => "&iuml;", 	#small i, umlaut mark
			"ð" => "&eth;",		#small eth, Icelandic
			"ñ" => "&ntilde;", 	#small n, tilde
			"ò" => "&ograve;", 	#small o, grave accent
			"ó" => "&oacute;", 	#small o, acute accent
			"ô" => "&ocirc;", 	#small o, circumflex accent
			"õ" => "&otilde;", 	#small o, tilde
			"ö" => "&ouml;", 	#small o, umlaut mark
			"ø" => "&oslash;", 	#small o, slash
			"ù" => "&ugrave;", 	#small u, grave accent
			"ú" => "&uacute;", 	#small u, acute accent
			"û" => "&ucirc;", 	#small u, circumflex accent
			"ü" => "&uuml;", 	#small u, umlaut mark
			"ý" => "&yacute;", 	#small y, acute accent
			"þ" => "&thorn;", 	#small thorn, Icelandic
			"ÿ" => "&yuml;"		#small y, umlaut mark
			);
*/
	$html_entities = array ( //SANS ACCENT !
			"À" =>  "A",	#capital a, grave accent
			"Á" =>  "A", 	#capital a, acute accent
			"Â" =>  "A", 	#capital a, circumflex accent
			"Ã" =>  "A", 	#capital a, tilde
			"Ä" => "A",	#capital a, umlaut mark
			"Å" => "A", 	#capital a, ring
			"Æ" => "AE", 	#capital ae
			"Ç" => "C", 	#capital c, cedilla
			"È" => "E", 	#capital e, grave accent
			"É" => "E", 	#capital e, acute accent
			"Ê" => "E", 	#capital e, circumflex accent
			"Ë" => "E", 	#capital e, umlaut mark
			"Ì" => "I", 	#capital i, grave accent
			"Í" => "I", 	#capital i, acute accent
			"Î" => "I", 	#capital i, circumflex accent
			"Ï" => "I", 	#capital i, umlaut mark
			"Ð" => "&ETH;",		#capital eth, Icelandic
			"Ñ" => "N", 	#capital n, tilde
			"Ò" => "O", 	#capital o, grave accent
			"Ó" => "O", 	#capital o, acute accent
			"Ô" => "O", 	#capital o, circumflex accent
			"Õ" => "O", 	#capital o, tilde
			"Ö" => "O", 	#capital o, umlaut mark
			"Ø" => "O", 	#capital o, slash
			"Ù" => "U", 	#capital u, grave accent
			"Ú" => "U", 	#capital u, acute accent
			"Û" => "U", 	#capital u, circumflex accent
			"Ü" => "U", 	#capital u, umlaut mark
			"Ý" => "U", 	#capital y, acute accent
			"Þ" => "&THORN;", 	#capital THORN, Icelandic
			"ß" => "s", 	#small sharp s, German
			"à" => "a", 	#small a, grave accent
			"á" => "a", 	#small a, acute accent
			"â" => "a", 	#small a, circumflex accent
			"ã" => "a", 	#small a, tilde
			"ä" => "a", 	#small a, umlaut mark
			"å" => "a", 	#small a, ring
			"æ" => "ae", 	#small ae
			"ç" => "c", 	#small c, cedilla
			"è" => "e", 	#small e, grave accent
			"é" => "e", 	#small e, acute accent
			"ê" => "e", 	#small e, circumflex accent
			"ë" => "e", 	#small e, umlaut mark
			"ì" => "i", 	#small i, grave accent
			"í" => "i", 	#small i, acute accent
			"î" => "i", 	#small i, circumflex accent
			"ï" => "i", 	#small i, umlaut mark
			"ð" => "&eth;",		#small eth, Icelandic
			"ñ" => "n", 	#small n, tilde
			"ò" => "o", 	#small o, grave accent
			"ó" => "o", 	#small o, acute accent
			"ô" => "o", 	#small o, circumflex accent
			"õ" => "o", 	#small o, tilde
			"ö" => "o", 	#small o, umlaut mark
			"ø" => "o", 	#small o, slash
			"ù" => "u", 	#small u, grave accent
			"ú" => "u", 	#small u, acute accent
			"û" => "u", 	#small u, circumflex accent
			"ü" => "u", 	#small u, umlaut mark
			"ý" => "y", 	#small y, acute accent
			"þ" => "&thorn;", 	#small thorn, Icelandic
			"ÿ" => "y"		#small y, umlaut mark
			);
			

	// Chargement
		$q = utyGetGet('q');
		$sql  = "Select e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub ";
		$sql .= "From gickp_Equipe e, gickp_Club c ";
		$sql .= "Where (UPPER(e.Libelle) LIKE UPPER('%".$q."%') ";
		$sql .= "Or UPPER(e.Code_club) LIKE UPPER('%".$q."%') ";
		$sql .= "Or UPPER(c.Libelle) LIKE UPPER('%".$q."%')) ";
		$sql .= "And e.Code_club = c.Code ";
		$sql .= "Order by e.Libelle ";
	
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_equipe : ".$sql);
	$resultGlobal = '';
	//header('Content-Type: application/json; charset=ISO-8859-1');
	//$response = array();
	while ($row = mysql_fetch_assoc($result)) {
		$Code_club = $row['Code_club'];
		$nomClub = $row['nomClub'];
		$Numero = $row['Numero'];
		$Libelle = $row['Libelle'];
		$resultGlobal .= "$Code_club - $Libelle ______($nomClub)|$Numero|$Libelle\n";
		//$response[] = array("$code - $libelle", $code, $libelle);
		//echo "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|<br>\n";
	}
	echo $resultGlobal;
	
/*	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($response);

	$response = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$code = $row['Code'];
		$libelle = $row['Libelle'];
		$response .= "$code - $libelle|$code|$libelle\n";
		//$response[] = array($i++, "$code - $libelle|$code|$libelle\n");
		//$response[] = array($i++, $row);
	}
	echo $response;
*/
?>