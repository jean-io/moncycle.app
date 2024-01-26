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

$result = [];

try {
	$db = db_open();

	$compte = sec_auth_jetton($db);
	sec_exit_si_non_connecte($compte);

	// SUPPRESSION D'UNE OBSERVATION
	if(isset($_POST['date'])) {
		$result["command"] = "SUPPR";
		
		$date = new DateTime($_POST['date']);
		$result["date"] = date_sql($date);

		$_POST['nb_suppr'] = db_delete_observation($db, $compte["no_compte"], date_sql($date));

		$result["outcome"] = "ok";
		$result["args"] = $_POST;
	}

	else {
		$result["err"] = "missing a date";
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


print(json_encode($result));

