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

	if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

	$db = db_open();

	$compte = sec_auth_jetton($db);
	sec_exit_si_non_connecte($compte);

	// LECTURE D'UNE OBSERVATION
	if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['date'])) {

		$dates_req = [];
		if (preg_match("/^\s*\d{4}-\d{2}-\d{2}(\s*,\s*\d{4}-\d{2}-\d{2})*\s*$/", $_GET["date"])) {
			$dates_req = explode(",", $_GET["date"]);
		}
		else {
			$result["err"] = "dates aux mauvais format YYYY-MM-DD,YYYY-MM-DD,YYYY-MM-DD... ";
		}

		foreach ($dates_req as $date) {
			$date = trim($date);

			$ob_data = array();

			$cycle = db_select_cycle($db, $date, $compte["no_compte"]);
			if(isset($cycle[0])) {
				$interval = date_diff(date_create($cycle[0]["cycle"]), date_create($date));
				$ob_data["cycle"] = $cycle[0]["cycle"];
				$ob_data["pos"] = intval($interval->format('%a'))+1;
			}

			$ob_db = db_select_observation($db, $date, $compte["no_compte"]);
			if(isset($ob_db[0])) {
				$ob_data = array_merge($ob_data, $ob_db[0]);
			}
			else {
				$ob_data["err"] = "no data at this date";
				$ob_data["date_obs"] = $date;
			}

			$result[$date] = $ob_data;

		}

	}

	// CREATION ET MISE A JOUR D'UNE OBSERVATION
	elseif($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_POST['date'])) {

		$date = trim($_POST['date']);
		$result["date"] = $date;

		$date_exploded = explode('-', $date);
		if (checkdate($date_exploded[1], $date_exploded[2], $date_exploded[0])) {

			if (isset($compte["relance"]) && boolval($compte["relance"])) {
				db_update_relance($db, $compte["no_compte"], 0);
				$compte["relance"] = 0;
			}

			$date = trim($_POST['date']);
			$result["date"] = $date;

			$output = db_select_observation($db, $date, $compte["no_compte"]);

			if(!isset($output[0])){
				db_insert_observation($db, $date, $compte["no_compte"]);
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
				elseif (!empty($_POST["heure_temp"])) $htemp = trim($_POST["heure_temp"]);
			}

			$go  = $_POST["gommette"] ?? '';
			$go .= $_POST["bebe"] ?? '';

			db_update_observation ($db, $date, $compte["no_compte"], $go, $_POST["note_fc"] ?? null, $_POST["fc_fle"] ?? null, $sensation_db, $temp, $htemp, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["jenesaispas"] ?? null, $_POST["grossesse"] ?? null, $_POST["commentaire"] ?? null);

			$result["outcome"] = "ok";

		}
		else {
			$result["err"] = "date non valide";
		}
	}

	// SUPPRESSION D'UNE OBSERVATION
	elseif($_SERVER['REQUEST_METHOD'] == "DELETE" && isset($_DELETE['date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_DELETE['date'])) {
		$date = trim($_DELETE['date']);
		$result["date"] = $date;

		$result['nb_suppr'] = db_delete_observation($db, $compte["no_compte"], $date);

		$result["outcome"] = "ok";
	}

	else {
		$result["err"] = "date et action manquantes";
	}

	$db = null;
}
catch (Exception $e) {
	$result["err"] = $e->getMessage();
	$result["line"] = $e->getLine();
}


print(json_encode($result));

