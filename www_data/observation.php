<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "config.php";
require_once "lib/db.php";
require_once "lib/date.php";

header('Content-Type: application/json');

session_start();

$result = [];

try {

	$db = db_open();

	// VERIFICATION DE LA BONNE OUVERTURE DE LA SESSION
	if (!isset($_SESSION["connected"]) || !$_SESSION["connected"]) {
		$result["err"] = "please login first";
	}


	// LECTURE D'UNE OBSERVATION
	elseif (isset($_GET['date'])) {

		$result["command"] = "GET";

		$date = new DateTime($_GET['date']);
		$result["date"] = date_sql($date);
		
		$output = db_select_observation($db, date_sql($date), $_SESSION["no"]);
		$cycle = db_select_cycle($db, date_sql($date), $_SESSION["no"]);
		
		$interval = date_diff(date_create($cycle[0]["cycle"]), date_create($result["date"]));
		$result["pos"] = intval($interval->format('%a'))+1;

		$result = array_merge($result, $cycle[0]);
		
		if(isset($output[0])) $result = array_merge($result, $output[0]);
		else $result["err"] = "no data at this date"; 
	}

	// CREATION ET MISE A JOUR D'UNE OBSERVATION
	elseif(isset($_POST['date'])) {

		if (isset($_SESSION["compte"]["relance"]) && boolval($_SESSION["compte"]["relance"])) {
			db_update_relance($db, $_SESSION["no"], 0);
			$_SESSION["compte"]["relance"] = 0;
		}

		$result["command"] = "POST";
		
		$date = new DateTime($_POST['date']);
		$result["date"] = date_sql($date);

		$output = db_select_observation($db, date_sql($date), $_SESSION["no"]);

		if(!isset($output[0])){
			db_insert_observation($db, date_sql($date), $_SESSION["no"]);
		}
		
		$sensation = [];
		foreach ($_POST as $key => $p) {
			if (!str_starts_with($key, "ob_") || $p=="") continue;
			array_push($sensation, strtolower(trim($p)));
		}
		$sensation_db = implode(", ", $sensation);
		if ($sensation_db == "") $sensation_db = null;

		$temp = null;
		if (isset($_POST["temp"]) && !empty(trim($_POST["temp"]))) {
			$temp = floatval($_POST["temp"]);
			if ($temp <= 0) $temp = null;
		}

		$go  = $_POST["gommette"] ?? '';
		$go .= $_POST["bebe"] ?? '';

		db_update_observation ($db, date_sql($date), $_SESSION["no"], $go, $_POST["note_fc"] ?? null, $_POST["fc_fle"] ?? null, $sensation_db, $temp, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["jenesaispas"] ?? null, $_POST["grossesse"] ?? null, $_POST["commentaire"] ?? null);

		$result["outcome"] = "ok";
		$result["args"] = $_POST;
	}

	// SUPPRESSION D'UNE OBSERVATION
	elseif(isset($_POST['suppr'])) {
		$result["command"] = "SUPPR";
		
		$date = new DateTime($_POST['suppr']);
		$result["date"] = date_sql($date);

		db_delete_observation($db, $_SESSION["no"], date_sql($date));

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

