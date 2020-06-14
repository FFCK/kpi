<?php
// include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();
$start = utyGetGet('start');
$end = utyGetGet('end');

//groupe les journées à date  et lieu identiques (format coupe)
$sql = "SELECT j.*, c.* 
    FROM gickp_Journees j, gickp_Competitions c 
    WHERE j.Code_competition = c.Code 
    AND j.Code_saison = c.Code_saison 
    AND c.Publication = 'O' 
    AND j.Publication = 'O' 
    AND ((j.Date_debut >= :start AND j.Date_debut <= :end) 
    OR (j.Date_fin >= :start AND j.Date_fin <= :end)) 
    GROUP BY j.Code_saison, j.Code_competition, j.Date_debut, j.Date_fin, j.Lieu 
    ORDER BY j.Date_debut, c.Code_niveau, c.GroupOrder, c.Code_tour, j.Nom ";

$arrayCalendrier = array();
//Couleurs selon le type de compétition (championnat, coupe, tournoi, compétition internationale, régionale)
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(
    ':start' => $start, 
    ':end' => $end
));
while ($row = $result->fetch()) { 
$title = html_entity_decode($row['Nom'].' ('.$row['Lieu'].'-'.$row['Departement'].')');
    $compet = $row['Code_competition'];
    $class = "bg-blue2";
    if($row['Code_niveau'] == 'INT'){
        $class = 'bg-brown';
    }elseif($row['Code_niveau'] == 'REG'){
        $class = 'bg-green';
    }elseif($compet[0] == 'N'){
        $class = "bg-blue";
    }
    //Si c'est un mode championnat, on dirige vers la journée demandée, sinon toute la compétition
    ($row['Code_typeclt'] == 'CHPT') ? $typ = '&J='.$row['Id'] : $typ = '&typ=CP';
    //Si la compétition est passée, on dirige vers le classement, sinon les matchs
    $ts = strtotime($row['Date_fin']) + 86400;
    $datefin = date('Y-m-d', $ts);
    
    $Code_competition = $row['Code_competition'];
    $Group = $row['Code_ref'];
    $Saison = $row['Code_saison'];
    
    
    $url = "kpdetails.php?Compet=$Code_competition&Group=$Group&Saison=$Saison$typ";
        
    array_push($arrayCalendrier, array(	
        'id' => $row['Id'],
        'title' => $title,
        'start' => $row['Date_debut'],
        'end' => $datefin,
        'url' => $url,
        'className' => $class
    ));
}

echo json_encode($arrayCalendrier);
