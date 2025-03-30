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

$user_account = sec_auth_token($db);

if(!is_null($user_account)) {
	db_delete_auth_token($db, $user_account["no_auth_token"], $user_account["no_user_account"]);
}

setcookie("MONCYCLEAPP_TOKEN", '', -1, '/');
setcookie("MONCYCLEAPP_JETTON", '', -1, '/'); // legacy, to be removed in a few release

header('Content-Type: application/json');
header('Location: /');

echo json_encode([
	"auth" => false,
	"auth_token" => '',
	"message" => "deconnection OK"
]);

