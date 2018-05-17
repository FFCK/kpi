<?php
/* 
 * Parser un fichier XML
 * 
 */
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');


// Formulaire
if(count($_FILES) === 0) {
    echo '<html>
            <body>
                <!-- The data encoding type, enctype, MUST be specified as below -->
                <h1>Import SDP xml file</h1>
                <form enctype="multipart/form-data" action="" method="POST">
                    <!-- MAX_FILE_SIZE must precede the file input field -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                    <!-- Name of input element determines name in $_FILES array -->
                    Send this file: <input name="userfile" type="file" />
                    <input type="submit" value="Send File" />
                </form>
            </body>
          </html>
            ';
    exit();
}

// Upload
try {
   
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['userfile']['error']) ||
        is_array($_FILES['userfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['userfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit : 1000000');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['userfile']['size'] > 1100000) {
        throw new RuntimeException('Exceeded filesize limit : 1100000');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['userfile']['tmp_name']),
        array(
//            'jpg' => 'image/jpeg',
//            'png' => 'image/png',
//            'gif' => 'image/gif',
            'xml' => 'application/xml'
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data or name.
    $fileName = sprintf('./uploads/%s.%s',
            sha1($_FILES['userfile']['tmp_name']),
            $ext
        );
    if (!move_uploaded_file(
        $_FILES['userfile']['tmp_name'],
        $fileName
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }
//    echo 'File is uploaded successfully.';
    
} catch (RuntimeException $e) {

    echo $e->getMessage();
    exit();
}

// Traitement
header('Content-Type: text/html; charset=utf-8');

if (!file_exists($fileName)) {
    exit('Echec lors de l\'ouverture du fichier .xml.');
}
    
$xml = simplexml_load_file($fileName);

// echo '<pre>';
// print_r($xml);
// echo '</pre>';
// exit();
    
$xmlDocumentType =  $xml['DocumentType']; // DT_PARTIC_TEAMS, DT_PARTIC

$myBdd = new MyBdd();
$listIcf = [];
$nomsIcf = [];
$matricIcf = [];
$sql  = "SELECT DISTINCT(Reserve), Nom, Prenom, Naissance, Matric "
        . "FROM gickp_Liste_Coureur "
        . "WHERE Reserve != '0' "
        . "AND Reserve IS NOT NULL "
        . "AND Matric > 2000000 ";
$result = $myBdd->Query($sql);
while($row = $myBdd->FetchAssoc($result)) {
    $listIcf[] = $row['Reserve'];
    $nomsIcf[$row['Reserve']] = $row['Nom'] . ' ' . $row['Prenom'] . ' - ' . $row['Naissance'];
    $matricIcf[$row['Reserve']] = $row['Matric'];
}

// Compétiteurs
if ($xmlDocumentType == 'DT_PARTIC') {
    $xmlSaison = substr($xml['Date'], 0, 4);
    
    foreach ($xml->Competition as $competition) {
        foreach ($competition->Participant as $participant) {
            $participant['BirthDate'] = substr($participant['BirthDate'], 0, 4)
                    .'-'.substr($participant['BirthDate'], 4, 2)
                    .'-'.substr($participant['BirthDate'], 6, 2);
            $participant['Club'] = $participant['Organisation'] . '00';
            
//            echo $participant[FamilyName] 
//            . ' ' . $participant[GivenName] 
//            . ' ' . $participant[BirthDate] 
//            . ' - ' . $participant[Club].'<br>';

            // Le compétiteur existe déjà
            if(in_array($participant['Code'], $listIcf)) {
                $sql  = "UPDATE gickp_Liste_Coureur "
                        . "SET Origine = $xmlSaison, "
                        . "Nom = '" . $participant['FamilyName'] . "', "
                        . "Prenom = '" . $participant['GivenName'] . "', "
                        . "Sexe = '" . $participant['Gender'] . "', "
                        . "Naissance = '" . $participant['BirthDate'] . "', "
                        . "Numero_club = '" . $participant['Club'] . "', "
                        . "Numero_comite_dept = '" . $participant['Organisation'] . "', "
                        . "Numero_comite_reg = '98' "
                        . "WHERE Reserve = '" . $participant['Code'] . "' "
                        . "AND Matric > 2000000 ";
                $result = $myBdd->Query($sql);
                $sql  = "UPDATE gickp_Competitions_Equipes_Joueurs "
                        . "SET Nom = '" . $participant['FamilyName'] . "', "
                        . "Prenom = '" . $participant['GivenName'] . "', "
                        . "Sexe = '" . $participant['Gender'] . "' "
                        . "WHERE Matric =  " . $matricIcf[(int) $participant['Code']] . " ";
                $result = $myBdd->Query($sql);
                echo '* ' . $participant['Organisation'] . ' - ' . $participant['TVName'] .' => Updated<br>';
                
            // Le compétiteur n'existe pas encore
            } else {
                $matricJoueur = $myBdd->GetNextMatricLicence();
                $sql  = "INSERT INTO gickp_Liste_Coureur "
                        . "(Matric, Origine, Nom, Prenom, Sexe, Naissance, Numero_club, Numero_comite_dept, Numero_comite_reg, Reserve) "
                        . "VALUES($matricJoueur, "
                        . "$xmlSaison, "
                        . "'" . $participant['FamilyName'] . "', "
                        . "'" . $participant['GivenName'] . "', "
                        . "'" . $participant['Gender'] . "', "
                        . "'" . $participant['BirthDate'] . "', "
                        . "'" . $participant['Club'] . "', "
                        . "'" . $participant['Organisation'] . "', "
                        . "'98', "
                        . "'" . $participant['Code'] . "') ";
                $result = $myBdd->Query($sql);
                echo '* ' . $participant['Organisation'] . ' - ' . $participant['TVName'] .' => Inserted<br>';
            }
        }
    }
    

// Equipes
} elseif ($xmlDocumentType == 'DT_PARTIC_TEAMS') {
    $fileDate = substr($xml['Date'], 0, 4)
        .'-'.substr($xml['Date'], 4, 2)
        .'-'.substr($xml['Date'], 6, 2);
    echo 'File date : ' . $fileDate . '<br><br>';
    
    foreach ($xml->Competition as $competition) {
        echo '<h3>Competition: '. $competition['Code'] . '</h3><ol>';
        foreach ($competition->Team as $team) {
            echo '<li>' . $team['Organisation'] 
                    . ' (' . $team['Name'] 
                    . ' ' . $team['Gender'] . ') '
                    . 'Event = ' . $team->Discipline->RegisteredEvent['Event']
                    . '<ul>';
            foreach ($team->Composition->Athlete as $athlete) {
                echo '<li>' . $athlete['Order'] . ' - ' . $nomsIcf[(int) $athlete['Code']] . ' (' . $athlete['Code'] .')</li>';
            }
            echo '<br></ul>';
        } 
        echo '</ol>';
    }
}

echo '<hr>'
    . '<a href="">Back</a>';
