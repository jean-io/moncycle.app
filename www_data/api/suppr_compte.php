<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/date.php";
require_once "../lib/db.php";
require_once "../lib/doc.php";
require_once "../lib/sec.php";

require_once "../vendor/autoload.php";

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

if (isset($_DELETE["mdp_pour_supprimer"]) && strlen($_DELETE["mdp_pour_supprimer"])>0) {

	$compte = db_select_compte_par_mail($db, $compte["email1"])[0] ?? [];

	if (isset($compte["motdepasse"]) && password_verify($_DELETE["mdp_pour_supprimer"], $compte["motdepasse"])) {
		// SUPPRESSION DU COMPTE
		db_delete_compte($db, $compte["no_compte"]);
		setcookie("MONCYCLEAPP_JETTON", '', -1, '/');
		echo json_encode(["suppr" => true, "msg" => "compte supprimé"]);
	}
	else {
		echo json_encode(["suppr" => false, "msg" => "mauvais mot de passe"]);
	}

}
else {
	echo json_encode(["suppr" => false, "msg" => "mot de passe manquant"]);
}
