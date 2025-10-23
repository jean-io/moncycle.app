<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/moncycle-app/backend-api-web-app
*/

require_once "../config.php";
require_once "../lib/db.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

$result = ["change_ok" => false, "msg" => ""];

if (isset($_POST["pw1"]) && !empty($_POST["pw1"]) && isset($_POST["old_pw"]) && !empty($_POST["old_pw"])) {
	
	$user_account = db_select_user_account_par_mail($db, $user_account["email1"])[0] ?? [];

	if (strlen($_POST["pw1"])<8) {
		$result["msg"] = "nouveau mot de passe trop court";
	}
	elseif (isset($user_account["password"]) && password_verify($_POST["pw1"], $user_account["password"])) {
		$result["msg"] = "le nouveau mot de passe est identique à l'ancien mot de passe";
	}
	elseif (isset($user_account["password"]) && password_verify($_POST["old_pw"], $user_account["password"])) {
		unset($_POST["old_pw"]);

		db_udpate_password_par_nouser_account($db, sec_hash($_POST["pw1"]), $user_account["no_user_account"]);

		$result["msg"] = "votre mot de passe a bien été mis à jour";
		$result["change_ok"] = true;
	}
	else {
		$result["msg"] = "l'ancien mot de passe n'est pas le bon";
	}

	unset($user_account["password"]);

}
else {
	$result["msg"] = "les données sont manquantes";
}

echo json_encode($result);
