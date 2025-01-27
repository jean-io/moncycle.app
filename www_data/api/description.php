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

$raw_description = db_select_description_with_count($db, $compte["no_compte"]);

$description = [];
foreach ($raw_description as $obj) {
	$description[$obj["name"]] = $obj["use_count"];
} 

echo json_encode($description);

