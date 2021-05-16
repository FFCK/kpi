<?php
include_once('headers.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$user = user_authentication($purifier);
if ($user) {
	http_response_code(200);
	echo json_encode(['user' => $user]);
	exit;
// } else {
// 	header('HTTP/1.0 401 Unauthorized');
// 	echo json_encode('Unauthorized');
// 	exit;
}

function user_authentication($purifier) {
	if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
		$user = preg_replace( '`^[0]*`', '', $purifier->purify( trim( $_SERVER["PHP_AUTH_USER"] ) ) );
		$myBdd = new MyBdd();
		$sql = "SELECT u.*, c.Nom, c.Prenom, c.Numero_club 
			FROM kp_user u, kp_licence c 
			WHERE u.Code = ? 
			AND u.Code = c.Matric ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($user));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();	  
			if ($row["Pwd"] === md5($_SERVER["PHP_AUTH_PW"])) {
				$user = [
					'id' => $row["Code"],
					'name' => $row["Nom"],
					'firstname' => $row["Prenom"],
					'profile' => $row["Niveau"]
				];

				return $user;
			}
		}
	}

	return false;
}