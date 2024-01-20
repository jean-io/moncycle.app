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

function sec_motdepasse_aleatoire($taille=12){
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

function sec_auth_jetton($db) {
	$jetton = "";
	if (isset($_COOKIE["MONCYCLEAPP_JETTON"]) && strlen($_COOKIE["MONCYCLEAPP_JETTON"])>0) $jetton = $_COOKIE["MONCYCLEAPP_JETTON"];
	if (isset($_POST["MONCYCLEAPP_JETTON"]) && strlen($_POST["MONCYCLEAPP_JETTON"])>0) $jetton = $_POST["MONCYCLEAPP_JETTON"];
	$head = getallheaders();
	if (isset($head["authorization"]) && str_contains($head["authorization"], "Bearer ")) $jetton = explode(' ', trim($head["authorization"]), 2)[1];
	if (strlen($jetton)>0) {
		$compte = db_select_compte_jetton($db, $jetton);
		if (isset($compte[0]) && isset($compte[0]["actif"]) && boolval($compte[0]["actif"])) {
			db_update_jetton_use($db, $compte[0]["no_jetton"]);
			return $compte[0];
		}
	}
	return null;	
}

function sec_exit_si_non_connecte($compte) {
	if (is_null($compte)) {
		http_response_code(401);
		echo json_encode(["auth" => False, "err" => "Accès interdit! Connectez-vous."]);
		exit;
	}
}

function sec_redirect_non_connecte($compte) {
	if (is_null($compte)) {
		header('Location: connexion');
		http_response_code(401);
		exit;
	}
}

function sec_auth_succes($db, $compte, $appareil=null) {
	$jetton = sec_motdepasse_aleatoire(256);

	db_insert_jetton($db, $compte["no_compte"], $appareil ?? ("AUTH | " . $_SERVER['HTTP_USER_AGENT']), "FR", $jetton);	
	db_update_compte_connecte($db, $compte["no_compte"]);

	$arr_cookie_options = array (
		'expires' => strtotime('+5 years'), 
		'path' => '/',
		'secure' => true,
		'httponly' => true,
	);

	setcookie("MONCYCLEAPP_JETTON", $jetton, $arr_cookie_options);

	return $jetton;
}

