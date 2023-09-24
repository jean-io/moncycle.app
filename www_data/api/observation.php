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

	// LECTURE D'UNE OBSERVATION
	if (isset($_GET['date'])) {

		$result["command"] = "GET";

		$date = new DateTime($_GET['date']);
		$result["date"] = date_sql($date);
		
		$output = db_select_observation($db, date_sql($date), $compte["no_compte"]);
		$cycle = db_select_cycle($db, date_sql($date), $compte["no_compte"]);
		
		$interval = date_diff(date_create($cycle[0]["cycle"]), date_create($result["date"]));
		$result["pos"] = intval($interval->format('%a'))+1;

		$result = array_merge($result, $cycle[0]);
		
		if(isset($output[0])) $result = array_merge($result, $output[0]);
		else $result["err"] = "no data at this date"; 
	}

	// CREATION ET MISE A JOUR D'UNE OBSERVATION
	elseif(isset($_POST['date'])) {

		if (isset($compte["relance"]) && boolval($compte["relance"])) {
			db_update_relance($db, $compte["no_compte"], 0);
			$compte["relance"] = 0;
		}

		$result["command"] = "POST";
		
		$date = new DateTime($_POST['date']);
		$result["date"] = date_sql($date);

		$output = db_select_observation($db, date_sql($date), $compte["no_compte"]);

		if(!isset($output[0])){
			db_insert_observation($db, date_sql($date), $compte["no_compte"]);
		}
		
		$sensation = [];
		foreach ($_POST as $key => $p) {
			if (!str_starts_with($key, "ob_") || $p=="") continue;
			array_push($sensation, strtolower(trim($p)));
		}
		$sensation_db = implode(", ", $sensation);
		if ($sensation_db == "") $sensation_db = null;

		$temp = null;
		$htemp = null;
		if (isset($_POST["temp"]) && !empty(trim($_POST["temp"]))) {
			$temp = floatval($_POST["temp"]);
			if ($temp <= 0) $temp = null;
			elseif (!empty($_POST["h_temp"])) $htemp = trim($_POST["h_temp"]);
		}

		$go  = $_POST["gommette"] ?? '';
		$go .= $_POST["bebe"] ?? '';

		db_update_observation ($db, date_sql($date), $compte["no_compte"], $go, $_POST["note_fc"] ?? null, $_POST["fc_fle"] ?? null, $sensation_db, $temp, $htemp, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["jenesaispas"] ?? null, $_POST["grossesse"] ?? null, $_POST["commentaire"] ?? null);

		$result["outcome"] = "ok";
		$result["args"] = $_POST;
	}

	// SUPPRESSION D'UNE OBSERVATION
	elseif(isset($_POST['suppr'])) {
		$result["command"] = "SUPPR";
		
		$date = new DateTime($_POST['suppr']);
		$result["date"] = date_sql($date);

		db_delete_observation($db, $compte["no_compte"], date_sql($date));

		$result["outcome"] = "ok";
		$result["args"] = $_POST;
	}

	else {
		$result["err"] = "missing an action and a date";
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


print(json_encode($result));

