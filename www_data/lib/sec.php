<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

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
	if (isset($_COOKIE["MONCYCLEAPP_JETTON"])) {
		$compte = db_select_compte_jetton($db, $_COOKIE["MONCYCLEAPP_JETTON"]);
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

