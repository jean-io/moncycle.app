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

$mise_a_jour = [];

if (isset($_POST["nom"])) {
	db_update_compte_param_str($db, "nom", $_POST["nom"], $compte["no_compte"]);
	$mise_a_jour["nom"] = $_POST["nom"];
}

if (isset($_POST["email2"]) && (empty($_POST["email2"]) || filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL))) {
	db_update_compte_param_str($db, "email2", $_POST["email2"], $compte["no_compte"]);
	$mise_a_jour["email2"] = $_POST["email2"];
}

if (isset($_POST["methode"]) && !empty($_POST["methode"])) {
	$methode = intval($_POST["methode"]);
	if ($methode && $methode >=1 && $methode <= 4) {
		db_update_compte_param_int($db, "methode", $methode, $compte["no_compte"]);
		$mise_a_jour["methode"] = $methode;
	}
}

if (isset($_POST["age"]) && !empty($_POST["age"])) {
	$age = intval($_POST["age"]);
	if ($age && $age >=1) {
		db_update_compte_param_int($db, "age", $age, $compte["no_compte"]);
		$mise_a_jour["age"] = $age;
	}
}

echo json_encode($mise_a_jour);

