<?php

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

		db_update_observation ($db, date_sql($date), $_SESSION["no"], $_POST["gommette"] ?? '', $sensation_db, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["commentaire"] ?? null);
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
