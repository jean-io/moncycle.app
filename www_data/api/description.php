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

$description_types = [0 => "undefined", 1 => "observation", 2 => "sensation"];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') parse_str(file_get_contents('php://input'), $_DELETE);

$db = db_open();

$compte = sec_auth_jetton($db);
sec_exit_si_non_connecte($compte);

$ret = [];

// creation of a description
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
	if (!isset($_POST["name"]) || !isset($_POST["type"])) {
		$ret["err"] = "Error : missing POST variable 'name' or 'type'.";
	}
	elseif (empty($_POST["name"]) || strpos(',',$_POST["name"])!==false) {
		$ret["err"] = "Error : 'name' argument is empty or contains a comma.";
	}
	elseif (!ctype_digit($_POST["type"]) || !isset($description_types[intval($_POST["type"])])) {
		$ret["err"] = "Error : 'type' argument is empty or contains an invalid number. Possibilities : 0 => undefined, 1 => observation, 2 => sensation.";
	}
	else {
		$desc_name = trim($_POST["name"]);
		$desc_type = intval($_POST["type"]);

		$raw_description = db_select_description_with_count($db, $compte["no_compte"]);

		$old_description = [];
		foreach ($raw_description as $desc) if ($desc["name"] == $desc_name) $old_description = $desc;
    
		if (isset($old_description["no_description"])) {
			$ret["ok"] = "Description of observation " . $desc_name . " already existing.";

			if (!empty($_POST["new_name"]) && strpos(',',$_POST["new_name"])==false) {
				$new_desc_name = trim($_POST["new_name"]);

				$already_exist_rename = false;
				foreach ($raw_description as $desc) if ($desc["name"] == $new_desc_name) $already_exist_rename = true;

				if ($already_exist_rename) {
					$ret["err"] = "Error : 'new_name' argument give a name that already exist.";
				}
				else {
					db_update_description_name($db, $compte["no_compte"], $old_description["no_description"], $new_desc_name);
					$ret["renamed"] = "Description of observation " . $desc_name . " renamed to " . $new_desc_name;
				}
			}

			if ($old_description["type"] != $desc_type) {
				db_update_description_type ($db, $compte["no_compte"], $old_description["no_description"], $desc_type);
				$ret["type_updated"] = "Type of description updated from " . $old_description["type"] . " to " . $desc_type;
			}
		}
		else {
			db_insert_description($db, $compte["no_compte"], $desc_name, $desc_type);
			$ret["ok"] = "Description of observation " . $desc_name . " added.";
		}

	}

}

// deletion of a description
elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {

}

// HTTP GET method, getting all descriptions
else {

	$ret = $description = db_select_description_with_count($db, $compte["no_compte"]);

}

echo json_encode($ret);
