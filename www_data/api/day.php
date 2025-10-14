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
require_once "../lib/data.php";
require_once "../lib/sec.php";

header('Content-Type: application/json');

$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$db = db_open();
$err = "";

$user_account = sec_auth_token($db);
sec_exit_si_non_connecte($user_account);

// LECTURE D'UNE OBSERVATION
if ($_SERVER['REQUEST_METHOD'] == "GET") {

	$dates_req = [];
	$start_date = null;
	$end_date = null;

	if (isset($_GET["date"]) && preg_match("/^\s*\d{4}-\d{2}-\d{2}(\s*,\s*\d{4}-\d{2}-\d{2})*\s*$/", $_GET["date"])) {
		$dates_req = explode(",", $_GET["date"]);
	}
	elseif (isset($_GET["date"])) $err .= "'date' in the wrong format. Correct format : YYYY-MM-DD,YYYY-MM-DD,YYYY-MM-DD... ";

	if (isset($_GET["start_date"]) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_GET["start_date"])) {
		$start_date = trim($_GET["start_date"]);
	}
	elseif (isset($_GET["start_date"])) $err .= "'start_date' in the wrong format. Correct format : YYYY-MM-DD ! ";

	if (isset($_GET["end_date"]) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_GET["end_date"])) {
		$end_date = trim($_GET["end_date"]);
	}
	elseif (isset($_GET["end_date"])) $err .= "'end_date' in the wrong format. Correct format : YYYY-MM-DD ! ";

	if (($start_date || $end_date) && !($start_date && $end_date)) {
		$err .= "'start_date' and 'end_date' should be used together";
	}

	if (empty($err)) {

		foreach ($dates_req as $date) {
			$date = trim($date);
			$result[$date] = data_construnct_day($db, $date, $user_account["no_user_account"]);
		}

		$all_days = array();
		if ($start_date && $end_date) $all_days = db_select_day_timelines_frame ($db, $start_date, $end_date, $user_account["no_user_account"]);
		elseif (empty($result)) $all_days = db_select_all_day_timeline($db, $user_account["no_user_account"]);
			
		$cycle_date = null;
		for ($i = 0; $i < count($all_days); $i+=1) {
			if ($all_days[$i]["cycle_1st_day"]) $cycle_date = $all_days[$i]["date_obs"];
			$all_days[$i] = data_construnct_day($db, $all_days[$i]["date_obs"], $user_account["no_user_account"], $all_days[$i], $cycle_date);
			$result[$all_days[$i]["date_obs"]] = $all_days[$i];
		}



	}

}

// CREATION ET MISE A JOUR D'UNE OBSERVATION
elseif($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_POST['date'])) {

	$date = trim($_POST['date']);
	$result["date"] = $date;

	$date_exploded = explode('-', $date);
	if (checkdate($date_exploded[1], $date_exploded[2], $date_exploded[0])) {

		if (isset($user_account["is_inactive"]) && boolval($user_account["is_inactive"])) {
			db_update_is_inactive($db, $user_account["no_user_account"], 0);
			$user_account["is_inactive"] = 0;
		}

		$date = trim($_POST['date']);
		$result["date"] = $date;

		try {

			$db->exec("START TRANSACTION");
	
			$output = db_select_day_timeline($db, $date, $user_account["no_user_account"]);
	
			$day_timeline_no = null;
			if(!isset($output[0])) $day_timeline_no = db_insert_day_timeline($db, $date, $user_account["no_user_account"]);
			else $day_timeline_no = $output[0]["no_day"];
			
			$day_timeline = [];
			foreach ($_POST as $key => $p) {
				if (!str_starts_with($key, "ob_") || $p=="") continue;
				if ($key == "ob_extra") {
					foreach (explode(",", $_POST["ob_extra"]) as $cp) {
						array_push($day_timeline, strtolower(trim($cp)));
					}
				}
				else array_push($day_timeline, strtolower(trim($p)));
			}
			
			$old_description = db_select_all_description_for_day_timeline($db, $user_account["no_user_account"], $day_timeline_no);
			$raw_old_description = [];
			$description_to_delete = [];
			foreach ($old_description as $desc) {
				if (!in_array($desc["name"], $day_timeline)) array_push($description_to_delete, $desc["no_description"]);
				array_push($raw_old_description, $desc["name"]);
			}
			$raw_new_description = [];
			foreach ($day_timeline as $desc) {
				if (!in_array($desc, $raw_old_description)) array_push($raw_new_description, $desc);
			}

			// TODO : DELETE AND CLEAN day_timeline_db
			$day_timeline_db = null;
	
			$temp = null;
			$htemp = null;
			if (isset($_POST["temp"]) && !empty(trim($_POST["temp"]))) {
				$temp = floatval($_POST["temp"]);
				if ($temp <= 0) $temp = null;
				elseif (!empty($_POST["time_temp_taken"])) $htemp = trim($_POST["time_temp_taken"]);
			}
	
			$go  = $_POST["stamp"] ?? '';
			$go .= $_POST["baby"] ?? '';
	
			$counter_start = null;
			if (isset($_POST["counter_start"]) && intval($_POST["counter_start"])>0) $counter_start = intval($_POST["counter_start"]);

			$last_write_client_UTC = "";
			if (isset($_POST["last_write_client_UTC"]) && date_validate_timestamp(trim($_POST['last_write_client_UTC']))) $last_write_client_UTC = trim($_POST['last_write_client_UTC']);
			else $last_write_client_UTC = date('Y-m-d H:i:s');
	
			db_update_day_timeline($db, $date, $user_account["no_user_account"], $last_write_client_UTC, $go, $_POST["fc_score"] ?? null, $_POST["fc_arrow"] ?? null, $day_timeline_db, $temp, $htemp, $_POST["is_peak"] ?? null, $_POST["union_sex"] ?? null, $_POST["cycle_1st_day"] ?? null, $_POST["day_not_observed"] ?? null, $_POST["pregnancy"] ?? null, $_POST["comment"] ?? null, $counter_start);
			
			foreach ($description_to_delete as $no_desc) db_delete_linked_descriptions ($db, $day_timeline_no, $no_desc);

			foreach ($raw_new_description as $desc) {
				$db_description = db_select_description_from_name($db, $user_account["no_user_account"], $desc);
				$description_no = null;
				if (!isset($db_description) || !isset($db_description[0])) {
					$description_no = db_insert_description($db, $user_account["no_user_account"], $desc, 0);
				}
				else $description_no = $db_description[0]["no_description"];
				db_insert_link_description_day_timeline($db, $day_timeline_no, $description_no);
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
		$err = "date non valide";
	}
}

// SUPPRESSION D'UNE OBSERVATION
elseif($_SERVER['REQUEST_METHOD'] == "DELETE" && isset($_DELETE['date']) && preg_match("/^\s*\d{4}-\d{2}-\d{2}\s*$/", $_DELETE['date'])) {
	$date = trim($_DELETE['date']);
	$result["date"] = $date;

	$result['nb_suppr'] = db_delete_day_timeline($db, $user_account["no_user_account"], $date);

	$result["outcome"] = "ok";
}

else {
	$err = "date and action missing";
}

$db = null;

if ($err) print(json_encode(array("err" => $err)));
else print(json_encode($result));

