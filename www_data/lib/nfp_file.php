<?php
/* MONCYCLE.APP
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

require_once "../config.php";
require_once "../lib/db.php";

header('Content-Type: application/json');

function nfp_file_billing_day ($day, $db, $no_account) {
	$description = db_select_all_description_for_day_timeline($db, $no_account, $day["no_day"]);
	$observation = array();
	$sensations = array();
	foreach ($description as $desc) {
		if ($desc["type"] == 2) array_push($sensations, $desc["name"]);
		else array_push($observation, $desc["name"]);
	}
	$nfp_day = array();
	if ($day["stamp"]) {
		if (str_contains($day["stamp"],"G")) $nfp_day["stampColor"] = "Green";
		if (str_contains($day["stamp"],"R")) $nfp_day["stampColor"] = "Red";
		if (str_contains($day["stamp"],"Y")) $nfp_day["stampColor"] = "Yellow";
		if (str_contains($day["stamp"],"BB")) {
			$nfp_day["stampBaby"] = true;
			if (!array_key_exists("stampColor", $nfp_day)) $nfp_day["stampColor"] = "White";
		}
	}
	if ($day["day_not_observed"]) $nfp_day["mucusNotObserved"] = true;
	if (count($observation)) $nfp_day["freeMucusObservation"] = implode(", ", $observation);
	if (count($sensations)) $nfp_day["freeMucusSensation"] = implode(", ", $sensations);
	if ($day["temperature"]) $nfp_day["temperature"] = $day["temperature"];
	if ($day["time_temp_taken"]) $nfp_day["temperatureTime"] = $day["time_temp_taken"];
	if ($day["is_peak"]) $nfp_day["isPeak"] = true;
	if ($day["counter_start"]) $nfp_day["counterStart"] = $day["counter_start"];
	if ($day["union_sex"]) $nfp_day["sexUnion"] = true;
	if ($day["comment"]) $nfp_day["comment"] = $day["comment"];
	return $nfp_day;
}

function nfp_file_billing_export($start_date, $end_date, $db, $no_account){
	
	$cycle_start_date = db_select_cycle($db, $start_date, $no_account)[0]["cycle"] ?? $start_date;

	$raw_days = db_select_day_timelines_frame ($db, $cycle_start_date, $end_date, $no_account);
	$raw_days = array_column($raw_days, null, 'date_obs');

	$nfp_data = array();

	$cycle_counter = 0;
	$nfp_data[$cycle_counter] = array();
	$nfp_data[$cycle_counter]["method"] = "billings";
	$nfp_data[$cycle_counter]["cycleStartDate"] = $cycle_start_date;
	$nfp_data[$cycle_counter]["days"] = array();

	$date_cursor = new DateTime($cycle_start_date);
	$end_date_obj = new DateTime($end_date);
	$today = new DateTime();
	while ($date_cursor <= $end_date_obj && $date_cursor < $today) {
		$date_cursor_txt = $date_cursor->format('Y-m-d');
		if (isset($raw_days[$date_cursor_txt])) {
			$nfp_day = nfp_file_billing_day($raw_days[$date_cursor_txt], $db, $no_account);
			if ($raw_days[$date_cursor_txt]["cycle_1st_day"] && $date_cursor_txt != $cycle_start_date) {
				$cycle_counter += 1;
				$nfp_data[$cycle_counter] = array();
				$nfp_data[$cycle_counter]["method"] = "billings";
				$nfp_data[$cycle_counter]["cycleStartDate"] = $date_cursor_txt;
				$nfp_data[$cycle_counter]["days"] = array();
			}
			array_push($nfp_data[$cycle_counter]["days"], $nfp_day);
		}
		else array_push($nfp_data[$cycle_counter]["days"], (object)[]);
		$date_cursor->modify('+1 day');
	}

	return $nfp_data;
}

