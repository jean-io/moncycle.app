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

$result = [];


if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['fromTimestamp'])) {

	$from_timestamp = trim($_GET['fromTimestamp']);
    
	if (date_validate_timestamp($from_timestamp)) {
		$result["observation"] = db_select_observations_modified($db, $from_timestamp, $compte["no_compte"]);
		$result["description"] = db_select_description_with_count_modified ($db, $from_timestamp, $compte["no_compte"]);
	}
	else {
		$result["err"] = "fromTimestamp is not respecting YYYY-MM-DD hh:mm:ss or is not a valide date or time (it should be UTC).";
	}

}
else {
    $result["err"] = "please specify `fromTimestamp` parameter in URL with a UTC timestamp with format YYYY-MM-DD hh:mm:ss";
}

$db = null;

print(json_encode($result));

