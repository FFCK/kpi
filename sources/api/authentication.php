<?php

function user_authentication()
{
	global $purifier;
	if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
		$user = preg_replace('`^[0]*`', '', $purifier->purify(trim($_SERVER["PHP_AUTH_USER"])));
		$myBdd = new MyBdd();
		$sql = "SELECT u.Code, u.Pwd, u.Niveau, u.Id_Evenement, 
			c.Nom, c.Prenom, c.Numero_club, ut.token, ut.generated_at
			FROM kp_user u
			JOIN kp_licence c ON (u.Code = c.Matric)
			LEFT OUTER JOIN kp_user_token ut ON (u.Code = ut.user)
			WHERE u.Code = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($user));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			$events = trim($row['Id_Evenement'], '|');
			if ($row["Pwd"] === md5($_SERVER["PHP_AUTH_PW"]) && strlen($events) > 0) {
				if ($row["token"] !== null) {
					$token = $row["token"];
				} else {
					$token = bin2hex(openssl_random_pseudo_bytes(16));
				}

				$user = [
					'id' => $row["Code"],
					'name' => $row["Nom"],
					'firstname' => $row["Prenom"],
					'profile' => $row["Niveau"],
					'events' => $events,
					'token' => $token
				];

				$sql2 = "INSERT INTO kp_user_token (user, token, generated_at)
					VALUES (:user, :token, NOW()) 
					ON DUPLICATE KEY UPDATE token = VALUES(token), generated_at = NOW() ";
				$result2 = $myBdd->pdo->prepare($sql2);
				$return2 = $result2->execute([
					':user' => $row["Code"],
					':token' => $token
				]);
				if (!$return2) {
					return_401();
				}

				return [
					'user' => $user
				];
			}
		}
	}
	return_401();
}

function login($route)
{
	$authentication_result = user_authentication();
	return_200($authentication_result);
}

function token_check($event)
{
	if (isset($_COOKIE["kpi_app"])) {
		$token = $_COOKIE["kpi_app"];
		$myBdd = new MyBdd();
		$sql = "SELECT ut.user, ut.generated_at, u.Id_Evenement
			FROM kp_user_token ut
			INNER JOIN kp_user u ON (ut.user = u.Code)
			WHERE ut.token = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($token));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			$generated_at = date_create($row["generated_at"]);
			date_add($generated_at, date_interval_create_from_date_string('10 days'));
			if (!$generated_at >= date_create()) {
				return_401();
			}
			$grantedEvents = explode('|', trim($row["Id_Evenement"], '|'));
			if (!in_array($event, $grantedEvents)) {
				return_401();
			}
			return $row["user"];
		}
	}
	return_401();
}
