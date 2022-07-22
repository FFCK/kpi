<?php
/* 
 * Parser un fichier CSV
 * 
 */
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');


// Formulaire
if (count($_FILES) === 0) {
    echo '<html>
            <body>
                <!-- The data encoding type, enctype, MUST be specified as below -->
                <h1>Import Nours CSV file</h1>
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
            'csv' => 'text/plain'
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
const FILE_NAME = 'Insertion_kpi_2022';
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
$existing = '|';
$toFile = '';

/**
 * Traitement fichier
 */
header('Content-Type: text/html; charset=utf-8');

if (!file_exists($fileName)) {
    exit('Echec lors de l\'ouverture du fichier .csv.');
}

$csvHead = [];
$csv = [];
$row = 1;
echo '<pre>';
if (($handle = fopen($fileName, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        // var_dump($data);
        // echo "<p> $num champs à la ligne $row: <br /></p>\n";
        if ($row === 1) {
            $csvHead = $data;
            for ($c = 0; $c < $num; $c++) {
                // echo $c . ' => ' . $data[$c] . '<br>';
            }
        } else {
            $csv[] = $data;
        }
        $row++;
    }
    fclose($handle);
}


// Compétiteurs
if (substr($_FILES['userfile']['name'], 0, 18) === FILE_NAME) {

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

    $sql_select_teams = "SELECT Id
        FROM kp_competition_equipe
        WHERE Code_compet IN ('CMH', 'CMF', 'CMH21', 'CMF21')
        AND Code_saison = ?
        AND Code_club = ?
        ";
    $stmt_select_teams = $myBdd->pdo->prepare($sql_select_teams);

    $sql_select_player_num = "SELECT MAX(Numero) num
        FROM kp_competition_equipe_joueur
        WHERE Id_equipe = ?
        ";
    $stmt_select_player_num = $myBdd->pdo->prepare($sql_select_player_num);

    $sql_insert_team = "INSERT INTO kp_competition_equipe_joueur 
        (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
        ";
    $stmt_insert_team = $myBdd->pdo->prepare($sql_insert_team);

    echo '<hr>';
    echo "Import saison " . SAISON . "<br>";
    echo "Mise à jour BDD ? ";
    echo utyGetPost('updateDB', 0) ? "OUI" : "NON";

    /*****************************************************************/
    // var_dump($listIcf);
    foreach ($csv as $staffMember) {
        if (in_array($staffMember[5], $listIcf)) {
            if ($updateDB) {
                $stmt_update->execute([
                    SAISON, $staffMember[6], $staffMember[7], $staffMember[8],
                    utyDateFrToUs($staffMember[9]), $staffMember[0] . '00', $staffMember[0], $staffMember[5]
                ]);
            }
            $updated++;
            $existing .= $staffMember[5] . '|';
        } else {
            if ($updateDB) {
                $nextMatric = $myBdd->GetNextMatricLicence();
                $stmt_insert->execute([
                    $nextMatric, SAISON, $staffMember[6], $staffMember[7], $staffMember[8],
                    utyDateFrToUs($staffMember[9]), $staffMember[0] . '00', $staffMember[0], $staffMember[5]
                ]);

                $stmt_select_teams->execute([SAISON, $staffMember[0] . '00']);
                while ($row = $stmt_select_teams->fetch()) {
                    $stmt_select_player_num->execute([$row['Id']]);
                    $row2 = $stmt_select_player_num->fetch();
                    $numero = $row2['num'] > 10 ? $row2['num'] + 1 : 11;
                    $categorie = utyCodeCategorie2(utyDateFrToUs($staffMember[9]), SAISON);
                    $stmt_insert_team->execute([
                        $row['Id'], $nextMatric,
                        $staffMember[6], $staffMember[7], $staffMember[8],
                        $categorie, $numero, 'E'
                    ]);
                }
            }
            $inserted++;
        }
    }

    echo '<hr>';
    echo 'Staff members ajoutés : ' . $inserted . '<br>';
    echo 'Staff members mis à jour : ' . $updated . '<br>';
    echo 'Staff members existants (icf) : ' . $existing . '<br>';
}

echo '<hr><pre>'
    . '<a href="">Back</a>';

unlink($fileName);
