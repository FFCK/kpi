<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
	session_start();

	$myBdd = new MyBdd();
	
    // Langue
    $langue = parse_ini_file("../commun/MyLang.ini", true);
    if (utyGetSession('lang') == 'en') {
        $lang = $langue['en'];
    } else {
        $lang = $langue['fr'];
    }

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
		$j = utyGetSession('sessionJournee','');
		$j = (int)utyGetGet('journee', $j);
		$m = utyGetSession('sessionMatch','');
		$m = (int)utyGetGet('sessionMatch',$m);
		$q = $myBdd->RealEscapeString(trim(utyGetGet('q')));
		$q = preg_replace('`^[0]*`','',$q);
		$resultGlobal = '';
		
		if($j == '' && $m == '') {
			$resultGlobal = "Selectionnez une journee / une phase !|XXX||||\n";
        } elseif(strlen($q) < 2) {
            $resultGlobal = "2 caractères minimum !|XXX||||\n";
        } else {
			// Equipes
			$resultGlobal .= "---------- " . $lang['Equipes'] . " ----------|XXX\n";
			$sql  = "Select a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet ";
			$sql .= "From gickp_Competitions_Equipes a, gickp_Journees b ";
			$sql .= "Where a.Code_compet = b.Code_competition ";
			$sql .= "And a.Code_saison = b.Code_saison ";
			if($j != '')
				$sql .= "And b.Id = ".$j." ";
			$sql .= "And UPPER(a.Libelle) LIKE UPPER('%".$q."%') ";
			$sql .= "Group By a.Libelle ";	 
			$sql .= "Order By a.Poule, a.Tirage, a.Libelle ";	 
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Equipes : ".$sql);
			while ($row = mysql_fetch_assoc($result))
			{
				//$libelle = __encode($row['Libelle']);
				$libelle = $row['Libelle'];
				$matric = '';
				$resultGlobal .= "$libelle|$matric|$libelle|||\n";
			}
			// Joueurs
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "---------- " . $lang['Joueurs'] . " ----------|XXX\n";
			$sql  = "Select distinct a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau, (c.Arb IS NULL) AS sortCol ";
			$sql .= "From gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, ";
			$sql .= "gickp_Competitions_Equipes b, gickp_Journees d, gickp_Matchs e ";
			$sql .= "Where a.Id_equipe = b.Id ";
			$sql .= "And b.Code_compet = d.Code_competition ";
			$sql .= "And b.Code_saison = d.Code_saison ";
			$sql .= "And d.Id = e.Id_journee ";
			$sql .= "AND a.Capitaine <> 'X' ";
			if ($j != '') {
                $sql .= "And d.Id = " . $j . " ";
            } elseif ($m != '') {
                $sql .= "And e.Id = " . $m . " ";
            }
            $sql .= "And (a.Matric Like '%".ltrim($q, '0')."%' ";
			$sql .= "Or UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(b.Libelle) LIKE UPPER('%".$q."%') ";
			$sql .= ") ";
			//$sql .= "Group By b.Libelle ";	 
			$sql .= "Order by b.Libelle, sortCol, c.Arb, a.Nom, a.Prenom ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Joueurs : ".$sql);
			while ($row = mysql_fetch_assoc($result))
			{
				$libelle = $row['Libelle'];
				$arb = strtoupper($row['Arb']);
				if ($row['niveau'] != '') {
                    $arb .= '-' . $row['niveau'];
                }
                $matric = $row['Matric'];
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				$resultGlobal .= "($libelle) $nom $prenom $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
//            die($resultGlobal);
			// Pool
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "---------- " . $lang['Pool_Arbitres'] . " ----------|XXX\n";
			$sql  = "Select a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau ";
			$sql .= "From gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, gickp_Competitions_Equipes b  ";
			$sql .= "Where a.Id_equipe = b.Id ";
			$sql .= "And b.Code_compet = 'POOL' ";
			$sql .= "And (a.Matric Like '%".ltrim($q, '0')."%' ";
			$sql .= "Or UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(b.Libelle) LIKE UPPER('%".$q."%') ";
			$sql .= ") ";
			$sql .= "Order By a.Nom, a.Prenom ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Pool arbitres : ".$sql);
			while ($row = mysql_fetch_assoc($result))
			{
				//$libelle = 'Pool Arbitres 1';
				$libelle = substr($row['Libelle'],0,3);
				$arb = strtoupper($row['Arb']);
				if($row['niveau'] != '')
					$arb .= '-'.$row['niveau'];
				$matric = $row['Matric'];
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				if(isset($row['Naissance'])) {
                    $naissance = $row['Naissance'];
                }
                if(isset($row['Sexe'])) {
                    $sexe = $row['Sexe'];
                }
				$resultGlobal .= "$nom $prenom ($libelle) $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
			// Autres arbitres
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "|XXX\n";
			$resultGlobal .= "---------- " . $lang['Autres_Arbitres'] . " ----------|XXX\n";
			$sql  = "Select lc.*, c.Libelle, b.Arb, b.niveau ";
				//$sql .= "From gickp_Liste_Coureur lc left outer join gickp_Arbitre b on lc.Matric = b.Matric, gickp_Club c ";
			$sql .= "From gickp_Liste_Coureur lc, gickp_Arbitre b, gickp_Club c ";
			$sql .= "Where lc.Matric = b.Matric ";
			$sql .= "And (lc.Matric Like '%".ltrim($q, '0')."%' ";
			$sql .= "Or UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER('%".$q."%') ";
			$sql .= ") And lc.Numero_club = c.Code ";
			$sql .= "Order by lc.Nom, lc.Prenom ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autres arbitres : ".$sql);
			while ($row = mysql_fetch_assoc($result))
			{
				$club = $row['Numero_club'];
				$libelle = mb_convert_case(strtolower($row['Libelle']), MB_CASE_TITLE, "UTF-8");
				//$arb = __encode(strtoupper($row['Arb']));
				$arb = strtoupper($row['Arb']);
				if ($row['niveau'] != '') {
                    $arb .= '-' . $row['niveau'];
                }
                $matric = $row['Matric'];
				//$nom = __encode(ucwords(strtolower($row['Nom'])));
				//$prenom = __encode(ucwords(strtolower($row['Prenom'])));
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				$naissance = $row['Naissance'];
				$sexe = $row['Sexe'];
				$resultGlobal .= "$nom $prenom ($libelle) $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
			//Résultat
		}

        echo $resultGlobal;


