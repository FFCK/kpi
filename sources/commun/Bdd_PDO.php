<?php
    include_once('../commun/MyBdd.php');
    
    $myBdd = new MyBdd();

    /**
     * Requêtes préparées
     */
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute();

    // SELECT avec plusieurs résultats
    while ($row = $stmt->fetch()) {
        //...
    }
    // ou  (permet de boucler plusieurs fois sur le même résultat)
    $resultarray = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultarray as $key => $row) {
        //...
    }

    $num_results = $stmt->rowCount(); // nombre de lignes sélectionnées
    // nombre de lignes modifiées pour un UPDATE, DELETE, 
    // nombre de lignes modifiées pour un REPLACE (x2 si Update effectif)

    // SELECT avec un seul résultat
    $row = $stmt->fetch($sql);

    // Exemple complet
    $sql  = "SELECT COUNT(m.Id) nbMatchs 
        FROM kp_match m, kp_journee j 
        WHERE j.Id = m.Id_journee 
        AND j.Code_competition = :Code_competition
        AND j.Code_saison = :Code_saison ";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute(array(
        ':Code_competition' => $codeCompetition,
        ':Code_saison' => $codeSaison
    ));

    while ($row = $stmt->fetch()) {}
    // ou
    $nbMatchs = $stmt->fetchColumn();

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
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute();
    if ($row = $stmt->fetch()) {
    // ...
    }

    // En plus court
    if ($row = $myBdd->pdo->query($sql)->fetch()) {
        // ...
    }



    /** 
     * Array 
    */
    $in = str_repeat('?,', count($in_array) - 1) . '?';
    $sql = "SELECT * FROM my_table WHERE my_value IN ($in)";
    $stmt = $myBdd->pdo->prepare($sql);
    $stmt->execute($in_array);
    $data = $stmt->fetchAll();

    // In case there are other placeholders in the query, you could use the following approach (the code is taken from my PDO tutorial):

    // You could use array_merge() function to join all the variables into a single array, adding your other variables in the form of arrays, in the order they appear in your query:

    $arr = [1,2,3];
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "SELECT * FROM table WHERE foo=? AND column IN ($in) AND bar=? AND baz=?";
    $stmt = $db->prepare($sql);
    $params = array_merge([$foo], $arr, [$bar, $baz]);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

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
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($params,$in_params)); // just merge two arrays
    $data = $stmt->fetchAll();

    $myBdd->pdo->lastInsertId();

    // DEBUG
    $stmt->debugDumpParams();
