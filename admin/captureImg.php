<?php
$debutTraitement = time();

include_once('../commun/MyBdd.php');
$myBdd = new MyBdd();

$sql = "SELECT Code, Code_saison, BandeauLink, LogoLink, SponsorLink "
        . "FROM gickp_Competitions "
        . "ORDER BY Code_saison, Code ";
$result = $myBdd->Query($sql);

$x = 0;
$msg = '';
while ($row = $myBdd->FetchArray($result)) {
    $change = FALSE;
    $code = $row['Code'];
    $saison = $row['Code_saison'];
    
    $bandeauLink = $row['BandeauLink'];
    if($bandeauLink != '' && $bandeauLink2 = $myBdd->captureImg($bandeauLink, 'B', $code, $saison)) {
        $bandeauLink = $bandeauLink2;
        $msg .= $bandeauLink . "  &nbsp;&nbsp;&nbsp;<=&nbsp;&nbsp;&nbsp;" . $row['BandeauLink'] . "<br>";
        $change = TRUE;
    }		
    $logoLink = $row['LogoLink'];
    if($logoLink != '' && $logoLink2 = $myBdd->captureImg($logoLink, 'L', $code, $saison)) {
        $logoLink = $logoLink2;
        $msg .= $logoLink . "  &nbsp;&nbsp;&nbsp;<=&nbsp;&nbsp;&nbsp;" . $row['LogoLink'] . "<br>";
        $change = TRUE;
    }
    $sponsorLink = $row['SponsorLink'];
    if($sponsorLink != '' && $sponsorLink2 = $myBdd->captureImg($sponsorLink, 'S', $code, $saison)) {
        $sponsorLink = $sponsorLink2;
        $msg .= $sponsorLink . "  &nbsp;&nbsp;&nbsp;<=&nbsp;&nbsp;&nbsp;" . $row['SponsorLink'] . "<br>";
        $change = TRUE;
    }
    
    if($change === TRUE) {
        $sql2 = "UPDATE gickp_Competitions "
                . "SET BandeauLink = '" . $bandeauLink . "', "
                . "LogoLink = '" . $logoLink . "', "
                . "SponsorLink = '" . $sponsorLink . "' "
                . "WHERE Code = '" . $code . "' "
                . "AND Code_saison = " . $saison . " ";
        $result2 = $myBdd->Query($sql2);
        $x ++;
//        $msg .= "Update $code $saison OK<br>";
    }
}

$msg .= "<br><br>" . $x . " compétitions modifiées.<br>";
$tempsIntermediaire = time() - $debutTraitement;
$msg .= "Traitement terminé en " . $tempsIntermediaire . "s.";
echo $msg;
exit;