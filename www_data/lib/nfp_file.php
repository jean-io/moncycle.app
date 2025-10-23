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
	if ($day["pregnancy"]) $nfp_day["booleanPregnancyDetected"] = true;
	return $nfp_day;
}

function nfp_file_fertility_note_triage ($fc_note, $search_array) {
	$note = "";
	foreach ($search_array as $note_part)  {
		if (isset($fc_note[$note_part]) && $fc_note[$note_part]) $note .= $note_part;
	}
	return $note;
}

function nfp_file_fertility_care_day ($day, $db, $no_account) {
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
	$fc_note = data_parse_fc_note ($day["fc_score"]);
	$note_part = nfp_file_fertility_note_triage($fc_note, ["VH", "H", "M", "L", "VL", "BR"]);
	if ($note_part != "") $nfp_day["codifiedBleedingObservation"] = $note_part;
	$note_part = nfp_file_fertility_note_triage($fc_note, ["0","2","2W","4","6","8","10","10DL","10SL","10WL"]);
	if ($note_part != "") $nfp_day["codifiedMucusSensation"] = $note_part;
	$note_part = nfp_file_fertility_note_triage($fc_note, ["C","C/K","K","G","L","P","Y"]);
	if ($note_part != "") $nfp_day["codifiedMucusObservation"] = $note_part;
	$note_part = nfp_file_fertility_note_triage($fc_note, ["X1","X2","X3","AD"]);
	if ($note_part != "") $nfp_day["codifiedNumberObservations"] = $note_part;
	$note_part = nfp_file_fertility_note_triage($fc_note, ["AP","RAP","LAP"]);
	if ($note_part != "") $nfp_day["codifiedPainObservations"] = $note_part;
	if (isset($day["fc_arrow"])) {
		if ($day["fc_arrow"] == "↑") $nfp_day["codifiedArrow"] = "Up";
		elseif ($day["fc_arrow"] == "↓") $nfp_day["codifiedArrow"] = "Down";
		elseif ($day["fc_arrow"] == "→") $nfp_day["codifiedArrow"] = "Right";
	}
	// $nfp_day["nonUsualBleeding"] = ""; // spotting : true|false
	if ($day["day_not_observed"]) $nfp_day["mucusNotObserved"] = true;
	if ($day["temperature"]) $nfp_day["temperature"] = $day["temperature"];
	if ($day["time_temp_taken"]) $nfp_day["temperatureTime"] = $day["time_temp_taken"];
	if ($day["is_peak"]) $nfp_day["isPeak"] = true;
	if ($day["union_sex"]) $nfp_day["sexUnion"] = true;
	if ($day["comment"]) $nfp_day["comment"] = $day["comment"];
	if ($day["pregnancy"]) $nfp_day["booleanPregnancyDetected"] = true;
	return $nfp_day;
}

function nfp_file_billing_export($start_date, $end_date, $db, $user_account){
	
	$cycle_start_date = db_select_cycle($db, $start_date, $user_account["no_user_account"])[0]["cycle"] ?? $start_date;

	$raw_days = db_select_day_timelines_frame ($db, $cycle_start_date, $end_date, $user_account["no_user_account"]);
	$raw_days = array_column($raw_days, null, 'date_obs');

	$nfp_data = array();

	$cycle_counter = 0;
	$nfp_data[$cycle_counter] = array();

	if ($user_account["nfp_method"] == 1 || $user_account["nfp_method"] == 2) $nfp_data[$cycle_counter]["method"] = "billings";
	elseif ($user_account["nfp_method"] == 3 || $user_account["nfp_method"] == 4) $nfp_data[$cycle_counter]["method"] = "fertilityCare";

	$nfp_data[$cycle_counter]["cycleStartDate"] = $cycle_start_date;
	$nfp_data[$cycle_counter]["days"] = array();

	$date_cursor = new DateTime($cycle_start_date);
	$end_date_obj = new DateTime($end_date);
	$today = new DateTime();
	while ($date_cursor <= $end_date_obj && $date_cursor < $today) {
		$date_cursor_txt = $date_cursor->format('Y-m-d');
		if (isset($raw_days[$date_cursor_txt])) {
			$nfp_day = [];
			if ($user_account["nfp_method"] == 1 || $user_account["nfp_method"] == 2) {
				$nfp_day = nfp_file_billing_day($raw_days[$date_cursor_txt], $db, $user_account["no_user_account"]);
			}
			elseif ($user_account["nfp_method"] == 3 || $user_account["nfp_method"] == 4) {
				$nfp_day = nfp_file_fertility_care_day($raw_days[$date_cursor_txt], $db, $user_account["no_user_account"]);
			}
			if ($raw_days[$date_cursor_txt]["cycle_1st_day"] && $date_cursor_txt != $cycle_start_date) {
				$cycle_counter += 1;
				$nfp_data[$cycle_counter] = array();
				$nfp_data[$cycle_counter]["method"] = $nfp_data[$cycle_counter-1]["method"];
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

