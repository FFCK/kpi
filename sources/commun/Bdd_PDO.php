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
    // while ($row = $result->fetch()) {}
    $nbMatchs = $result->fetchColumn();



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

    // DELETE : Nombre de lignes traitées
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