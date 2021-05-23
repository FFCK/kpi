<?php

function StaffTestController ($method, $route) {
	return_200(['result' => 'OK']);
}

function EventController ($method, $route) {
  $myBdd = new MyBdd();
  $sql = "SELECT Id id, Libelle libelle, Lieu place 
    FROM kp_evenement
    WHERE Publication = 'O' 
    ORDER BY Id DESC ";
  $result = $myBdd->pdo->query($sql);
  $row = $result->fetchAll();	  

	return_200($row);
}