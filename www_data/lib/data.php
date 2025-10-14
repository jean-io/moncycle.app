<?php
/* moncycle.app
**
** licence Creative Commons CC BY-NC-SA
**
** https://www.moncycle.app
** https://github.com/jean-io/moncycle.app
*/

function data_convert_description($raw_description) {
	$description = [];
	$description_text = "";
	foreach ($raw_description as $obj) array_push($description, $obj["name"]);
	if (count($description)>0) $description_text = implode(", ", $description);
	else $description_text = null;
	return $description_text;
}

function data_construnct_day($db, $date, $no_user_account, $raw_day=null, $cycle=null, $pos=null) {
	$ob_data = array();

	if (is_null($cycle)) {
		$cycle_raw = db_select_cycle($db, $date, $no_user_account);
		$cycle = $cycle_raw[0]["cycle"] ?? null;
		$ob_data["cycle"] = $cycle;
	}
	elseif (!is_null($cycle)) $ob_data["cycle"] = $cycle;

	if($cycle && is_null($pos)) {
		$interval = date_diff(date_create($cycle), date_create($date));
		$ob_data["pos"] = intval($interval->format('%a'))+1;
	}
	elseif (!is_null($pos)) $ob_data["pos"] = $pos;

	if(is_null($raw_day)) $raw_day = db_select_day_timeline($db, $date, $no_user_account)[0] ?? array();
	if(!empty($raw_day)) {
		$ob_data = array_merge($ob_data, $raw_day);
		$raw_description = db_select_all_description_for_day_timeline($db, $no_user_account, $ob_data["no_day"]);
		$ob_data["sensation"] = data_convert_description($raw_description);
	}
	else {
		$ob_data["date_obs"] = $date;
	}

	return $ob_data;
}
