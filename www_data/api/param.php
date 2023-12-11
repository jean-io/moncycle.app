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
	db_update_compte_param($db, "nom", $_POST["nom"], $compte["no_compte"]);
	$mise_a_jour["nom"] = $_POST["nom"];
}

if (isset($_POST["email2"]) && (empty($_POST["email2"]) || filter_var($_POST["email2"], FILTER_VALIDATE_EMAIL))) {
	db_update_compte_param($db, "email2", $_POST["email2"], $compte["no_compte"]);
	$mise_a_jour["email2"] = $_POST["email2"];
}

echo json_encode($mise_a_jour);

