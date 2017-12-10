<?php
include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();
$start1 = utyGetGet('start');
$end1 = utyGetGet('end');

$sql  = "SELECT j.*, c.* "
        ."FROM gickp_Journees j, gickp_Competitions c "
        ."WHERE j.Code_competition = c.Code "
        ."AND j.Code_saison = c.Code_saison "
        ."AND c.Publication = 'O' "
        ."AND j.Publication = 'O' "
        ."AND ((j.Date_debut >= '".$start1."' AND j.Date_debut <= '".$end1."') OR (j.Date_fin >= '".$start1."' AND j.Date_fin <= '".$end1."')) "
        //groupe les journées à date identique (format coupe)
        ."GROUP BY j.Code_saison, j.Code_competition, j.Date_debut, j.Date_fin, j.Lieu "
        ."ORDER BY j.Date_debut, c.Code_niveau, c.GroupOrder, c.Code_tour, j.Nom ";

$result = $myBdd->Query($sql);
$arrayCalendrier = array();
while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
    $title = html_entity_decode($row['Nom'].' ('.$row['Lieu'].'-'.$row['Departement'].')');
    $compet = $row['Code_competition'];
    //Couleurs selon le type de compétition (championnat, coupe, tournoi, compétition internationale, régionale)
    //$class = $compet[0].'class '.$compet[0].$compet[1].'class';
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
    //$datedebut = explode("-", $row['Date_debut']);
    //$datedebut = $datedebut[0].$datedebut[1].$datedebut[2];
    //$datejour = date('Ymd');
    $ts = strtotime($row['Date_fin']) + 86400;
    $datefin = date('Y-m-d', $ts);
    
    $Code_competition = $row['Code_competition'];
    $Group = $row['Code_ref'];
    $Saison = $row['Code_saison'];
    
    
//    $datefin = date_create($row['Date_fin']);
//    date_add($datefin, date_interval_create_from_date_string('1 day'));
//    $datefin = date_format($datefin, 'Y-m-d');
    
//    if ($datedebut < $datejour) {
//        $url = 'kpclassements.php?Compet=' . $row['Code_competition'] . '&Group=' . $row['Code_ref'] . '&Saison=' . $row['Code_saison'] . '&Dat=' . $row['Date_debut'] . $typ;
//    } else {
        //$url = 'Classements.php?Compet='.$row['Code_competition'].'&Group='.$row['Code_ref'].'&Saison='.$row['Code_saison'].'&Dat='.$row['Date_debut'].$typ;
        $url = "kpdetails.php?Compet=$Code_competition&Group=$Group&Saison=$Saison$typ";
//    }
        
    array_push($arrayCalendrier, array(	'id' => $row['Id'],
                                        'title' => $title,
                                        'start' => $row['Date_debut'],
                                        'end' => $datefin,
                                        'url' => $url,
                                        'className' => $class
                                    ));
}
echo json_encode($arrayCalendrier);
	

