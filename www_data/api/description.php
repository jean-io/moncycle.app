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

		$already_exist = false;
		foreach ($raw_description as $desc) if ($desc["name"] == $desc_name) $already_exist = true;
    
		if ($already_exist) {
			$ret["err"] = "Error : this description already exist for this account.";
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
