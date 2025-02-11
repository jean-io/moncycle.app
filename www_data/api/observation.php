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
			$raw_description = db_select_all_description_for_observation($db, $compte["no_compte"], $ob_data["no_observation"]);
			$description = [];
			foreach ($raw_description as $obj) array_push($description, $obj["name"]);
			if (count($description)>0) $ob_data["sensation"] = implode(", ", $description);
			else $ob_data["sensation"] = null;
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

		try {

			$db->exec("START TRANSACTION");
	
			$output = db_select_observation($db, $date, $compte["no_compte"]);
	
			$observation_no = null;
			if(!isset($output[0])) $observation_no = db_insert_observation($db, $date, $compte["no_compte"]);
			else $observation_no = $output[0]["no_observation"];
			
			$observation = [];
			foreach ($_POST as $key => $p) {
				if (!str_starts_with($key, "ob_") || $p=="") continue;
				array_push($observation, strtolower(trim($p)));
			}
			
			$old_description = db_select_all_description_for_observation($db, $compte["no_compte"], $observation_no);
			$raw_old_description = [];
			$description_to_delete = [];
			foreach ($old_description as $desc) {
				if (!in_array($desc["name"], $observation)) array_push($description_to_delete, $desc["no_description"]);
				array_push($raw_old_description, $desc["name"]);
			}
			$raw_new_description = [];
			foreach ($observation as $desc) {
				if (!in_array($desc, $raw_old_description)) array_push($raw_new_description, $desc);
			}

			// TODO : DELETE AND CLEAN observation_db
			$observation_db = null;
	
			$temp = null;
			$htemp = null;
			if (isset($_POST["temp"]) && !empty(trim($_POST["temp"]))) {
				$temp = floatval($_POST["temp"]);
				if ($temp <= 0) $temp = null;
				elseif (!empty($_POST["heure_temp"])) $htemp = trim($_POST["heure_temp"]);
			}
	
			$go  = $_POST["gommette"] ?? '';
			$go .= $_POST["bebe"] ?? '';
	
			$compteur = null;
			if (isset($_POST["compteur"]) && intval($_POST["compteur"])>0) $compteur = intval($_POST["compteur"]);

			$last_write_client_UTC = "";
			if (isset($_POST["last_write_client_UTC"]) && date_validate_timestamp(trim($_POST['last_write_client_UTC']))) $last_write_client_UTC = trim($_POST['last_write_client_UTC']);
			else $last_write_client_UTC = date('Y-m-d H:i:s');
	
			db_update_observation($db, $date, $compte["no_compte"], $last_write_client_UTC, $go, $_POST["note_fc"] ?? null, $_POST["fc_fle"] ?? null, $observation_db, $temp, $htemp, $_POST["jour_sommet"] ?? null, $_POST["union_sex"] ?? null, $_POST["premier_jour"] ?? null, $_POST["jenesaispas"] ?? null, $_POST["grossesse"] ?? null, $_POST["commentaire"] ?? null, $compteur);
			
			foreach ($description_to_delete as $no_desc) db_delete_linked_descriptions ($db, $observation_no, $no_desc);

			foreach ($raw_new_description as $desc) {
				$db_description = db_select_description_from_name($db, $compte["no_compte"], $desc);
				$description_no = null;
				if (!isset($db_description) || !isset($db_description[0])) {
					$description_no = db_insert_description($db, $compte["no_compte"], $desc, 0);
				}
				else $description_no = $db_description[0]["no_description"];
				db_insert_link_description_observation($db, $observation_no, $description_no);
			}

			$db->exec("COMMIT");

		} catch (\Throwable $th) {
			$db->exec("ROLLBACK");
			$result["outcome"] = "ko";
			throw $th;
		}

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


print(json_encode($result));

