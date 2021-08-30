<?php

function set_response_headers($method)
{
	$origin = &$_SERVER['HTTP_ORIGIN'];

	if (
		$origin === "https://kayak-polo.info" ||
		$origin === "https://www.kayak-polo.info" ||
		$origin === "http://localhost:9000"
	) {
		header("Access-Control-Allow-Origin: $origin");
	}

	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires');
	header('Content-Type: application/json');

	if ($method === 'OPTIONS') {
		http_response_code(200);
		exit;
	}
}

function user_authentication()
{
	global $purifier;
	if (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
		$user = preg_replace('`^[0]*`', '', $purifier->purify(trim($_SERVER["PHP_AUTH_USER"])));
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

function token_verification()
{
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
	return_401();
}

function methods($methods_array)
{
	if (!in_array($_SERVER['REQUEST_METHOD'], $methods_array)) {
		return_405();
		exit;
	}
	return;
}

function return_401()
{
	header('HTTP/1.0 401 Unauthorized');
	echo json_encode('Unauthorized');
	exit;
}

function return_404()
{
	header('HTTP/1.0 404 Not Found');
	echo json_encode('Not Found');
	exit;
}

function return_405()
{
	header('HTTP/1.0 405 Method Not Allowed');
	echo json_encode('Method Not Allowed');
	exit;
}

function return_200($result, $convert_to_json = true)
{
	http_response_code(200);
	$result = ($convert_to_json) ? json_encode($result) : $result;
	echo $result;
	exit;
}

/**
 * Renvoie la data du fichier json s'il a moins de $cache_duration minutes d'ancienneté
 */
function json_cache_read($cache_type, $cache_id, $cache_duration = 5)
{
	$file_name = 'files/' . $cache_type . '_' . $cache_id . '.json';
	try {
		$file_content = json_decode(file_get_contents($file_name), false);
	} catch (Exception $e) {
		return false;
	}
	if ($file_content) {
		$file_date = $file_content->created_at;
		$file_date = DateTime::createFromFormat('Y-m-d H:i:s', $file_date);
		$file_date->add(new DateInterval('PT' . $cache_duration . 'M'));
		$now = new DateTime("now");
		if ($now <= $file_date) {
			return $file_content->data;
		}
	}

	return false;
}

/**
 * Ecrit la data et la date de création dans un fichier json
 */
function json_cache_write($cache_type, $cache_id, $cache_content)
{
	$file_name = 'files/' . $cache_type . '_' . $cache_id . '.json';
	$file_content = json_encode([
		'created_at' => date('Y-m-d H:i:s'),
		'data' => $cache_content
	]);
	file_put_contents($file_name, $file_content);
}
