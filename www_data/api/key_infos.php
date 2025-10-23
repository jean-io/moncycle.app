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
require_once "../lib/date.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

$cycles = db_select_cycles($db, $user_account["no_user_account"]);
$pregnancys = db_select_pregnancys($db, $user_account["no_user_account"]);

$nfp_method = [1 => "bill_temp", 2 => "bill", 3 => "fc", 4 => "fc_temp"];

echo json_encode([
	"no_user_account" => $user_account["no_user_account"],
	"email1" => $user_account["email1"],
	"email2" => $user_account["email2"],
	"nfp_method" => $user_account["nfp_method"],
	"nfp_method_name" => $nfp_method[$user_account["nfp_method"]],
	"age" => $user_account["age"],
	"name" => $user_account["name_user_account"],
	"inscription_date" => $user_account["inscription_date"],
	"sponsor" => boolval($user_account["sponsor"]),
	"research" => boolval($user_account["research"]), 
	"timeline_asc" => boolval($user_account["timeline_asc"]), 
	"all_cycles_1st_day" => $cycles,
	"all_pregnancy_dates" => $pregnancys,
	"totp_state" => $user_account["totp_state"],
	"last_write_client_UTC" => $user_account["last_write_client_UTC"]
]);

