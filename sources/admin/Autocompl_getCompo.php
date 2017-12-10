<?php
session_start();
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Langue
$langue = parse_ini_file("../commun/MyLang.ini", true);
if (utyGetSession('lang') == 'en') {
    $lang = $langue['en'];
} else {
    $lang = $langue['fr'];
}
        
    $myBdd = new MyBdd();
	
	// Chargement
        $codeCompet = utyGetSession('codeCompet');
        if(substr($codeCompet, 0, 1) != 'N' && substr($codeCompet, 0, 2) != 'CF') {
            $codeSaison = $myBdd->RealEscapeString(trim(utyGetGet('s')));
            $codeSaison2 = $codeSaison - 1;
            $q = $myBdd->RealEscapeString(trim(utyGetGet('q')));

            $sql  = "SELECT ce.Code_saison, ce.Code_compet "
                    . "FROM `gickp_Competitions_Equipes` ce, `gickp_Competitions_Equipes_Joueurs` cej "
                    . "WHERE ce.Id = cej.Id_equipe "
                    . "AND (ce.Code_saison = $codeSaison OR ce.Code_saison = $codeSaison2) "
                    . "AND ce.Numero = ".$q." "
                    . "GROUP BY ce.Code_compet, ce.Code_saison "
                    . "ORDER BY ce.Code_saison DESC, ce.Code_compet ";

            $result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_getCompo : ".$sql);
            //$num_results = mysql_num_rows($result);
            //header('Content-Type: application/json; charset=ISO-8859-1');
            //$response = array();
            echo "<input type='radio' name='checkCompo' value='' checked /><i>" . $lang['Aucune_reprise'] . "</i><br>";
            while ($row = mysql_fetch_assoc($result)) {
                $Code_saison = $row['Code_saison'];
                $Code_compet = $row['Code_compet'];
                echo "<input type='radio' name='checkCompo' value='$Code_saison-$Code_compet'/>$Code_saison - $Code_compet<br />";
                //$response[] = array("$code - $libelle", $code, $libelle);
                //echo "$code - $libelle|$code|$libelle|$Code_niveau|$Code_ref|<br>\n";
            }
        } else {
            echo '<br>-> Pas de reprise des feuilles de présences sur Championnat de France et Coupe de France<br>';
        }