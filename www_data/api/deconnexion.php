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

$db = db_open();

$compte = sec_auth_jetton($db);

if(!is_null($compte)) {
	db_delete_jetton($db, $compte["no_jetton"], $compte["no_compte"]);
}

setcookie("MONCYCLEAPP_JETTON", '', -1, '/');

header('Content-Type: application/json');
header('Location: /');

echo json_encode([
	"auth" => false,
	"jetton" => '',
	"message" => "deconexion OK"
]);

