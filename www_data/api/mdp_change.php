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

if (isset($_POST["mdp1"]) && !empty($_POST["mdp1"]) && isset($_POST["mdp_old"]) && !empty($_POST["mdp_old"])) {
	
	$compte = db_select_compte_par_mail($db, $compte["email1"])[0] ?? [];

	if (strlen($_POST["mdp1"])<8) {
		$result["msg"] = "nouveau mot de passe trop court";
	}
	elseif (isset($compte["motdepasse"]) && password_verify($_POST["mdp1"], $compte["motdepasse"])) {
		$result["msg"] = "le nouveau mot de passe est identique à l'ancien mot de passe";
	}
	elseif (isset($compte["motdepasse"]) && password_verify($_POST["mdp_old"], $compte["motdepasse"])) {
		unset($_POST["mdp_old"]);

		db_udpate_motdepasse_par_nocompte($db, sec_hash($_POST["mdp1"]), $compte["no_compte"]);

		$result["msg"] = "votre mot de passe a bien été mis à jour";
		$result["change_ok"] = true;
	}
	else {
		$result["msg"] = "l'ancien mot de passe n'est pas le bon";
	}

	unset($compte["motdepasse"]);

}
else {
	$result["msg"] = "les données sont manquantes";
}

echo json_encode($result);
