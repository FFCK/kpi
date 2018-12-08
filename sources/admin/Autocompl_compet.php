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
		$q = $myBdd->RealEscapeString(trim(utyGetGet('q')));
		$sql  = "SELECT * "
                . "FROM gickp_Competitions "
                . "WHERE Code LIKE '%".$q."%' "
                . "OR Libelle LIKE '%".$q."%' "
                . "GROUP BY Code, Libelle "
                . "ORDER BY Code_saison DESC, Code, Libelle ";
	
    $result = $myBdd->Query($sql);
    $resultGlobal = '';
    while($row = $myBdd->FetchAssoc($result)) {
		$code = $row['Code'];
		//$libelle = __encode($row['Libelle']);
		$libelle = $row['Libelle'];
		$Code_niveau = $row['Code_niveau'];
		$Code_ref = $row['Code_ref'];
		$GroupOrder = $row['GroupOrder'];
		$Code_typeclt = $row['Code_typeclt'];
		$Code_tour = $row['Code_tour'];
		$Qualifies = $row['Qualifies'];
		$Elimines = $row['Elimines'];
		$Points = $row['Points'];
		//$Soustitre = __encode($row['Soustitre']);
		//$Soustitre2 = __encode($row['Soustitre2']);
		$Soustitre = $row['Soustitre'];
		$Soustitre2 = $row['Soustitre2'];
		$Web = $row['Web'];
		$BandeauLink = $row['BandeauLink'];
		$LogoLink = $row['LogoLink'];
		$SponsorLink = $row['SponsorLink'];
		$ToutGroup = $row['ToutGroup'];
		$TouteSaisons = $row['TouteSaisons'];
		$Titre_actif = $row['Titre_actif'];
		$Bandeau_actif = $row['Bandeau_actif'];
		$Logo_actif = $row['Logo_actif'];
		$Sponsor_actif = $row['Sponsor_actif'];
		$Kpi_ffck_actif = $row['Kpi_ffck_actif'];
		$En_actif = $row['En_actif'];
		$resultGlobal .= "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|$Code_typeclt|$Code_tour|$Qualifies|$Elimines|$Points|$Soustitre|$Web|$LogoLink|$SponsorLink|$ToutGroup|$TouteSaisons|$GroupOrder|$Soustitre2|$Titre_actif|$Logo_actif|$Sponsor_actif|$Kpi_ffck_actif|$En_actif|$BandeauLink|$Bandeau_actif\n";
	}
	echo $resultGlobal;

