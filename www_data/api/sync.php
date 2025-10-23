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
require_once "../lib/data.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$db = db_open();

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

$result = [];


if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['fromTimestamp'])) {

	$from_timestamp = trim($_GET['fromTimestamp']);
    
	if (date_validate_timestamp($from_timestamp)) {
		$result["day_timeline"] = db_select_day_timelines_modified($db, $from_timestamp, $user_account["no_user_account"]);
		$result["description"] = db_select_description_with_count_modified ($db, $from_timestamp, $user_account["no_user_account"]);
		for ($i = 0; $i < count($result["day_timeline"]); $i+=1) {
			$result["day_timeline"][$i] = data_construnct_day($db, $result["day_timeline"][$i]["date_obs"], $user_account["no_user_account"], $result["day_timeline"][$i]);
		}
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

