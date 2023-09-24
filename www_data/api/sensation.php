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

$sensations_brut = db_select_sensations($db, $compte["no_compte"]);

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

