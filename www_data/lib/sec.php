<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

define("TOTP_STATE_NEVER_USED", 0);
define("TOTP_STATE_DISABLED", 1);
define("TOTP_STATE_INIT", 2);
define("TOTP_STATE_ACTIVE", 3);

function sec_password_aleatoire($taille=12){
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$pass = [];
	$alphaLength = strlen($alphabet)-1;
	for ($i = 0; $i < $taille; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}

function sec_hash($text) {
	return password_hash($text, PASSWORD_BCRYPT);
}

function sec_auth_token($db) {
	$auth_token = "";
	if (isset($_COOKIE["MONCYCLEAPP_JETTON"]) && strlen($_COOKIE["MONCYCLEAPP_JETTON"])>0) $auth_token = $_COOKIE["MONCYCLEAPP_JETTON"]; // legacy, to be removed in a few release
	if (isset($_COOKIE["MONCYCLEAPP_TOKEN"]) && strlen($_COOKIE["MONCYCLEAPP_TOKEN"])>0) $auth_token = $_COOKIE["MONCYCLEAPP_TOKEN"];
	$head = getallheaders();
	if (isset($head["Authorization"]) && str_contains($head["Authorization"], "Bearer ")) $auth_token = explode(' ', trim($head["Authorization"]), 2)[1];
	if (strlen($auth_token)>0) {
		$user_account = db_select_user_account_auth_token($db, $auth_token);
		if (isset($user_account[0]) && isset($user_account[0]["user_enabled"]) && boolval($user_account[0]["user_enabled"])) {
			db_update_auth_token_use($db, $user_account[0]["no_auth_token"]);
			return $user_account[0];
		}
	}
	return null;	
}

function sec_exit_si_non_connecte($user_account) {
	if (is_null($user_account)) {
		http_response_code(401);
		echo json_encode(["auth" => False, "err" => "Accès interdit! Connectez-vous."]);
		exit;
	}
}

function sec_redirect_non_connecte($user_account) {
	if (is_null($user_account)) {
		header('Location: auth');
		http_response_code(401);
		exit;
	}
}

function sec_auth_succes($db, $user_account, $appareil=null) {
	$auth_token = sec_password_aleatoire(256);

	db_insert_auth_token($db, $user_account["no_user_account"], $appareil ?? ("AUTH | " . $_SERVER['HTTP_USER_AGENT']), "FR", $auth_token);	
	db_update_user_account_connecte($db, $user_account["no_user_account"]);

	$arr_cookie_options = array (
		'expires' => strtotime('+5 years'), 
		'path' => '/',
		'secure' => PHP_SECURE_COOKIES,
		'httponly' => true,
	);

	setcookie("MONCYCLEAPP_TOKEN", $auth_token, $arr_cookie_options);

	return $auth_token;
}

function sec_offuscate_str($str) {
	return substr($str, 0, 3) . " [masqué] " . substr($str, -3);
}
