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
require_once "../lib/date.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$cycles = db_select_cycles($db, $compte["no_compte"]);
$pregnancys = db_select_pregnancys($db, $compte["no_compte"]);

$nfp_method = [1 => "bill_temp", 2 => "bill", 3 => "fc", 4 => "fc_temp"];

echo json_encode([
	"account_id" => $compte["no_compte"],
	"email1" => $compte["email1"],
	"email2" => $compte["email2"],
	"nfp_method" => $compte["nfp_method"],
	"nfp_method_name" => $nfp_method[$compte["nfp_method"]],
	"age" => $compte["age"],
	"name" => $compte["name_compte"],
	"inscription_date" => $compte["inscription_date"],
	"sponsor" => boolval($compte["sponsor"]),
	"research" => boolval($compte["research"]), 
	"timeline_asc" => boolval($compte["timeline_asc"]), 
	"all_cycles_1st_day" => $cycles,
	"all_pregnancy_dates" => $pregnancys,
	"totp_state" => $compte["totp_state"]
]);

