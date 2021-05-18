<?php

function set_headers() {
	$origin = $_SERVER['HTTP_ORIGIN'];

	if ($origin === "https://kayak-polo.info" || 
		$origin === "https://www.kayak-polo.info" || 
		$origin === "http://localhost:9000")
	{  
		header("Access-Control-Allow-Origin: $origin");
	}

	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization');
}

function token_verification() {
	if (isset($_COOKIE["kpi_app"])) {
		$token = $_COOKIE["kpi_app"];
		$myBdd = new MyBdd();
		$sql = "SELECT generated_at
			FROM kp_user_token 
			WHERE token = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($token));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
            $date = date_create($row["generated_at"]);
            date_add($date, date_interval_create_from_date_string('10 days'));
			if ($date >= date_create()) {
				return true;
			}
		}
	}
	return false;
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
				$token = bin2hex(openssl_random_pseudo_bytes(16));

				$user = [
					'id' => $row["Code"],
					'name' => $row["Nom"],
					'firstname' => $row["Prenom"],
					'profile' => $row["Niveau"],
					'token' => $token
				];


				$sql2 = "INSERT INTO kp_user_token (user, token, generated_at)
					VALUES (:user, :token, NOW()) 
					ON DUPLICATE KEY UPDATE token = VALUES(token), generated_at = NOW() ";
				$result2 = $myBdd->pdo->prepare($sql2);
				$return2 = $result2->execute([
					'user' => $row["Code"],
					':token' => $token
				]);
				if (!$return2) {
					return false;
				}

				return [
					'user' => $user
				];
			}
		}
	}
	return false;
}