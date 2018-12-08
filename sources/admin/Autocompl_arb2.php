<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

	session_start();

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
		$j = utyGetSession('sessionJournee','');
		$m = utyGetSession('sessionMatch','');
		$m = (int)utyGetGet('sessionMatch',$m);
		$q = $myBdd->RealEscapeString(trim(utyGetGet('q')));
		$q = preg_replace('`^[0]*`','',$q);
		$resultGlobal = '';
		
		if($j == '' && ($m == '' || $m == 0))
			$resultGlobal .= "Selectionnez une journee / une phase !|XXX|||\n";
		else
		{
			// Equipes
			$resultGlobal .= "------------- Equipes engagées -------------\n";
			$sql  = "SELECT a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet "
                    . "FROM gickp_Competitions_Equipes a, gickp_Journees b "
                    . "WHERE a.Code_compet = b.Code_competition "
                    . "AND a.Code_saison = b.Code_saison ";
			if($j != '')
				$sql .= "AND b.Id = ".$j." ";
			$sql .= "AND UPPER(a.Libelle) LIKE UPPER('%".$q."%') "
                    . "GROUP BY a.Libelle "
                    . "ORDER BY a.Poule, a.Tirage, a.Libelle ";	 
            $result = $myBdd->Query($sql);
            while($row = $myBdd->FetchAssoc($result)) {
				//$libelle = __encode($row['Libelle']);
				$libelle = $row['Libelle'];
				$matric = '';
				$resultGlobal .= "$libelle|$matric|$libelle|||\n";
			}
			// Joueurs
			$resultGlobal .= ".\n";
			$resultGlobal .= "----------------- Joueurs -----------------\n";
			$sql  = "SELECT distinct a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau "
                    . "FROM gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, "
                    . "gickp_Competitions_Equipes b, gickp_Journees d, gickp_Matchs e "
                    . "WHERE a.Id_equipe = b.Id "
                    . "AND b.Code_compet = d.Code_competition "
                    . "AND b.Code_saison = d.Code_saison "
                    . "AND d.Id = e.Id_journee ";
			if($j != '')
				$sql .= "AND d.Id = ".$j." ";
			elseif($m != '')
				$sql .= "AND e.Id = ".$m." ";
			$sql .= "AND (a.Matric Like '%".ltrim($q, '0')."%' "
                    . "OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(b.Libelle) LIKE UPPER('%".$q."%') "
                    . ") "
                    . "ORDER BY b.Libelle, a.Nom, a.Prenom ";
            $result = $myBdd->Query($sql);
            while($row = $myBdd->FetchAssoc($result)) {
				$libelle = __encode($row['Libelle']);
				$arb = strtoupper($row['Arb']);
				if($row['niveau'] != '')
					$arb .= '-'.$row['niveau'];
				$matric = $row['Matric'];
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				$resultGlobal .= "($libelle) $nom $prenom $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
			// Pool
			$resultGlobal .= ".\n";
			$resultGlobal .= ".\n";
			$resultGlobal .= ".\n";
			$resultGlobal .= "----- Pool arbitres (hors équipes engagées) -----\n";
			$sql  = "SELECT a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau "
                    . "FROM gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, gickp_Competitions_Equipes b  "
                    . "WHERE a.Id_equipe = b.Id "
                    . "AND b.Code_compet = 'POOL' "
                    . "AND (a.Matric Like '%".ltrim($q, '0')."%' "
                    . "OR UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(b.Libelle) LIKE UPPER('%".$q."%') "
                    . ") "
                    . "ORDER BY a.Nom, a.Prenom ";
            $result = $myBdd->Query($sql);
            while($row = $myBdd->FetchAssoc($result)) {
				//$libelle = 'Pool Arbitres 1';
				$libelle = substr($row['Libelle'],0,3);
				$arb = strtoupper($row['Arb']);
				if($row['niveau'] != '')
					$arb .= '-'.$row['niveau'];
				$matric = $row['Matric'];
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				$naissance = $row['Naissance'];
				$sexe = $row['Sexe'];
				$resultGlobal .= "$nom $prenom ($libelle) $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
			// Autres arbitres
			$resultGlobal .= ".\n";
			$resultGlobal .= "----- Autres arbitres (hors équipes engagées) -----\n";
			$sql  = "SELECT lc.*, c.Libelle, b.Arb, b.niveau "
                    . "FROM gickp_Liste_Coureur lc, gickp_Arbitre b, gickp_Club c "
                    . "WHERE lc.Matric = b.Matric "
                    . "AND (lc.Matric Like '%".ltrim($q, '0')."%' "
                    . "OR UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER('%".$q."%') "
                    . ") AND lc.Numero_club = c.Code "
                    . "ORDER BY lc.Nom, lc.Prenom ";
            $result = $myBdd->Query($sql);
            while($row = $myBdd->FetchAssoc($result)) {
				$club = $row['Numero_club'];
				$libelle = mb_convert_case(strtolower($row['Libelle']), MB_CASE_TITLE, "UTF-8");
				$arb = strtoupper($row['Arb']);
				if($row['niveau'] != '')
					$arb .= '-'.$row['niveau'];
				$matric = $row['Matric'];
				$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
				$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
				$naissance = $row['Naissance'];
				$sexe = $row['Sexe'];
				$resultGlobal .= "$nom $prenom ($libelle) $arb|$matric|$nom|$prenom|$libelle|$arb\n";
			}
			//Résultat
		}
		//echo $resultGlobal;
	echo $resultGlobal;

