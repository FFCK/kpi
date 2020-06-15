<?php
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');
session_start();

$journee = (int) utyGetGet('J', 0);
$Compet = utyGetSession('codeCompet', '');
$Compet = utyGetGet('Compet', $Compet);
$Saison = utyGetSession('Saison', 0);
$Saison = utyGetGet('Saison', $Saison);

// Export to ics
if ($journee > 0) {
    $myBdd = new MyBdd();
    $sql = "SELECT j.Code_competition, j.Code_saison, j.Id, j.Nom, j.Date_debut, 
        j.Date_fin, j.Lieu, j.Departement, c.Code_typeclt, c.Code_ref 
        FROM gickp_Journees j, gickp_Competitions c 
        WHERE j.Code_competition = c.Code 
        AND j.Code_saison = c.Code_saison 
        AND j.Publication = 'O' 
        AND j.Id = ? ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($journee));
    $row = $result->fetch();

    $title = html_entity_decode($row['Nom'].' ('.$row['Lieu'].'-'.$row['Departement'].')');
    $place = $row['Lieu'].' ('.$row['Departement'].')';
    $compet = $row['Code_competition'];
    $Group = $row['Code_ref'];
    $Saison = $row['Code_saison'];

    //Si c'est un mode championnat, on dirige vers la journée demandée, sinon toute la compétition
    if ($row['Code_typeclt'] == 'CHPT') {
        $typ = '&J='.$row['Id'];
        $typ2 = '%26J%3D'.$row['Id'];
    } else {
        $typ = '&typ=CP';
        $typ2 = '%26typ%3DCP';
    } 

    $datedebut = $row['Date_debut'];
    $datefin = $row['Date_fin'];
    // $ts = strtotime($row['Date_fin']) + 86400;
    // $datefin = date('Y-m-d', $ts);
    
    $url = "https://kayak-polo.info/kpdetails.php?Compet=$compet&Group=$Group&Saison=$Saison$typ";

    $url2 = "https%3A%2F%2Fwww.kayak-polo.info%2Fkpdetails.php";
    $url2 .= "%3FCompet%3D$compet%26Group%3D$Group%26Saison%3D$Saison%26$typ2";

    //Evenèment au format ICS
    $ics = "BEGIN:VCALENDAR\n";
    $ics .= "VERSION:2.0\n";
    $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
    $ics .= "BEGIN:VEVENT\n";
    $ics .= "DTSTART:".date('Ymd',strtotime($datedebut))."\n";
    $ics .= "DTEND:".date('Ymd',strtotime($datefin))."T170000Z\n";
    $ics .= "SUMMARY:".$title."\n";
    $ics .= "LOCATION:".$place."\n";
    $ics .= "DESCRIPTION:".$url."\n";
    $ics .= "URL:".$url2."\n";
    $ics .= "END:VEVENT\n";
    $ics .= "END:VCALENDAR\n";

    //Création du fichier
    $fp = fopen('php://output', 'w');
    if ($fp) {     
        header('Content-Type: text/ics; charset=utf-8');
        header('Content-Disposition: attachment; filename="event.ics"');
        header('Pragma: no-cache');    
        header('Expires: 0');
        fputs($fp, $ics);
        die();
    }

} elseif ($Compet != '' && $Saison > 0) {
    $myBdd = new MyBdd();
    $sql = "SELECT j.Code_competition, j.Code_saison, j.Id, j.Nom, j.Date_debut, 
        j.Date_fin, j.Lieu, j.Departement, c.Code_typeclt, c.Code_ref 
        FROM gickp_Journees j, gickp_Competitions c 
        WHERE j.Code_competition = c.Code 
        AND j.Code_saison = c.Code_saison 
        AND j.Publication = 'O' 
        AND j.Code_competition = ? 
        AND j.Code_saison = ? ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($Compet, $Saison));

    $ics = "BEGIN:VCALENDAR\n";
    $ics .= "VERSION:2.0\n";
    $ics .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";

    while ($row = $result->fetch()) {

        $title = html_entity_decode($row['Nom'].' ('.$row['Lieu'].'-'.$row['Departement'].')');
        $place = $row['Lieu'].' ('.$row['Departement'].')';
        $compet = $row['Code_competition'];
        $Group = $row['Code_ref'];
        $Saison = $row['Code_saison'];

        //Si c'est un mode championnat, on dirige vers la journée demandée, sinon toute la compétition
        if ($row['Code_typeclt'] == 'CHPT') {
            $typ = '&J='.$row['Id'];
            $typ2 = '%26J%3D'.$row['Id'];
        } else {
            $typ = '&typ=CP';
            $typ2 = '%26typ%3DCP';
        } 
    
        $datedebut = $row['Date_debut'];
        $datefin = $row['Date_fin'];
        // $ts = strtotime($row['Date_fin']) + 86400;
        // $datefin = date('Y-m-d', $ts);
            
        $url = "https://kayak-polo.info/kpdetails.php?Compet=$compet&Group=$Group&Saison=$Saison$typ";

        $url2 = "https%3A%2F%2Fwww.kayak-polo.info%2Fkpdetails.php";
        $url2 .= "%3FCompet%3D$compet%26Group%3D$Group%26Saison%3D$Saison%26$typ2";

        //Evenèment au format ICS
        $ics .= "BEGIN:VEVENT\n";
        $ics .= "DTSTART:".date('Ymd',strtotime($datedebut))."\n";
        $ics .= "DTEND:".date('Ymd',strtotime($datefin))."T170000Z\n";
        $ics .= "SUMMARY:".$title."\n";
        $ics .= "LOCATION:".$place."\n";
        $ics .= "DESCRIPTION:".$url."\n";
        $ics .= "URL:".$url2."\n";
        $ics .= "END:VEVENT\n";

        if ($row['Code_typeclt'] == 'CP') {
            break;
        }

    }

    $ics .= "END:VCALENDAR\n";

    //Création du fichier
    $fp = fopen('php://output', 'w');
    if ($fp) {     
        header('Content-Type: text/ics; charset=utf-8');
        header('Content-Disposition: attachment; filename="event.ics"');
        header('Pragma: no-cache');    
        header('Expires: 0');
        fputs($fp, $ics);
        die();
    }
}