<?php
    include_once('../commun/MyBdd.php');
    
    $myBdd = new MyBdd();

    /**
     * Requêtes préparées
     */
    $result = $myBdd->pdo->prepare($sql);
    $result->execute();

    // SELECT avec plusieurs résultats
    while ($row = $result->fetch()) {
        //...
    }
    // ou  (permet de boucler plusieurs fois sur le même résultat)
    $resultarray = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultarray as $key => $row) {
        //...
    }


    // SELECT avec un seul résultat
    $row = $result->fetch($sql);

    // Exemple complet
    $sql  = "SELECT COUNT(m.Id) nbMatchs 
        FROM gickp_Matchs m, gickp_Journees j 
        WHERE j.Id = m.Id_journee 
        AND j.Code_competition = :Code_competition
        AND j.Code_saison = :Code_saison ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array(
        ':Code_competition' => $codeCompetition,
        ':Code_saison' => $codeSaison
    ));
    $num_results = $result->rowCount(); // nombre de lignes sélectionnées
    // nombre de lignes modifiées pour un UPDATE, DELETE, 
    // nombre de lignes modifiées pour un REPLACE (x2 si Update effectif)

    while ($row = $result->fetch()) {}
    // ou
    $nbMatchs = $result->fetchColumn();

    $codeSaison = $myBdd->GetActiveSaison();


    /**
     * Requêtes directes SELECT
     */
    foreach ($myBdd->pdo->query($sql) as $row) {
        //...
    }

    // SELECT avec une seule valeur en résultat
    $sql  = "SELECT Count(m.Id) ...";
    $row = $myBdd->pdo->query($sql)->fetchColumn();

    if ($result = $myBdd->pdo->query($sql)->fetchColumn()) {
        // ...
    }

    // SELECT avec une seule ligne de résultat
    $sql  = "SELECT ...";
    $row = $myBdd->pdo->query($sql)->fetch();

    // DELETE ou INSERT : Nombre de lignes traitées
    $count = $myBdd->pdo->exec("DELETE FROM ...");

    // Action seulement si une réponse
    $result = $myBdd->pdo->prepare($sql);
    $result->execute();
    if ($row = $result->fetch()) {
    // ...
    }

    // En plus court
    if ($row = $myBdd->pdo->query($sql)->fetch()) {
        // ...
    }



    /** 
     * Array 
    */
    $in  = str_repeat('?,', count($in_array) - 1) . '?';
    $sql = "SELECT * FROM my_table WHERE my_value IN ($in)";
    $stm = $db->prepare($sql);
    $stm->execute($in_array);
    $data = $stm->fetchAll();

    // In case there are other placeholders in the query, you could use the following approach (the code is taken from my PDO tutorial):

    // You could use array_merge() function to join all the variables into a single array, adding your other variables in the form of arrays, in the order they appear in your query:

    $arr = [1,2,3];
    $in  = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "SELECT * FROM table WHERE foo=? AND column IN ($in) AND bar=? AND baz=?";
    $stm = $db->prepare($sql);
    $params = array_merge([$foo], $arr, [$bar, $baz]);
    $stm->execute($params);
    $data = $stm->fetchAll();

    // In case you are using named placeholders, the code would be a little more complex, as you have to create a sequence of the named placeholders, e.g. :id0,:id1,:id2. So the code would be:

    // other parameters that are going into query
    $params = ["foo" => "foo", "bar" => "bar"];

    $ids = [1,2,3];
    $in = "";
    foreach ($ids as $i => $item)
    {
        $key = ":id".$i;
        $in .= "$key,";
        $in_params[$key] = $item; // collecting values into key-value array
    }
    $in = rtrim($in,","); // :id0,:id1,:id2

    $sql = "SELECT * FROM table WHERE foo=:foo AND id IN ($in) AND bar=:bar";
    $stm = $db->prepare($sql);
    $stm->execute(array_merge($params,$in_params)); // just merge two arrays
    $data = $stm->fetchAll();

    $myBdd->pdo->lastInsertId();

    // DEBUG
    $result->debugDumpParams();
