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

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
	http_response_code(403);
	echo json_encode(["auth" => False, "err" => "accès interdit"]);
	exit;
}

$db = db_open();

$sensations_brut = db_select_sensations($db, $_SESSION["no"]);

$sensations = [];
foreach ($sensations_brut as $obj) {
	$i = explode(',', $obj["sensation"]);
	foreach ($i as $sens) {
		$sens = strtolower(trim($sens));
		if (!isset($sensations[$sens])) $sensations[$sens] = 0;
		$sensations[$sens] += $obj["nb"];
	}
} 

echo json_encode($sensations);

