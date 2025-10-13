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
