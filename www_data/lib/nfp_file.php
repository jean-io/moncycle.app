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
	
	$cycle_start_date = db_select_cycle($db, $start_date, $no_account)[0]["cycle"] ?? null;

	$raw_days = db_select_day_timelines_frame ($db, $cycle_start_date, $end_date, $no_account);

	$nfp_data = array();
	$nfp_data["cycles"] = array();
	$nfp_data["cycles"][0] = array();

	$nfp_data["cycles"][0]["method"] = "billings";
	$nfp_data["cycles"][0]["cycleStartDate"] = $cycle_start_date;
	$nfp_data["cycles"][0]["days"] = array();

	foreach ($raw_days as $day) {
		$nfp_day = nfp_file_billing_day($day, $db, $no_account);
		array_push($nfp_data["cycles"][0]["days"], $nfp_day);
	}

	return $nfp_data;
}

$db = db_open();

print(json_encode(nfp_file_billing_export("2025-10-01", "2025-10-15", $db, 2)));
