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
sec_redirect_non_connecte($compte);

if (isset($_POST["ok_pour_supprimer"])) {
	db_delete_compte($db, $compte["no_compte"]);
	setcookie("MONCYCLEAPP_JETTON", '', -1, '/');
	echo json_encode(["suppr" => true]);
}
else {
	echo json_encode(["suppr" => false]);
}
