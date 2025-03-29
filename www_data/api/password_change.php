<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$result = ["change_ok" => false, "msg" => ""];

if (isset($_POST["pw1"]) && !empty($_POST["pw1"]) && isset($_POST["old_pw"]) && !empty($_POST["old_pw"])) {
	
	$compte = db_select_compte_par_mail($db, $compte["email1"])[0] ?? [];

	if (strlen($_POST["pw1"])<8) {
		$result["msg"] = "nouveau mot de passe trop court";
	}
	elseif (isset($compte["password"]) && password_verify($_POST["pw1"], $compte["password"])) {
		$result["msg"] = "le nouveau mot de passe est identique à l'ancien mot de passe";
	}
	elseif (isset($compte["password"]) && password_verify($_POST["old_pw"], $compte["password"])) {
		unset($_POST["old_pw"]);

		db_udpate_password_par_nocompte($db, sec_hash($_POST["pw1"]), $compte["no_compte"]);

		$result["msg"] = "votre mot de passe a bien été mis à jour";
		$result["change_ok"] = true;
	}
	else {
		$result["msg"] = "l'ancien mot de passe n'est pas le bon";
	}

	unset($compte["password"]);

}
else {
	$result["msg"] = "les données sont manquantes";
}

echo json_encode($result);
