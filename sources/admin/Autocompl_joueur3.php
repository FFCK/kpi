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
        $q = utyGetGet('q');
        $q = preg_replace('`^[0]*`','',$q);
        
        if (strlen($q) < 2){
            echo 'Trop court...';
            return;
        }
        
        $Profile = utyGetSession('Profile');
        $Limit_Clubs = utyGetSession('Limit_Clubs', '0000');
        $Limit_Clubs_2 = str_replace(",", "','", $Limit_Clubs);
        
        $sql  = "SELECT lc.*, c.Libelle, s.Date date_surclassement "
                . "FROM gickp_Liste_Coureur lc "
                . "LEFT OUTER JOIN gickp_Surclassements s ON (lc.Matric = s.Matric AND s.Saison = ".  utyGetSaison() ."), "
                . "gickp_Club c "
                . "WHERE (lc.Matric Like '%".ltrim($q, '0')."%' "
                . "OR UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER('%".$q."%') "
                . "OR UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER('%".$q."%') "
                . ") AND lc.Numero_club = c.Code ";
        if($Profile >= 7) {
            $sql .= "AND lc.Numero_club IN ('".$Limit_Clubs_2."') ";
        }
        $sql .= "Order by lc.Nom, lc.Prenom ";
	
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_joueur : ".$sql);
	while ($row = mysql_fetch_assoc($result)) {
		$club = $row['Numero_club'];
		$libelle = $row['Libelle'];
		$matric = $row['Matric'];
		$nom = __encode($row['Nom']);
		$prenom = __encode($row['Prenom']);
		$nom2 = __encode(ucwords(strtolower($row['Nom'])));
		$prenom2 = __encode(ucwords(strtolower($row['Prenom'])));
		$naissance = $row['Naissance'];
		$sexe = $row['Sexe'];
		$origine = $row['Origine'];
		$pagaie_ECA = $row['Pagaie_ECA'];
		$pagaie_EVI = $row['Pagaie_EVI'];
		$pagaie_MER = $row['Pagaie_MER'];
        $pagaies = array('', 'PAGB', 'PAGJ');
        if(in_array($pagaie_ECA, $pagaies)) { //si pas de pagaie verte ECA (ou plus)
            if(in_array($pagaie_EVI, $pagaies)) { // si pas de pagaie verte EVI (ou plus)
                if(!in_array($pagaie_MER, $pagaies)) { // si une pagaie verte MER (ou plus)
                    $pagaie_ECA = 'PAGV'; // sinon ECA est au moins verte
                }
            } else {
                $pagaie_ECA = 'PAGV'; // sinon ECA est au moins verte
            }
        }
        
		$certificat_CK = $row['Etat_certificat_CK'];
		$certificat_APS = $row['Etat_certificat_APS'];
        $date_surclassement = utyDateUsToFr($row['date_surclassement']);
        $return = "$matric - $nom $prenom ($club - $libelle)|$matric|$nom|$prenom|$naissance|$sexe|$nom2|$prenom2|$origine|$pagaie_ECA|$certificat_CK|$certificat_APS|$libelle|$date_surclassement\n";
        echo $return;
	}
