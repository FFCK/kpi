<?php
/* 
 * Parser un fichier XML
 * 
 */
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');


// Formulaire
if (count($_FILES) === 0) {
    echo '<html>
            <body>
                <!-- The data encoding type, enctype, MUST be specified as below -->
                <h1>Import SDP xml file</h1>
                <form enctype="multipart/form-data" action="" method="POST">
                    <!-- MAX_FILE_SIZE must precede the file input field -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                    <!-- Name of input element determines name in $_FILES array -->
                    Send this file: <input name="userfile" type="file" />
                    <br>
                    <input type="checkbox" value="1" name="updateDB"> MAJ BDD
                    <br>
                    <br>
                    <input type="submit" value="Send File" />
                </form>
            </body>
          </html>
            ';
    exit();
}

/**
 * Upload fichier
 */
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
    // var_dump($finfo->file($_FILES['userfile']['tmp_name']));
    if (false === $ext = array_search(
        $finfo->file($_FILES['userfile']['tmp_name']),
        array(
            //            'jpg' => 'image/jpeg',
            //            'png' => 'image/png',
            //            'gif' => 'image/gif',
            'xml' => 'text/xml'
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data or name.
    $fileName = sprintf(
        './uploads/%s.%s',
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

/**
 * Constantes
 */
const SAISON = 2022;
const COMPETITION_CODE = 1507; // WC 2022
const COMPETITION_LABEL = "Championnats du monde 2022"; // WC 2022
// Event 1 = U21 M
// Event 2 = U21 W
// Event 3 = Senior M
// Event 4 = Senior W
$eventArray = ['001' => 'CMH21', '002' => 'CMF21', '003' => 'CMH', '004' => 'CMF'];
$catArray = ['CMH21' => 'U21 MEN', 'CMF21' => 'U21 WOMEN', 'CMH' => 'SENIOR MEN', 'CMF' => 'SENIOR WOMEN'];
$resultArray = [];
$updateDB = utyGetPost('updateDB', 0);
$inserted = 0;
$updated = 0;
$teamInserted = 0;
$teamUpdated = 0;
$orphans = 0;

/**
 * Traitement fichier
 */
header('Content-Type: text/html; charset=utf-8');

if (!file_exists($fileName)) {
    exit('Echec lors de l\'ouverture du fichier .xml.');
}

$xml = simplexml_load_file($fileName);

$xmlDocumentType =  $xml['DocumentType']; // DT_PARTIC_TEAMS, DT_PARTIC

// Compétiteurs
if ($xmlDocumentType == 'DT_PARTIC') {

    $myBdd = new MyBdd();
    $listIcf = [];
    $matricIcf = [];
    $sql = "SELECT DISTINCT(Reserve), Nom, Prenom, Naissance, Matric 
        FROM kp_licence 
        WHERE Reserve != '0' 
        AND Reserve IS NOT NULL ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $listIcf[] = $row['Reserve'];
        $matricIcf[$row['Reserve']] = $row['Matric'];
    }
    $teams = [];
    $sql = "SELECT Code_compet, Id, Code_club 
        FROM kp_competition_equipe 
        WHERE Code_saison =  ? 
        AND Code_compet IN ('CMH', 'CMF', 'CMH21', 'CMF21') ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute([SAISON]);
    while ($row = $stmt->fetch()) {
        $teams[$row['Code_compet']][$row['Code_club']] = $row['Id'];
    }


    $sql_insert = "INSERT INTO kp_licence (Matric, Origine, Nom, Prenom, 
        Sexe, Naissance, Numero_club, Numero_comite_dept, Numero_comite_reg, Reserve)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, '98', ?) ";
    $stmt_insert = $myBdd->pdo->prepare($sql_insert);

    $sql_update = "UPDATE kp_licence 
        SET Origine = ?, Nom = ?, Prenom = ?, 
        Sexe = ?, Naissance = ?, Numero_club = ?, Numero_comite_dept = ?, Numero_comite_reg = '98'
        WHERE Reserve = ? 
        AND Matric > 2000000 ";
    $stmt_update = $myBdd->pdo->prepare($sql_update);

    $sql_insert_team = "INSERT INTO kp_competition_equipe_joueur 
        (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero)
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            Id_equipe = VALUES(Id_equipe), Matric = VALUES(Matric), 
            Nom = VALUES(Nom), Prenom = VALUES(Prenom), 
            Sexe = VALUES(Sexe), Categ = VALUES(Categ), 
            Numero = VALUES(Numero) 
        ";
    $stmt_insert_team = $myBdd->pdo->prepare($sql_insert_team);

    $sql_update_team = "INSERT INTO kp_competition_equipe_joueur 
        (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero)
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            Id_equipe = VALUES(Id_equipe), Matric = VALUES(Matric), 
            Nom = VALUES(Nom), Prenom = VALUES(Prenom), 
            Sexe = VALUES(Sexe), Categ = VALUES(Categ), 
            Numero = VALUES(Numero) 
        ";
    $stmt_update_team = $myBdd->pdo->prepare($sql_update_team);

    $xmlSaison = (int) substr($xml['Date'], 0, 4);
    echo "Import saison " . $xmlSaison . "<br>";
    echo "Mise à jour BDD ? ";
    echo utyGetPost('updateDB', 0) ? "OUI" : "NON";
    echo "<br>";

    foreach ($xml->Competition as $competition) {
        if ((int) $competition['Code'] !== COMPETITION_CODE) {
            echo "Mauvais code compétition !!<br>";
            return;
        } else {
            echo "Import " . COMPETITION_LABEL . ".<br>";
        }
        echo '<hr>';

        foreach ($competition->Participant as $participant) {
            $event = $eventArray[(string) $participant->Discipline->RegisteredEvent['Event']];
            $team = (string) $participant['Organisation'];
            $icf = (int) $participant['Code'];
            $nom = (string) $participant['FamilyName'];
            $prenom = (string) $participant['GivenName'];
            $sexe = ((string) $participant['Gender'] === 'W') ? 'F' : 'M';
            $birthdate = (string) $participant['BirthDate'];
            $naissance = substr($birthdate, 0, 4) . '-' . substr($birthdate, 4, 2) . '-' . substr($birthdate, 6, 2);
            $taille = (int) $participant['Height'];
            $poids = (int) $participant['Weight'];
            $num = (int) $participant->Discipline->RegisteredEvent->EventEntry['Pos'];
            if (in_array($icf, $listIcf)) {
                $exists = true;
                $matric = $matricIcf[$icf];
            } else {
                $exists = false;
                $matric = 0;
            }
            $exists = (in_array($icf, $listIcf)) ? true : false;
            if (isset($teams[$event][$team . '00'])) {
                $teamId = $teams[$event][$team . '00'];
            } else {
                $teamId = 0;
            }

            $resultArray[$event][$team][$icf] = [
                'icf' => $icf,
                'nom' => $nom,
                'prenom' => $prenom,
                'sexe' => $sexe,
                'naissance' => $naissance,
                'taille' => $taille,
                'poids' => $poids,
                'num' => $num,
                'teamId' => $teamId,
                'exists' => $exists,
                'matric' => $matric
            ];
        }
    }

    echo 'Country;KPICat;Cat;KPITeamId;KPIId;IcfId;FamilyName;FirstName;Gender;Birthdate;Height;Weigth;Number;KPILicenceDB;KPIRosterDB;Fonction';
    echo '<br>';
    foreach ($resultArray as $cat => $arrayCat) {
        foreach ($arrayCat as $team => $arrayTeam) {
            foreach ($arrayTeam as $key3 => $arrayPlayer) {
                echo $team;
                echo ';' . $cat;
                echo ';' . $catArray[$cat];
                echo ';' . $arrayPlayer['teamId'];
                echo ';' . $arrayPlayer['matric'];
                echo ';' . $arrayPlayer['icf'];
                echo ';' . $arrayPlayer['nom'];
                echo ';' . $arrayPlayer['prenom'];
                echo ';' . $arrayPlayer['sexe'];
                echo ';' . $arrayPlayer['naissance'];
                echo ';' . $arrayPlayer['taille'];
                echo ';' . $arrayPlayer['poids'];
                echo ';' . $arrayPlayer['num'];

                if ($arrayPlayer['exists']) {
                    if ($updateDB) {
                        $stmt_update->execute([
                            SAISON, $arrayPlayer['nom'], $arrayPlayer['prenom'], $arrayPlayer['sexe'],
                            $arrayPlayer['naissance'], $team . '00', $team, $arrayPlayer['icf']
                        ]);
                        echo ';' . 'Updated';
                        $updated++;
                        if ($arrayPlayer['teamId'] > 0) {
                            $categorie = utyCodeCategorie2($arrayPlayer['naissance'], SAISON);
                            $stmt_update_team->execute([
                                $arrayPlayer['teamId'], $arrayPlayer['matric'],
                                $arrayPlayer['nom'], $arrayPlayer['prenom'],
                                $arrayPlayer['sexe'], $categorie,
                                $arrayPlayer['num']
                            ]);
                            echo ';' . 'Updated';
                            $teamUpdated++;
                        } else {
                            echo ';' . 'No team';
                            $orphans++;
                        }
                    } else {
                        echo ';' . 'To update';
                        echo ';' . 'To update';
                    }
                } else {
                    if ($updateDB) {
                        $nextMatric = $myBdd->GetNextMatricLicence();
                        $stmt_insert->execute([
                            $nextMatric, SAISON, $arrayPlayer['nom'], $arrayPlayer['prenom'], $arrayPlayer['sexe'],
                            $arrayPlayer['naissance'], $team . '00', $team, $arrayPlayer['icf']
                        ]);
                        echo ';' . 'Inserted';
                        $inserted++;
                        if ($arrayPlayer['teamId'] > 0) {
                            $categorie = utyCodeCategorie2($arrayPlayer['naissance'], SAISON);
                            $stmt_insert_team->execute([
                                $arrayPlayer['teamId'], $nextMatric,
                                $arrayPlayer['nom'], $arrayPlayer['prenom'],
                                $arrayPlayer['sexe'], $categorie,
                                $arrayPlayer['num']
                            ]);
                            echo ';' . 'Inserted';
                            $teamInserted++;
                        } else {
                            echo ';' . 'No team';
                            $orphans++;
                        }
                    } else {
                        echo ';' . 'To insert';
                        echo ';' . 'To insert';
                    }
                }
                echo ';Athlete';
                echo '<br>';
            }
        }
    }

    echo '<hr>';
    echo 'Licences ajoutées : ' . $inserted . '<br>';
    echo 'Licences mises à jour : ' . $updated . '<br>';
    echo 'Titulaires ajoutés : ' . $teamInserted . '<br>';
    echo 'Titulaires mis à jour : ' . $teamUpdated . '<br>';
    echo 'Orphelins : ' . $orphans . '<br>';
    // echo '<hr><pre>';
    // print_r($resultArray);
    // echo '</pre>';
}

// echo '<hr><pre>';
// print_r($xml);
// echo '</pre>';

echo '<hr><pre>'
    . '<a href="">Back</a>';

unlink($fileName);
