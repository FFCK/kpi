<?php
/* 
 * Parser un fichier XML
 * 
 */

header('Content-Type: text/html; charset=utf-8');

// chemin/fichier.xml
$file = 'uploads/ODF_DT_PARTIC_TEAMS_1360.xml';
//$file = 'uploads/test.xml';

if (!file_exists($file)) {
    exit('Echec lors de l\'ouverture du fichier test.xml.');
}
    
$xml = simplexml_load_file($file);

$xmlDocumentType =  $xml['DocumentType']; // DT_PARTIC_TEAMS, DT_PARTIC

if($xmlDocumentType == 'DT_PARTIC') {
    $xmlSaison = substr($xml['Date'], 0, 4);
    
    foreach ($xml->Competition as $competition) {

        //       echo '-> '. $competition[Code] . '<br>';
       foreach ($competition->Participant as $participant) {
           //       echo '<pre>' . print_r($participant) . '</pre>';
           echo '* ' . $participant[TVName] .'<br>';
       } 
       echo '---';
    }
    
    echo $xmlSaison;
}

if($xmlDocumentType == 'DT_PARTIC_TEAMS') {
    echo 'File date : ' . $xml['Date'] . '<br><br>';
    
    foreach ($xml->Competition as $competition) {
        echo '<h3>-> '. $competition['Code'] . '</h3><ol>';
        foreach ($competition->Team as $team) {
            echo '<li>' . $team['Organisation'] 
                    . ' (' . $team['Name'] 
                    . ' ' . $team['Gender'] . ') '
                    . 'Event = ' . $team->Discipline->RegisteredEvent['Event']
                    . '<ul>';
            foreach ($team->Composition->Athlete as $athlete) {
                echo '<li>#' . $athlete['Order'] . ' - ICF #' . $athlete['Code'] .'</li>';
            }
            echo '</ul>';
        } 
        echo '</ol>';
    }
    
}


echo '<hr>'
    . '<pre>';
print_r($xml);
echo '</pre>';
/* Pour chaque <character>, nous affichons un <name>. */
